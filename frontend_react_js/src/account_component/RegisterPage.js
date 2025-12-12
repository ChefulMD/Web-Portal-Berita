import React, { useState } from 'react';
import { auth } from '../api';
import { useNavigate } from 'react-router-dom';

const RegisterPage = () => {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);
  const navigate = useNavigate();

  const handleRegister = async (e) => {
    e.preventDefault();

    setError(null);
    setSuccessMessage(null);

    if (password !== passwordConfirmation) {
      setError('Password dan konfirmasi password tidak cocok.');
      return;
    }

    try {
      const response = await auth.register({
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });

      setSuccessMessage('Pendaftaran berhasil! Silakan login.');
      setTimeout(() => {
        navigate('/login');
      }, 2000);

    } catch (err) {
      console.error('Gagal mendaftar:', err);
      if (err.response) {
        if (err.response.status === 422 && err.response.data.errors) {
          const validationErrors = Object.values(err.response.data.errors).flat();
          setError(validationErrors[0] || 'Terjadi kesalahan validasi.');
        } else if (err.response.data.message) {
          setError(err.response.data.message);
        } else {
          setError('Terjadi kesalahan saat pendaftaran. Silakan coba lagi.');
        }
      } else {
        setError('Tidak dapat terhubung ke server. Periksa koneksi Anda.');
      }
    }
  };

  return (
    <div className="container d-flex justify-content-center align-items-center min-vh-100">
      <div className="col-md-6 col-lg-5">
        <div className="card shadow border-0">
          <div className="card-body p-4">
            <h3 className="text-center mb-4">Daftar Akun Baru</h3>

            {error && (
              <div className="alert alert-danger">{error}</div>
            )}

            {successMessage && (
              <div className="alert alert-success">{successMessage}</div>
            )}

            <form onSubmit={handleRegister}>
              <div className="mb-3">
                <label htmlFor="name" className="form-label">Nama Lengkap</label>
                <input
                  type="text"
                  className="form-control"
                  id="name"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  required
                  placeholder="Nama Lengkap"
                />
              </div>

              <div className="mb-3">
                <label htmlFor="email" className="form-label">Email</label>
                <input
                  type="email"
                  className="form-control"
                  id="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                  placeholder="email@contoh.com"
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
                  placeholder="Minimal 8 karakter"
                />
              </div>

              <div className="mb-3">
                <label htmlFor="passwordConfirmation" className="form-label">Konfirmasi Password</label>
                <input
                  type="password"
                  className="form-control"
                  id="passwordConfirmation"
                  value={passwordConfirmation}
                  onChange={(e) => setPasswordConfirmation(e.target.value)}
                  required
                  placeholder="Ulangi password"
                />
              </div>

              <div className="d-grid mb-3">
                <button type="submit" className="btn btn-primary">
                  Daftar
                </button>
              </div>
            </form>

            <div className="d-flex justify-content-between">
              <button
                className="btn btn-link p-0"
                type="button"
                onClick={() => navigate("/")}
              >
                &larr; Kembali
              </button>

              <button
                className="btn btn-link p-0"
                type="button"
                onClick={() => navigate('/login')}
              >
                Sudah punya akun? Login
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default RegisterPage;
