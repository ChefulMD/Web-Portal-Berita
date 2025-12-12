<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Category;
use App\Models\User; // Digunakan untuk relasi penulis

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Untuk Auth::id()
use Illuminate\Support\Str; // Untuk Str::slug()
use Illuminate\Support\Facades\Storage; // Untuk upload/hapus gambar
use Illuminate\Routing\Controller as BaseController; // Base controller yang di-extend
use Illuminate\Validation\Rule; // Untuk Rule::in, Rule::unique
use Illuminate\Validation\ValidationException; // Untuk menangani error validasi
use Illuminate\Auth\Access\AuthorizationException; // Untuk menangani error otorisasi
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Untuk menggunakan $this->authorize()


class BeritaController extends BaseController
{
    use AuthorizesRequests; // Mengaktifkan penggunaan $this->authorize() di controller ini

    public function __construct()
    {
        // Middleware 'auth:api' untuk melindungi semua metode kecuali yang publik.
        // Pastikan 'jwt.auth' berfungsi dengan konfigurasi JWT-Auth Anda di config/auth.php.
        $this->middleware('auth:api', ['except' => [
            'getAll_news',
            'search_news',
            'filtering_news',
            'show_news',
        ]]);

        // Middleware 'role:admin,user' untuk otorisasi akses ke operasi CRUD.
        // Hanya user dengan role 'admin' ATAU 'user' yang bisa mencoba mengakses method ini.
        // Pastikan 'role' alias terdaftar di bootstrap/app.php ke RoleMiddleware::class.
        $this->middleware('role:admin,user', ['only' => ['store_news', 'update_news', 'destroy_news']]);
    }

    // --- Metode Publik (Bisa Diakses Tanpa Login) ---

    /**
     * Display a listing of the news.
     * Mengambil semua berita yang dipublikasikan dengan pagination.
     * Dapat menerima parameter filter jika digabungkan dengan filtering_news.
     */
    public function getAll_news(Request $request)
    {
        $query = Berita::where('status', 'published');
        $query->with(['category', 'user']);
        $query->orderBy('published_at', 'desc');
        $berita = $query->paginate(10);

        return response()->json($berita);
    }

