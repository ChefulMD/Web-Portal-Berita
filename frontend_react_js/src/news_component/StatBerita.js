import React, { useEffect, useState } from 'react';
import { admin } from '../api';
import { useAuth } from '../AuthContent';
import {
  Line
} from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);

const StatBerita = () => {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const { user } = useAuth();

  useEffect(() => {
    if (user && user.role === 'admin') {
      fetchStats();
    } else if (!user) {
      setLoading(false);
      setError("Anda harus login untuk melihat halaman ini.");
    } else {
      setLoading(false);
      setError("Anda tidak memiliki izin untuk mengakses halaman ini.");
    }
  }, [user]);

  const fetchStats = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await admin.getDashboardStats();
      setStats(response.data);
    } catch (err) {
      console.error(err);
      setError("Gagal memuat data dashboard.");
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return <div className="container py-4">Memuat statistik...</div>;
  }

  if (error) {
    return <div className="container py-4 text-danger">{error}</div>;
  }

  if (!stats) {
    return <div className="container py-4">Tidak ada data statistik.</div>;
  }

  /**
   * Convert month format "2025-06" → "Juni 2025"
   */
  const formatMonthLabel = (monthStr) => {
    const [year, month] = monthStr.split('-');
    const monthsIndo = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return `${monthsIndo[Number(month) - 1]} ${year}`;
  };

  // Prepare chart data from BE response
  const months = stats.users_per_month.map(item => formatMonthLabel(item.month));
  const usersCounts = stats.users_per_month.map(item => item.count);
  const newsCounts = stats.news_per_month.map(item => item.count);

  const chartData = {
    labels: months,
    datasets: [
      {
        label: 'Pengguna Aktif per Bulan',
        data: usersCounts,
        fill: false,
        borderColor: 'rgb(13, 110, 253)',
        backgroundColor: 'rgba(13, 110, 253, 0.3)',
        tension: 0.3,
      },
      {
        label: 'Berita Dipublikasikan per Bulan',
        data: newsCounts,
        fill: false,
        borderColor: 'rgb(25, 135, 84)',
        backgroundColor: 'rgba(25, 135, 84, 0.3)',
        tension: 0.3,
      }
    ],
  };

  const chartOptions = {
    responsive: true,
    plugins: {
      legend: {
        position: 'top',
      },
    },
  };

  return (
    <div className="container py-4">
      <h2 className="mb-4">Statistik Website</h2>
      <p>Selamat datang, {user ? user.name : 'Admin'}!</p>

      <div className="row">
        <div className="col-lg-8">
          <div className="row g-4 mb-4">
            {/* Card Pengguna Aktif */}
            <div className="col-md-6">
              <div className="card shadow h-100 border-0">
                <div className="card-body d-flex align-items-center">
                  <div className="me-3">
                    <i className="bi bi-people-fill text-primary fs-1"></i>
                  </div>
                  <div>
                    <h5 className="card-title mb-1">Total Pengguna Aktif</h5>
                    <h3 className="card-text fw-bold">{stats.total_active_users}</h3>
                  </div>
                </div>
              </div>
            </div>

            {/* Card Berita Dipublikasikan */}
            <div className="col-md-6">
              <div className="card shadow h-100 border-0">
                <div className="card-body d-flex align-items-center">
                  <div className="me-3">
                    <i className="bi bi-newspaper text-success fs-1"></i>
                  </div>
                  <div>
                    <h5 className="card-title mb-1">Total Berita Dipublikasikan</h5>
                    <h3 className="card-text fw-bold">{stats.total_published_news}</h3>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Grafik */}
          <div className="card shadow border-0 mb-4">
            <div className="card-body">
              <h5 className="card-title">Perkembangan Pengguna & Berita</h5>
              <Line data={chartData} options={chartOptions} height={150} />
            </div>
          </div>
        </div>

        {/* Tabel kategori di kanan */}
        <div className="col-lg-4">
          <div className="card shadow border-0">
            <div className="card-body">
              <h5 className="card-title">Berita per Kategori</h5>
              {stats.news_per_category.length === 0 ? (
                <p className="text-muted">Tidak ada data.</p>
              ) : (
                <table className="table table-striped table-hover mt-3">
                  <thead className="table-light">
                    <tr>
                      <th>Kategori</th>
                      <th>Jumlah</th>
                    </tr>
                  </thead>
                  <tbody>
                    {stats.news_per_category.map((cat) => (
                      <tr key={cat.id}>
                        <td>{cat.name}</td>
                        <td>{cat.news_count}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default StatBerita;
