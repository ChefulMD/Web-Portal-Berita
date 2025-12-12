import http from 'k6/http';
import { check, sleep } from 'k6';
import { SharedArray } from 'k6/data';

// ----------------------------------------------------
// KONSTANTA GLOBAL
// ----------------------------------------------------
// PASTIKAN BASE_URL INI BENAR SESUAI DENGAN TEMPAT LARAVEL BERJALAN
export const BASE_URL = 'http://127.0.0.1:8000/api';

// Data Pengguna untuk Login
// ****************************************************
// !! DATA TELAH DIPERBARUI DENGAN KREDENSIAL DARI USER !!
// ****************************************************
export const users = new SharedArray('users', function () {
    return [
        { email: 'Admin@example.com', password: 'admin123' }, // Akun Admin
        { email: 'user@example.com', password: 'user123' },   // Akun User
        { email: 'cheful@example.com', password: 'cheful123' }, // Akun Cheful
        { email: 'Aku@example.com', password: 'aku123' },     // Akun Aku
    ];
});

// ----------------------------------------------------
// FUNGSI ALUR TES UTAMA (Reusable Function)
// ----------------------------------------------------
export function runTestFlow(users) {
    // Memilih user secara bergantian berdasarkan Virtual User ID (__VU)
    const user = users[__VU % users.length];
    let token = ''; 
    
    // --------------------------------------------------
    // A. UJI LOGIN (POST /api/auth/login)
    // --------------------------------------------------
    let loginRes = http.post(`${BASE_URL}/auth/login`, 
        JSON.stringify({ 
            email: user.email, 
            password: user.password
        }),
        { headers: { 'Content-Type': 'application/json' }, tags: { name: 'Login' } }
    );

    check(loginRes, { 
        'Login: Status 200/201': (r) => r.status >= 200 && r.status < 300,
    });

    try {
        token = loginRes.json().access_token;
    } catch (e) {
        return; // Hentikan jika login gagal
    }

    // --------------------------------------------------
    // B. UJI AKSES RUTE TERPROTEKSI (GET /api/auth/user-profile)
    // --------------------------------------------------
    let profileRes = http.get(`${BASE_URL}/auth/user-profile`, {
        headers: { 'Authorization': `Bearer ${token}` },
        tags: { name: 'UserProfile' },
    });

    check(profileRes, { 
        'Profile: Status 200': (r) => profileRes.status === 200,
    });
    
    // --------------------------------------------------
    // C. UJI LOGOUT (POST /api/auth/logout)
    // --------------------------------------------------
    let logoutRes = http.post(`${BASE_URL}/auth/logout`, null, {
        headers: { 'Authorization': `Bearer ${token}` },
        tags: { name: 'Logout' },
    });
    
    check(logoutRes, { 
        'Logout: Status 200': (r) => logoutRes.status === 200,
    });
    
    // --------------------------------------------------
    // D. UJI VERIFIKASI TOKEN (Cek Blacklisting)
    // --------------------------------------------------
    let invalidRes = http.get(`${BASE_URL}/auth/user-profile`, {
        headers: { 'Authorization': `Bearer ${token}` },
        tags: { name: 'TokenInvalidationCheck' },
    });

    check(invalidRes, {
        // Harus mengembalikan 401 karena token sudah di-logout
        'Invalid Check: Status 401': (r) => invalidRes.status === 401,
    });

    // Istirahat (think time) 1 sampai 5 detik
    sleep(Math.random() * 4 + 1); 
}