    /**
     * Display the specified news.
     * Mengambil detail satu berita berdasarkan slug atau ID.
     */
    public function show_news(string $identifier)
    {
        try {
            $berita = Berita::where('slug', $identifier)
                            ->orWhere('id', $identifier)
                            ->where('status', 'published')
                            ->with(['category', 'user'])
                            ->first();

            if (!$berita) {
                return response()->json(['message' => 'Berita tidak ditemukan.'], 404);
            }

            // Opsional: Menambah jumlah tampilan
            // $berita->increment('views_count');

            return response()->json($berita);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server saat memuat detail berita.',
                'error' => $e->getMessage() . ' on file ' . $e->getFile() . ' at line ' . $e->getLine()
            ], 500);
        }
    }

    /**
     * Handle news search requests.
     * Mencari berita berdasarkan kata kunci di judul atau konten.
     */
   public function search_news(Request $request)
    {
        $keyword = $request->query('q');
        $query = Berita::where('status', 'published');

        $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', '%' . $keyword . '%')
            ->orWhere('content', 'like', '%' . $keyword . '%')
            ->orWhere('excerpt', 'like', '%' . $keyword . '%')
            ->orWhereHas('category', function ($qc) use ($keyword) {
                $qc->where('name', 'like', '%' . $keyword . '%');
            });
        });

        $query->with(['category', 'user']);
        $query->orderBy('published_at', 'desc');
        $results = $query->paginate(10);

        if ($results->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada berita yang ditemukan untuk kata kunci "' . $keyword . '".',
                'data' => []
            ], 200);
        }

        return response()->json($results);
    }


    /**
     * Filter news articles based on various criteria.
     * Dapat memfilter berdasarkan category_slug, author_id, date, from_date, to_date.
     */
    public function filtering_news(Request $request)
    {
        $query = Berita::query()->where('status', 'published');

        if ($request->has('category_slug')) {
            $categorySlug = $request->query('category_slug');
            $category = Category::where('slug', $categorySlug)->first();

            if ($category) {
                $query->where('category_id', $category->id);
            } else {
                return response()->json(['message' => 'Kategori tidak ditemukan.', 'data' => []], 404);
            }
        }

        if ($request->has('author_id')) {
            $authorId = $request->query('author_id');
            if (is_numeric($authorId)) {
                $query->where('user_id', $authorId);
            }
        }

        if ($request->has('date')) {
            $date = $request->query('date');
            if (strtotime($date)) {
                $query->whereDate('published_at', $date);
            }
        }

        if ($request->has('from_date') && $request->has('to_date')) {
            $fromDate = $request->query('from_date');
            $toDate = $request->query('to_date');
            if (strtotime($fromDate) && strtotime($toDate)) {
                $query->whereBetween('published_at', [$fromDate, $toDate]);
            }
        }

        $query->with(['category', 'user']);
        $query->orderBy('published_at', 'desc');
        $berita = $query->paginate(10);

        return response()->json($berita);
    }

    // --- Metode Terlindungi (Membutuhkan Login & Otorisasi via Policy) ---

    /**
     * Store a newly created news in storage.
     * Membutuhkan otorisasi 'create' (user yang login, role admin/user).
     */
     public function store_news(Request $request)
    {
        try {
            $this->authorize('create', Berita::class);

            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'status' => ['required', Rule::in(['draft', 'published'])],
                'published_at' => 'nullable|date',
            ]);

            $slug = Str::slug($validatedData['title']);
            $originalSlug = $slug;
            $count = 1;
            while (Berita::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }
            $validatedData['slug'] = $slug;

            // --- PERBAIKAN: TAMBAHKAN LOGIKA UNTUK EXCERPT DI SINI ---
            $validatedData['excerpt'] = Str::limit(strip_tags($validatedData['content']), 150);
            // --- AKHIR PERBAIKAN ---

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('news_images', 'public');
            }
            $validatedData['image'] = $imagePath;

            $validatedData['user_id'] = Auth::id();
            $validatedData['published_at'] = ($validatedData['status'] === 'published') ? ($validatedData['published_at'] ?? now()) : null;

            $berita = Berita::create($validatedData);

            return response()->json([
                'message' => 'Berita berhasil dibuat!',
                'berita' => $berita->load(['category', 'user'])
            ], 201);

        } catch (ValidationException $e) { /* ... */ }
        catch (AuthorizationException $e) { /* ... */ }
        catch (\Exception $e) { /* ... */ }
    }

    public function getManagedNews(Request $request)
    {
        try {
            $user = Auth::user(); // Dapatkan user yang sedang login

            $query = Berita::query(); // Mulai query untuk model Berita

            // Logika otorisasi berdasarkan role
            if ($user->role === 'admin') {
                // Admin bisa melihat semua berita, termasuk draft dan archived
                // Tidak perlu filter user_id
            } else {
                // User biasa hanya bisa melihat berita yang dia buat sendiri
                $query->where('user_id', $user->id);
            }

            // Anda bisa tambahkan filter status jika diperlukan di dashboard manajemen
            if ($request->has('status')) {
                $status = $request->query('status');
                // Pastikan status yang diminta valid
                if (in_array($status, ['draft', 'published', 'archived'])) {
                    $query->where('status', $status);
                }
            }

            // Eager load relasi category dan user
            $query->with(['category', 'user']);

            // Urutkan berdasarkan tanggal terbaru
            $query->orderBy('created_at', 'desc');

            // Pagination
            $berita = $query->paginate(10); // 10 berita per halaman manajemen

            return response()->json($berita);

        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server saat memuat berita manajemen.',
                'error' => $e->getMessage() . ' on file ' . $e->getFile() . ' at line ' . $e->getLine()
            ], 500);
        }
    }
    
    public function update_news(Request $request, int $id)
    {
        try {
            $berita = Berita::find($id);
            if (!$berita) {
                return response()->json(['message' => 'Berita tidak ditemukan.'], 404);
            }

            // Otorisasi: Memanggil Policy BeritaPolicy@update
            $this->authorize('update', $berita);

            $validatedData = $request->validate([
                'title' => [ 'required', 'string', 'max:255', Rule::unique('berita')->ignore($berita->id), ],
                'content' => 'required|string',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
                'published_at' => 'nullable|date',
            ]);


            // --- Logika Update/Hapus Gambar ---
            if ($request->hasFile('image')) {
                // Hapus gambar lama jika ada
                if ($berita->image && Storage::disk('public')->exists($berita->image)) {
                    Storage::disk('public')->delete($berita->image);
                }
                $validatedData['image'] = $request->file('image')->store('news_images', 'public'); // Simpan gambar baru
            } elseif ($request->input('image_remove') === 'true') { // Cek 'true' karena FormData mengirim string
                // Hapus gambar lama jika ada perintah hapus dari frontend
                if ($berita->image && Storage::disk('public')->exists($berita->image)) {
                    Storage::disk('public')->delete($berita->image);
                }
                $validatedData['image'] = null; // Set ke null di database
            } else {
                // Pertahankan path gambar lama jika tidak ada upload baru dan tidak ada perintah hapus
                $validatedData['image'] = $berita->image;
            }
            // --- Akhir Logika Update/Hapus Gambar ---

            // --- Logika Pembuatan Excerpt ---
            $validatedData['excerpt'] = Str::limit(strip_tags($validatedData['content']), 150);
            // --- Akhir Logika Excerpt ---

            // --- Logika Pembuatan Slug Baru jika Judul Berubah ---
            if ($berita->title !== $validatedData['title']) {
                $slug = Str::slug($validatedData['title']);
                $originalSlug = $slug;
                $count = 1;
                while (Berita::where('slug', $slug)->where('id', '!=', $berita->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $validatedData['slug'] = $slug;
            } else {
                $validatedData['slug'] = $berita->slug; // Pertahankan slug lama jika judul tidak berubah
            }
            // --- Akhir Logika Slug ---

            // --- Logika Atur published_at berdasarkan status ---
            if ($validatedData['status'] === 'published' && is_null($berita->published_at)) {
                // Jika status menjadi published dan sebelumnya belum ada tanggal publikasi, set now()
                $validatedData['published_at'] = $validatedData['published_at'] ?? now();
            } elseif ($validatedData['status'] !== 'published') {
                // Jika status bukan published, set published_at menjadi null (opsional)
                $validatedData['published_at'] = null;
            }
            // --- Akhir Logika published_at ---

            // user_id tetap dari penulis awal, tidak berubah saat update
            // $validatedData['user_id'] = $berita->user_id; // Tidak perlu diubah karena user_id tidak berubah saat update

            // Update Berita di Database
            $berita->update($validatedData);

            return response()->json([
                'message' => 'Berita berhasil diperbarui!',
                'berita' => $berita->load(['category', 'user']) // Muat relasi untuk respons
            ], 200); // 200 OK

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validasi gagal.', 'errors' => $e->errors()], 422);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan server saat memperbarui berita.',
                'error' => $e->getMessage() . ' on file ' . $e->getFile() . ' at line ' . $e->getLine()
            ], 500);
        }
    }

    /**
     * Remove the specified news from storage.
     * Membutuhkan otorisasi 'delete' (admin bisa semua, user biasa hanya miliknya).
     */
    public function destroy_news(int $id)
    {
        try {
            $berita = Berita::find($id);
            if (!$berita) { return response()->json(['message' => 'Berita tidak ditemukan.'], 404); }

            // Otorisasi: Memanggil Policy BeritaPolicy@delete
            $this->authorize('delete', $berita);

            // Logika Hapus Gambar
            if ($berita->image && Storage::disk('public')->exists($berita->image)) {
                Storage::disk('public')->delete($berita->image);
            }

            $berita->delete();

            return response()->json([ 'message' => 'Berita berhasil dihapus.' ], 200);

        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json([ 'message' => 'Terjadi kesalahan server saat menghapus berita.', 'error' => $e->getMessage() . ' on file ' . $e->getFile() . ' at line ' . $e->getLine() ], 500);
        }
    }
}