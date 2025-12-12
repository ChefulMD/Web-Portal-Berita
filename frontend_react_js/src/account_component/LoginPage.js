import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../AuthContent';

const LoginPage = () => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState(null);
  const navigate = useNavigate();
  const { login } = useAuth();

  const handleLogin = async (e) => {
    e.preventDefault();
    setError(null);

    try {
      const response = await login({ email, password });
      alert('Login berhasil! Selamat datang, ' + response.data.user.name + '!');
      navigate('/');
    } catch (err) {
      console.error('Gagal login:', err);
      if (err.response) {
        if (err.response.status === 401) {
          setError('Email atau password salah.');
        } else if (err.response.status === 422 && err.response.data.errors) {
          const validationErrors = Object.values(err.response.data.errors).flat();
          setError(validationErrors[0] || 'Terjadi kesalahan validasi.');
        } else {
          setError(err.response.data.message || 'Terjadi kesalahan saat login. Silakan coba lagi.');
        }
      } else {
        setError('Tidak dapat terhubung ke server. Periksa koneksi Anda.');
      }
    }
  };

  return (
    <div className="container d-flex justify-content-center align-items-center min-vh-100">
      <div className="col-md-5">
        <div className="card shadow border-0">
          <div className="card-body p-4">
            <h3 className="text-center mb-4">Login</h3>

            {error && (
              <div className="alert alert-danger">
                {error}
              </div>
            )}

            <form onSubmit={handleLogin}>
              <div className="mb-3">
                <label htmlFor="email" className="form-label">Email</label>
                <input
                  type="email"
                  className="form-control"
                  id="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                  placeholder="Masukkan email"
                />
              </div>

              <div className="mb-3">
                <label htmlFor="password" className="form-label">Password</label>
                <input
                  type="password"
                  className="form-control"
                  id="password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                  placeholder="Masukkan password"
                />
              </div>

              <div className="d-grid mb-3">
                <button type="submit" className="btn btn-primary">
                  Login
                </button>
              </div>
            </form>

            <div className="d-flex justify-content-between">
              <button
                type="button"
                className="btn btn-link p-0"
                onClick={() => navigate("/")}
              >
                &larr; Kembali
              </button>

              <button
                type="button"
                className="btn btn-link p-0"
                onClick={() => navigate('/register')}
              >
                Belum punya akun? Daftar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default LoginPage;
