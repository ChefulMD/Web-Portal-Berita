<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Berita; // Import model Berita
use Illuminate\Auth\Access\Response; // Import ini untuk Response::allow/deny jika digunakan

class BeritaPolicy
{
    /**
     * Perform pre-authorization checks.
     * Metode ini dijalankan SEBELUM metode Policy lainnya (create, update, delete).
     * Jika user adalah 'admin', izinkan semua aksi secara otomatis.
     */
    public function before(User $user, string $ability): ?bool
    {
        // Admin bisa melakukan SEMUA aksi (create, update, delete) pada Berita.
        if ($user->role === 'admin') {
            return true; // Mengizinkan admin melewati Policy lainnya
        }

        // Jika user bukan admin, lanjutkan ke metode Policy spesifik (create, update, delete).
        return null;
    }

    /**
     * Determine whether the user can create models.
     * Siapa yang bisa membuat berita? Admin atau user biasa yang login.
     */
    public function create(User $user): bool
    {
        // Policy ini hanya dieksekusi jika user BUKAN admin (karena admin sudah diizinkan di before()).
        // User biasa yang sudah login bisa membuat berita.
        return $user !== null && $user->role === 'user'; // Atau cukup $user !== null; jika semua logged-in user bisa
        // Jika Anda ingin hanya 'admin' dan 'user' (yang bukan admin) secara eksplisit:
        // return in_array($user->role, ['user']);
    }

    /**
     * Determine whether the user can update the model.
     * Siapa yang bisa mengupdate berita? Admin (sudah di before()) atau penulisnya sendiri.
     */
    public function update(User $user, Berita $berita): bool
    {
        // Policy ini hanya dieksekusi jika user BUKAN admin.
        // User biasa hanya bisa update berita yang dia buat sendiri.
        return $user->id === $berita->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Siapa yang bisa menghapus berita? Admin (sudah di before()) atau penulisnya sendiri.
     */
    public function delete(User $user, Berita $berita): bool
    {
        // Policy ini hanya dieksekusi jika user BUKAN admin.
        // User biasa hanya bisa delete berita yang dia buat sendiri.
        return $user->id === $berita->user_id;
    }

    // Anda bisa tambahkan metode untuk restore/forceDelete jika diperlukan di masa depan
    // public function restore(User $user, Berita $berita): bool { return false; }
    // public function forceDelete(User $user, Berita $berita): bool { return false; }
}