<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;     // Import model User
use App\Models\Category;  // Import model Category
use App\Models\Berita;    // Import model Berita, karena kita akan pakai Berita::create()

class BeritaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dapatkan user 'Admin User' dan 'Regular User'
        $adminUser = User::where('email', 'admin@example.com')->first();
        $regularUser = User::where('email', 'user@example.com')->first();

        // Dapatkan beberapa kategori yang sudah ada
        $teknologiCategory = Category::where('slug', 'teknologi')->first();
        $olahragaCategory = Category::where('slug', 'olahraga')->first();
        $politikCategory = Category::where('slug', 'politik')->first();
        $hiburanCategory = Category::where('slug', 'hiburan')->first();

        // Pengecekan agar seeder tidak gagal jika user atau kategori belum ada
        if (!$adminUser || !$regularUser || !$teknologiCategory || !$olahragaCategory || !$politikCategory || !$hiburanCategory) {
            $this->command->info('Warning: Some users or categories not found. Please ensure UserSeeder and CategorySeeder run correctly first.');
            // Opsional: Anda bisa 'return;' di sini jika tidak ingin melanjutkan tanpa data dasar
            // Namun, kali ini kita akan tetap mencoba membuat berita dengan ID user/kategori yang mungkin null
            // untuk melihat apa yang terjadi (akan menyebabkan error jika foreign key not nullable)
        }

        $beritaData = [
            [
                'title' => 'Inovasi AI Terbaru Mengubah Industri Global',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                'image' => 'https://via.placeholder.com/800x400?text=AI+Innovation',
                'category_id' => $teknologiCategory ? $teknologiCategory->id : null, // Pastikan ada atau null
                'user_id' => $adminUser ? $adminUser->id : null, // Pastikan ada atau null
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'views_count' => rand(100, 5000),
            ],
            [
                'title' => 'Timnas Indonesia Lolos Kualifikasi Piala Dunia 2026',
                'content' => 'Berita menggembirakan datang dari dunia sepak bola. Timnas Indonesia berhasil mengamankan tiket ke babak kualifikasi Piala Dunia setelah pertandingan dramatis semalam. Dukungan penuh dari suporter menjadi kunci keberhasilan ini.',
                'image' => 'https://via.placeholder.com/800x400?text=Timnas+Indonesia',
                'category_id' => $olahragaCategory ? $olahragaCategory->id : null,
                'user_id' => $regularUser ? $regularUser->id : null, // Penulis: Regular User
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'views_count' => rand(100, 5000),
            ],
            [
                'title' => 'Analisis Kebijakan Ekonomi Baru Pemerintah',
                'content' => 'Pemerintah baru saja mengumumkan serangkaian kebijakan ekonomi yang diharapkan dapat mendorong pertumbuhan. Para analis memberikan pandangan beragam mengenai dampak jangka pendek dan panjang kebijakan ini terhadap pasar dan masyarakat.',
                'image' => 'https://via.placeholder.com/800x400?text=Ekonomi+Politik',
                'category_id' => $politikCategory ? $politikCategory->id : null,
                'user_id' => $adminUser ? $adminUser->id : null,
                'status' => 'published',
                'published_at' => now()->subDays(1),
                'views_count' => rand(100, 5000),
            ],
            [
                'title' => 'Berita Draft: Persiapan Konser Musim Panas Terbesar',
                'content' => 'Persiapan konser musim panas terbesar tahun ini sedang berjalan. Para artis ternama dari dalam dan luar negeri dijadwalkan akan tampil. Informasi tiket akan segera diumumkan.',
                'image' => 'https://via.placeholder.com/800x400?text=Konser+Draft',
                'category_id' => $hiburanCategory ? $hiburanCategory->id : null,
                'user_id' => $regularUser ? $regularUser->id : null,
                'status' => 'draft', // Berstatus draft
                'published_at' => null, // Belum dipublikasikan
                'views_count' => 0, // Belum dilihat jika draft
            ],
        ];

        foreach ($beritaData as $data) {
            // Otomatis membuat slug dan excerpt
            $data['slug'] = Str::slug($data['title']);
            $data['excerpt'] = Str::limit(strip_tags($data['content']), 150);

            // Menggunakan Berita::create()
            Berita::create($data);
        }
    }
}