<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController; // Sesuaikan dengan base controller Anda

class CategoryController extends BaseController // Pastikan ini meng-extend BaseController Anda
{
    public function __construct()
    {
        // Semua endpoint kategori ini bisa diakses publik (tanpa autentikasi)
        $this->middleware('auth:api', ['except' => ['getAllCategories']]);
        // Anda bisa tambahkan middleware admin jika ada operasi CRUD kategori di sini
    }

    public function getAllCategories()
    {
        $categories = Category::all(); // Ambil semua kategori dari database
        return response()->json($categories);
    }
}