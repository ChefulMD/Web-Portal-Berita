import React, { useEffect, useState } from 'react';
import { useLocation, NavLink } from 'react-router-dom';
import { news } from '../api';
import './HasilPencarian.css';

const HasilPencarian = () => {
  const location = useLocation();
  const [searchResults, setSearchResults] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [searchKeyword, setSearchKeyword] = useState('');

  const loadSearchResults = async (keyword) => {
    if (!keyword) {
      setSearchResults([]);
      setLoading(false);
      return;
    }
    try {
      setLoading(true);
      setError(null);
      const response = await news.searchNews(keyword);
      setSearchResults(response.data.data);
    } catch (err) {
      console.error("Gagal memuat hasil pencarian:", err);
      setError("Gagal memuat hasil pencarian. Silakan coba lagi nanti.");
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const queryParams = new URLSearchParams(location.search);
    const keyword = queryParams.get('q');
    setSearchKeyword(keyword);
    loadSearchResults(keyword);
  }, [location.search]);

  if (loading) {
    return <div className="search-page-container">Memuat hasil pencarian...</div>;
  }

  if (error) {
    return <div className="search-page-container error-message">{error}</div>;
  }

  return (
    <div className="search-page-container">
      <h2>Hasil Pencarian untuk "{searchKeyword}"</h2>

      {searchResults.length === 0 ? (
        <p className="text-muted">Tidak ada berita yang ditemukan untuk pencarian ini.</p>
      ) : (
        <div className="search-results-list">
          {searchResults.map((item) => (
            <div key={item.id} className="search-item-card">
              <h5 className="search-item-title">
                <NavLink
                  to={`/berita/${item.slug}`}
                  className="text-decoration-none text-primary"
                >
                  {item.title}
                </NavLink>
              </h5>

              <p className="search-item-excerpt text-muted small">
                {item.excerpt}
              </p>

              <div className="search-item-meta small text-secondary">
                <span>
                  Oleh <strong>{item.user?.name || '-'}</strong>
                </span>
                <span className="mx-2">|</span>
                <span>
                  {item.created_at
                    ? new Date(item.created_at).toLocaleDateString('id-ID')
                    : '-'}
                </span>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
};

export default HasilPencarian;
