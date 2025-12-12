import React, { useEffect, useState } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import {
  LogOut,
  Home,
  FileText,
  BarChart3,
  Newspaper,
  Menu,
  X,
  Search
} from 'lucide-react';
import { useAuth } from '../AuthContent';

import './navbar.css';

const Navbar = () => {
  const [daftarKategori, setDaftarKategori] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  const navigate = useNavigate();
  const { user, logout } = useAuth();

  useEffect(() => {
    // Ganti ini dengan fetch API jika sudah ada API kategori
    setDaftarKategori([
      { id: 1, name: 'Politik', slug: 'politik' },
      { id: 2, name: 'Teknologi', slug: 'teknologi' },
      { id: 3, name: 'Olahraga', slug: 'olahraga' },
      { id: 4, name: 'Hiburan', slug: 'hiburan' },
      { id: 5, name: 'Gaya Hidup', slug: 'gaya-hidup' }
    ]);
  }, []);

  const handleSearchSubmit = (e) => {
    e.preventDefault();
    if (searchQuery.trim()) {
      navigate(`/search?q=${encodeURIComponent(searchQuery.trim())}`);
      setSearchQuery('');
      setIsMobileMenuOpen(false);
    }
  };

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  return (
    <nav className="navbar navbar-expand-lg custom-navbar shadow-sm">
      <div className="container-fluid">
        {/* Logo */}
        <NavLink to="/" className="navbar-brand d-flex align-items-center gap-2 text-primary fw-bold">
          <Newspaper size={24} /> Portal Berita
        </NavLink>

        {/* Hamburger button */}
        <button
          className="navbar-toggler"
          type="button"
          onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
        >
          {isMobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
        </button>

        <div className={`collapse navbar-collapse ${isMobileMenuOpen ? 'show' : ''}`}>
          {/* Left links */}
          <ul className="navbar-nav me-auto gap-lg-2">
            <li className="nav-item">
              <NavLink to="/" end className="nav-link d-flex align-items-center gap-1">
                <Home size={18} /> Beranda
              </NavLink>
            </li>
            {daftarKategori.map((kat) => (
              <li key={kat.id} className="nav-item">
                <NavLink to={`/kategori/${kat.slug}`} className="nav-link">
                  {kat.name}
                </NavLink>
              </li>
            ))}

            {user && (
              <>
                <li className="nav-item">
                  <NavLink
                    to="/berita/manajemen"
                    className="nav-link d-flex align-items-center gap-1"
                  >
                    <FileText size={18} /> Manajemen
                  </NavLink>
                </li>
                {user.role === 'admin' && (
                  <li className="nav-item">
                    <NavLink
                      to="/admin/dashboard"
                      className="nav-link d-flex align-items-center gap-1"
                    >
                      <BarChart3 size={18} /> Statistik
                    </NavLink>
                  </li>
                )}
              </>
            )}
          </ul>

          {/* Search + User */}
          <div className="d-lg-flex align-items-center gap-3 mt-3 mt-lg-0">
            <form className="d-flex" onSubmit={handleSearchSubmit}>
              <input
                type="text"
                className="form-control form-control-sm me-2"
                placeholder="Cari berita..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                style={{ minWidth: '200px' }}
              />
              <button type="submit" className="btn btn-primary btn-sm d-flex align-items-center gap-1">
                <Search size={16} /> Cari
              </button>
            </form>

            {user ? (
              <div className="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                <div className="text-end">
                  <div className="fw-medium">{user.name}</div>
                  <div className="text-muted small">{user.role}</div>
                </div>
                <button
                    onClick={handleLogout}
                    className="btn btn-danger btn-sm d-flex align-items-center gap-1"
                    >
                    <LogOut size={18} /> Logout
                </button>

              </div>
            ) : (
              <div className="d-flex gap-2 mt-3 mt-lg-0">
                <NavLink to="/login" className="btn btn-outline-primary btn-sm">
                  Login
                </NavLink>
                <NavLink to="/register" className="btn btn-primary btn-sm">
                  Sign Up
                </NavLink>
              </div>
            )}
          </div>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;
