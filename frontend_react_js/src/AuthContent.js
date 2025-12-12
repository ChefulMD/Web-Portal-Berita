// src/contexts/AuthContent.js
import React, { createContext, useState, useEffect, useContext } from 'react';
import { auth } from './api'; // Pastikan path ke api.js benar

const AuthContent = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null); // State untuk menyimpan objek user
  const [loading, setLoading] = useState(true); // State untuk menunjukkan sedang memuat user

  // Fungsi untuk memuat user dari localStorage (saat aplikasi pertama kali dimuat)
  const loadUserFromLocalStorage = async () => {
    const token = localStorage.getItem('jwt_token');
    if (token) {
      try {
        // Coba ambil user profile dari backend untuk validasi token dan mendapatkan data user terbaru
        const response = await auth.getUserProfile();
        setUser(response.data); // Simpan data user
      } catch (error) {
        console.error("Gagal memuat user profile atau token invalid:", error);
        localStorage.removeItem('jwt_token'); // Hapus token jika tidak valid
        setUser(null);
      }
    }
    setLoading(false);
  };

  useEffect(() => {
    loadUserFromLocalStorage();
  }, []);

  // Fungsi untuk update user state setelah login
  const login = async (credentials) => {
    const response = await auth.login(credentials);
    const { access_token, user: userData } = response.data;
    localStorage.setItem('jwt_token', access_token); // Simpan token
    setUser(userData); // Simpan data user
    return response; // Kembalikan respons penuh
  };

  // Fungsi untuk logout
  const logout = async () => {
    try {
      await auth.logout(); // Panggil endpoint logout di backend
    } catch (error) {
      console.error("Logout error:", error);
      // Tetap lanjutkan logout di frontend meskipun backend error
    } finally {
      localStorage.removeItem('jwt_token'); // Hapus token dari localStorage
      setUser(null); // Set user ke null
    }
  };

  // Objek value yang akan disediakan oleh AuthContent
  const value = { user, login, logout, loading };

  return (
    <AuthContent.Provider value={value}>
      {!loading && children} {/* Render children hanya setelah loading selesai */}
      {loading && <div>Memuat sesi pengguna...</div>} {/* Opsional: Tampilan loading awal */}
    </AuthContent.Provider>
  );
};

// Hook kustom untuk memudahkan penggunaan AuthContent
export const useAuth = () => {
  return useContext(AuthContent);
};