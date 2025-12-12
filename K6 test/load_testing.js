import http from 'k6/http';
import { check, sleep } from 'k6';
import { SharedArray } from 'k6/data';
import { runTestFlow, BASE_URL, users } from './common.js'; // Import alur tes dari common.js

export const options = {
    // Uji Bertahap (Simulasi Pemanasan, Puncak Normal, dan Cooldown)
    stages: [
        { duration: '30s', target: 50 }, // Naik ke 50 VU
        { duration: '1m', target: 50 },  // Stabil 50 VU
        { duration: '30s', target: 0 },  // Turun ke 0
    ],
    thresholds: {
        http_req_failed: ['rate<0.01'],
        http_req_duration: ['p(95)<500'], // Latensi harus ketat di sini!
        checks: ['rate==1.00'],
    },
};

export default function () {
    runTestFlow(users);
}