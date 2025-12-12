// src/components/Layouts/AuthLayout.jsx
import React from 'react';
import { Outlet } from 'react-router-dom';
import HomePage from '../news_component/HomePage'; // <-- INI YANG BENAR

const AuthLayout = () => {
  return (
    <div className="auth-container">
      {/* Tidak ada Navbar di sini */}
      <Outlet /> {/* Ini akan merender komponen halaman login/register */}
    </div>
  );
};

export default AuthLayout;