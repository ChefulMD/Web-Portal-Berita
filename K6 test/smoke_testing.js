import http from 'k6/http';
import { check, sleep } from 'k6';
import { SharedArray } from 'k6/data';
import { runTestFlow, BASE_URL, users } from './common.js'; // Import alur tes dari common.js

export const options = {
    // 1 Virtual User selama 10 detik (Minimum effort)
    vus: 1,
    duration: '10s',
    thresholds: {
        // Toleransi kegagalan 0% (harus sukses 100% untuk dasar)
        http_req_failed: ['rate<0.01'], 
        http_req_duration: ['p(95)<4000'], // Waktu respons lebih longgar
        checks: ['rate==1.00'],
    },
};

// Menggunakan fungsi runTestFlow yang diimpor
export default function () {
    runTestFlow(users);
}