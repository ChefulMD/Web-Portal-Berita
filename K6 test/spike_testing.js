import http from 'k6/http';
import { check, sleep } from 'k6';
import { SharedArray } from 'k6/data';
import { runTestFlow, BASE_URL, users } from './common.js'; // Import alur tes dari common.js

export const options = {
    stages: [
        { duration: '10s', target: 5 },   // Beban dasar
        { duration: '5s', target: 100 },  // LONJAKAN (Spike) tiba-tiba ke 100 VU
        { duration: '30s', target: 5 },   // Turun kembali ke beban dasar (Recovery)
        { duration: '10s', target: 0 },
    ],
    thresholds: {
        http_req_failed: ['rate<0.05'], // Menerima sedikit kegagalan di masa spike
        http_req_duration: ['p(95)<1500'], // Latensi tinggi saat spike
    },
};

export default function () {
    runTestFlow(users);
}