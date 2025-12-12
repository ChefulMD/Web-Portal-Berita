import http from 'k6/http';
import { check, sleep } from 'k6';
import { SharedArray } from 'k6/data';
import { runTestFlow, BASE_URL, users } from './common.js'; // Import alur tes dari common.js

export const options = {
    // Naik secara agresif hingga 100 VU
    stages: [
        { duration: '1m', target: 100 }, 
        { duration: '30s', target: 100 }, 
        { duration: '30s', target: 0 }, 
    ],
    thresholds: {
        // Diizinkan kegagalan lebih tinggi, karena kita mencari titik rusak
        http_req_failed: ['rate<0.10'], // Maksimal 10% kegagalan
        http_req_duration: ['p(95)<2000'], // Latensi yang tinggi adalah wajar
    },
};

export default function () {
    runTestFlow(users);
}