// src/App.js

import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import 'bootstrap-icons/font/bootstrap-icons.css';

// Import komponen-komponen halaman (sesuaikan PATH sesuai lokasi file Anda)
import HomePage from './news_component/HomePage'; // <-- INI YANG BENAR
import DetailPage from './news_component/DetailPage';
import LoginPage from './account_component/LoginPage'; // Asumsi login.js export default LoginPage
import RegisterPage from './account_component/RegisterPage';

// --- PERBAIKAN DI SINI ---
// Beri nama impor yang berbeda untuk setiap komponen
import AddBeritaPage from './news_component/AddBeritaPage'; // Untuk form "Tambah Berita"
import EditBeritaPage from './news_component/EditBerita';
import BeritaDashboard from './news_component/BeritaDashboard'; // Untuk halaman "Manajemen Berita"
import StatBerita from './news_component/StatBerita';
import HasilPencarian from './news_component/HasilPencarian';
// --- AKHIR PERBAIKAN ---

// Import komponen Layout
import MainLayout from './layout/MainLayout';
import AuthLayout from './layout/AuthLayout';

// Import ProtectedRoute
import ProtectedRoute from './ProteksiJalur'; // Sesuaikan path

// Contoh komponen dummy untuk Admin Dashboard (jika belum dibuat)
const AdminDashboardPage = () => <div><h1>Admin Dashboard</h1><p>Halaman khusus Admin.</p></div>;


function App() {
  return (
    <Router>
      <Routes>
        {/* --- Rute Publik dengan Navbar (menggunakan MainLayout) --- */}
        <Route element={<MainLayout />}>
          <Route path="/" element={<HomePage />} />
          <Route path="/berita/:identifier" element={<DetailPage />} />
          <Route path="/kategori/:categorySlug" element={<HomePage />} />
          <Route path="/search" element={<HasilPencarian />} />

          {/* --- Rute yang Dilindungi (Nested di dalam MainLayout) --- */}
          {/* Untuk Manajemen Berita: Bisa diakses oleh 'admin' atau 'user' yang sudah login */}
          <Route element={<ProtectedRoute allowedRoles={['admin', 'user']} />}>
            {/* Gunakan nama komponen yang benar: BeritaDashboard */}
            <Route path="/berita/manajemen" element={<BeritaDashboard />} />
          </Route>

          {/* Untuk Membuat/Mengedit Berita (Formulir) */}
          <Route element={<ProtectedRoute allowedRoles={['admin', 'user']} />}>
            {/* Gunakan nama komponen yang benar: AddBeritaPage */}
            <Route path="/berita/tambah" element={<AddBeritaPage />} />
            <Route path="/berita/edit/:id" element={<EditBeritaPage />} />
          </Route>

          {/* Rute Admin Khusus (hanya Admin Dashboard): Hanya bisa diakses oleh 'admin' */}
          <Route element={<ProtectedRoute allowedRoles={['admin']} />}>
            <Route path="/admin/dashboard" element={<StatBerita />} />
          </Route>

        </Route>

        {/* --- Rute Tanpa Navbar (menggunakan AuthLayout) --- */}
        <Route element={<AuthLayout />}>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />
        </Route>
      </Routes>
    </Router>
  );
}

export default App;