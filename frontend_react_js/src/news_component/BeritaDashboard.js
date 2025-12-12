import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { news } from '../api';
import { useAuth } from '../AuthContent';

const BeritaDashboard = () => {
  const [berita, setBerita] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [sortOrder, setSortOrder] = useState('desc'); // default: terbaru
  const navigate = useNavigate();
  const { user } = useAuth();

  const loadManagedNews = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await news.getManagedNews();
      setBerita(response.data.data);
    } catch (err) {
      console.error("Gagal memuat berita manajemen:", err);
      setError("Gagal memuat daftar berita manajemen.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadManagedNews();
  }, []);

  const handleDelete = async (id) => {
    if (window.confirm("Yakin ingin menghapus berita ini?")) {
      try {
        await news.deleteNews(id);
        alert("Berita berhasil dihapus!");
        loadManagedNews();
      } catch (err) {
        console.error("Gagal menghapus berita:", err);
        if (err.response && err.response.status === 403) {
          alert("Anda tidak memiliki izin untuk menghapus berita ini.");
        } else {
          alert("Gagal menghapus berita. Silakan coba lagi.");
        }
      }
    }
  };

  // Sort berita berdasarkan tanggal dibuat
  const sortedBerita = [...berita].sort((a, b) => {
    if (sortOrder === 'desc') {
      return new Date(b.created_at) - new Date(a.created_at);
    } else {
      return new Date(a.created_at) - new Date(b.created_at);
    }
  });

  if (loading) {
    return <div className="container py-4">Memuat berita manajemen...</div>;
  }

  if (error) {
    return <div className="container py-4 text-danger">{error}</div>;
  }

  return (
    <div className="container py-4">
      <h2 className="mb-3">Manajemen Berita</h2>

      {/* Tombol buat berita dan filter */}
      <div className="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <button
          className="btn btn-primary"
          onClick={() => navigate('/berita/tambah')}
        >
          + Buat Berita Baru
        </button>

        <div className="d-flex align-items-center mt-2 mt-md-0">
          <span className="me-2">Urutkan:</span>
          <select
            className="form-select form-select-sm w-auto"
            value={sortOrder}
            onChange={(e) => setSortOrder(e.target.value)}
          >
            <option value="desc">Terbaru</option>
            <option value="asc">Terlama</option>
          </select>
        </div>
      </div>

      {sortedBerita.length === 0 ? (
        <p className="text-muted">Belum ada berita yang dibuat.</p>
      ) : (
        <div className="card shadow-sm">
          <div className="card-body p-0">
            <div className="table-responsive">
              <table className="table table-hover table-striped align-middle mb-0">
                <thead className="table-light">
                  <tr>
                    <th scope="col" style={{ width: "50px" }}>#</th>
                    <th scope="col">Judul</th>
                    <th scope="col">Kategori</th>
                    <th scope="col">Penulis</th>
                    <th scope="col">Status</th>
                    <th scope="col">Tanggal Dibuat</th>
                    <th scope="col" style={{ width: "180px" }}>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  {sortedBerita.map((item, index) => (
                    <tr key={item.id}>
                      <td>{index + 1}</td>
                      <td>{item.title}</td>
                      <td>{item.category ? item.category.name : '-'}</td>
                      <td>{item.user ? item.user.name : '-'}</td>
                      <td>
                        <span className={`badge ${item.status === 'published' ? 'bg-success' : 'bg-secondary'}`}>
                          {item.status}
                        </span>
                      </td>
                      <td>{new Date(item.created_at).toLocaleDateString('id-ID')}</td>
                      <td>
                        {user && (user.role === 'admin' || user.id === item.user_id) && (
                          <>
                            <button
                              className="btn btn-sm btn-warning me-2"
                              onClick={() => navigate(`/berita/edit/${item.id}`)}
                            >
                              Edit
                            </button>
                            <button
                              className="btn btn-sm btn-danger"
                              onClick={() => handleDelete(item.id)}
                            >
                              Hapus
                            </button>
                          </>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default BeritaDashboard;
