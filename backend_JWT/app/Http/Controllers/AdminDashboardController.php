<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Berita;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB; // <-- TAMBAHKAN INI

class AdminDashboardController extends BaseController
{
    public function getDashboardStats()
    {
        try {
            // 1. Menghitung Total Pengguna Aktif (Tetap Sama)
            $totalActiveUsers = User::where('role', '!=', 'admin')->count();

            // 2. Menghitung Jumlah Berita per Kategori (Tetap Sama)
            $newsPerCategory = Category::withCount(['berita' => function ($query) {
                                                $query->where('status', 'published');
                                            }])
                                        ->get()
                                        ->map(function ($category) {
                                            return [
                                                'id' => $category->id,
                                                'name' => $category->name,
                                                'slug' => $category->slug,
                                                'news_count' => $category->berita_count,
                                            ];
                                        });

            // 3. Menghitung Total Berita yang Dipublikasikan (Tetap Sama)
            $totalPublishedNews = Berita::where('status', 'published')->count();

            // --- TAMBAHAN BARU: Statistik Per Bulan ---

            // 4. Menghitung Jumlah Pengguna Baru per Bulan
            $usersPerMonth = User::select(
                                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                                DB::raw('COUNT(*) as count')
                            )
                            ->groupBy('month')
                            ->orderBy('month', 'asc')
                            ->get();

            // 5. Menghitung Jumlah Berita Dipublikasikan per Bulan
            $newsPerMonth = Berita::select(
                                DB::raw('DATE_FORMAT(published_at, "%Y-%m") as month'),
                                DB::raw('COUNT(*) as count')
                            )
                            ->where('status', 'published') // Hanya hitung yang sudah dipublikasikan
                            ->whereNotNull('published_at') // Pastikan ada tanggal publikasi
                            ->groupBy('month')
                            ->orderBy('month', 'asc')
                            ->get();

            // --- AKHIR TAMBAHAN BARU ---

            return response()->json([
                'total_active_users' => $totalActiveUsers,
                'news_per_category' => $newsPerCategory,
                'total_published_news' => $totalPublishedNews,
                'users_per_month' => $usersPerMonth, // <-- Data baru
                'news_per_month' => $newsPerMonth,   // <-- Data baru
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memuat statistik dashboard.',
                'error' => $e->getMessage() . ' on file ' . $e->getFile() . ' at line ' . $e->getLine()
            ], 500);
        }
    }
}