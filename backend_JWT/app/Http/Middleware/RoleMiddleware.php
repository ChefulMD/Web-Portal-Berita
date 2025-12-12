<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles  // Parameter ini akan menerima daftar peran yang diizinkan (misal: 'admin', 'user')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pastikan user sudah terautentikasi (sudah login)
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized. Token diperlukan.'], 401);
        }

        $userRole = Auth::user()->role;

        // Periksa apakah peran pengguna ada di dalam daftar peran yang diizinkan
        if (! in_array($userRole, $roles)) {
            return response()->json(['message' => 'Akses ditolak. Anda tidak memiliki izin yang cukup.'], 403);
        }

        return $next($request);
    }
}