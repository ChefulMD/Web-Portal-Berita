<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AdminDashboardController; // Pastikan ini diimpor

// --- PUBLIC ROUTES (Tidak Butuh Token / Login) ---
// Rute yang bisa diakses siapa saja (pengunjung dan pengguna yang belum login)

// Rute Autentikasi Dasar (Login & Register)
Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Rute untuk mendapatkan semua kategori (untuk dropdown filter)
Route::get('categories', [CategoryController::class, 'getAllCategories']);

// Rute Berita Publik (Daftar, Pencarian, Filter, Detail)
Route::get('berita', [BeritaController::class, 'getAll_news']);
Route::get('berita/search', [BeritaController::class, 'search_news']);
Route::get('berita/filter', [BeritaController::class, 'filtering_news']);

// --- RUTE KRUSIAL: TEMPATKAN LEBIH SPESIFIK DI ATAS YANG UMUM ---
// Rute untuk manajemen berita harus di atas rute detail berita
// agar 'manajemen' tidak dianggap sebagai {identifier}
Route::get('berita/manajemen', [BeritaController::class, 'getManagedNews']); // <-- PENTING: Pindahkan ke sini

// Rute detail berita (paling umum, harus di bawah rute spesifik 'berita/manajemen')
Route::get('berita/{identifier}', [BeritaController::class, 'show_news']);


// --- PROTECTED ROUTES (Membutuhkan Token API yang Valid) ---
// Semua rute di dalam grup ini akan melewati middleware 'auth:api'
Route::middleware('auth:api')->group(function () {

    // Rute Auth yang memerlukan token (setelah login)
    Route::group([
        'prefix' => 'auth'
    ], function ($router) {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);
    });

    // Rute untuk Dashboard User biasa (yang sudah login)
    // Route::get('/user-dashboard', function () {
    //     return response()->json(['message' => 'Welcome to User Dashboard']);
    // });

    // --- ADMIN DAN USER YANG LOGIN (BISA CREATE/UPDATE/DELETE BERITA) ---
    // Menggunakan middleware 'role' untuk mengizinkan role 'admin' ATAU 'user'
    // Pastikan middleware 'role' sudah terdaftar di bootstrap/app.php
    Route::group(['middleware' => 'role:admin,user'], function () {

        // CRUD Berita
        Route::post('berita', [BeritaController::class, 'store_news']);
        Route::put('berita/{id}', [BeritaController::class, 'update_news']);
        Route::delete('berita/{id}', [BeritaController::class, 'destroy_news']);

    }); // Akhir dari grup middleware role:admin,user


    // --- ADMIN-ONLY ROUTES (Untuk Statistik, dll.) ---
    // Ini masih tetap hanya untuk admin
    Route::group(['middleware' => 'admin'], function () {
        // Admin Dashboard Statistik
        Route::get('admin/dashboard/stats', [AdminDashboardController::class, 'getDashboardStats']);
        // Jika ada rute admin lain seperti CRUD user, letakkan di sini
        // Route::get('/admin-dashboard', [AuthController::class, 'adminDashboard']); // Contoh jika ada
    });

}); // Akhir dari grup middleware auth:api

Route::get('/cors-test', function () {
    return response()->json(['message' => 'CORS works!']);
});
