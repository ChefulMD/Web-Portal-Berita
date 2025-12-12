// src/components/Layouts/MainLayout.jsx
import React from 'react';
import { Outlet } from 'react-router-dom'; // Outlet akan merender komponen anak
import Navbar from '../navbar/navbar'; // Sesuaikan path Navbar Anda
import HomePage from '../news_component/HomePage'; // <-- INI YANG BENAR

const MainLayout = () => {
  return (
    <>
      <Navbar /> {/* Navbar muncul di sini */}
      <div className="main-content">
        <Outlet /> {/* Ini akan merender komponen halaman yang sesuai dengan route anak */}
      </div>
      {/* Anda bisa tambahkan Footer di sini jika mau */}
    </>
  );
};

export default MainLayout;