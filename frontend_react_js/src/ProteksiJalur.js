import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from './AuthContent'; // <-- Sesuaikan PATH ini ke AuthContext.js Anda

const ProteksiJalur = ({ allowedRoles }) => {
  const { user, loading } = useAuth(); // Dapatkan user dan status loading dari AuthContext

  // 1. Tampilkan status loading saat AuthContext sedang memverifikasi sesi pengguna
  if (loading) {
    return <div>Memuat...</div>; // Atau tampilkan spinner yang lebih bagus
  }

  // 2. Cek apakah pengguna sudah login
  if (!user) {
    // Jika tidak ada user (belum login), arahkan ke halaman login
    // 'replace' akan mengganti entri di history, jadi user tidak bisa 'back' ke halaman yang dilindungi
    return <Navigate to="/login" replace />;
  }

  // 3. Cek Role (jika allowedRoles diberikan)
  if (allowedRoles && !allowedRoles.includes(user.role)) {
    // Jika user login tapi perannya tidak diizinkan untuk rute ini,
    // arahkan ke halaman utama atau halaman 'Akses Ditolak' (403)
    return <Navigate to="/" replace />; // Atau ke halaman /akses-ditolak
  }

  // 4. Jika user sudah login dan memiliki role yang diizinkan,
  // render komponen anak (halaman yang dilindungi)
  return <Outlet />;
};

export default ProteksiJalur;