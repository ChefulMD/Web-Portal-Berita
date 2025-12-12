<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data kategori yang akan dimasukkan
        $categories = [
            'Teknologi',
            'Olahraga',
            'Politik',
            'Hiburan',
            'Gaya Hidup'
        ];

        foreach ($categories as $categoryName) {
            DB::table('categories')->insert([
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
                'description' => 'Kategori tentang ' . strtolower($categoryName),
                'created_at' => now(), // Otomatis mengisi timestamp
                'updated_at' => now(), // Otomatis mengisi timestamp
            ]);
        }
    }
}