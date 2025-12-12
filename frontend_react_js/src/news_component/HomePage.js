import React, { useEffect, useState } from 'react';
import { news } from '../api';
import { useParams, NavLink } from 'react-router-dom';
import './HomePage.css';

const HomePage = () => {
  const [berita, setBerita] = useState([]);
  const { categorySlug } = useParams();

  const loadBerita = async () => {
    try {
      const filterParams = {};
      if (categorySlug) {
        filterParams.category_slug = categorySlug;
      }

      const response = await news.getFilteredNews(filterParams);

      const sorted = [...response.data.data].sort((a, b) =>
        new Date(b.created_at) - new Date(a.created_at)
      );
      setBerita(sorted);
    } catch (err) {
      console.error("Gagal memuat berita:", err);
    }
  };

  useEffect(() => {
    loadBerita();
  }, [categorySlug]);

  if (!berita || berita.length === 0) {
    return (
      <div className="container my-4">
        <h2>
          {categorySlug
            ? `Berita ${categorySlug.replace(/-/g, ' ').toUpperCase()}`
            : 'Semua Berita'}
        </h2>
        <p className="text-muted">
          Belum ada berita yang dipublikasikan (atau tidak ditemukan dengan filter ini).
        </p>
      </div>
    );
  }

  if (categorySlug) {
    // =============== ✅ Layout KATEGORI (Grid Cards) ===============
    return (
      <div className="container my-4">
        <h2 className="mb-4">
          Berita {categorySlug.replace(/-/g, ' ').toUpperCase()}
        </h2>

        <div className="row g-4">
          {berita.map((item) => (
            <div className="col-12 col-md-6 col-lg-4" key={item.id}>
              <div className="card h-100 shadow-sm news-card">
                {/* Image jika ada */}
                {item.image && (
                  <img
                    src={
                      item.image.startsWith("http")
                        ? item.image
                        : `http://localhost:6969/storage/${item.image}`
                    }
                    alt={item.title}
                    className="card-img-top"
                    style={{ objectFit: 'cover', height: '180px' }}
                  />
                )}

                <div className="card-body d-flex flex-column">
                  <h5 className="card-title text-primary">
                    <NavLink
                      to={`/berita/${item.slug}`}
                      className="text-decoration-none text-primary"
                    >
                      {item.title}
                    </NavLink>
                  </h5>

                  <p className="card-text small text-muted">
                    {item.excerpt}
                  </p>

                  <div className="mt-auto small">
                    <span className="badge bg-secondary me-1">
                      {item.category?.name || '-'}
                    </span>
                    <span className="text-muted">
                      Oleh <strong>{item.user?.name || '-'}</strong> <br />
                      {item.published_at
                        ? new Date(item.published_at).toLocaleDateString('id-ID')
                        : 'Belum dipublikasikan'}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    );
  }

  // =============== ✅ Layout HOMEPAGE PORTAL ===============
  const headline = berita[0];
  const smallNews = berita.slice(1, 5);
  const terbaru = berita.slice(0, 5);

  return (
    <div className="container my-4">
      <div className="row">
        {/* Kiri: headline + grid small news */}
        <div className="col-lg-8">
          {/* Headline */}
          <div className="mb-4">
            <div className="card border-0 shadow-sm">
              {headline.image && (
                <img
                  src={
                    headline.image.startsWith("http")
                      ? headline.image
                      : `http://localhost:6969/storage/${headline.image}`
                  }
                  className="card-img-top"
                  alt={headline.title}
                  style={{ maxHeight: "400px", objectFit: "cover" }}
                />
              )}
              <div className="card-body">
                <span className="badge bg-primary mb-2">
                  {headline.category?.name}
                </span>
                <h2 className="card-title">
                  <NavLink
                    to={`/berita/${headline.slug}`}
                    className="text-decoration-none text-dark"
                  >
                    {headline.title}
                  </NavLink>
                </h2>
                <p className="text-muted">
                  Oleh {headline.user?.name || "-"} •{" "}
                  {headline.published_at &&
                    new Date(headline.published_at).toLocaleDateString("id-ID")}
                </p>
                <p>{headline.excerpt}</p>
              </div>
            </div>
          </div>

          {/* Small news grid */}
          <div className="row g-3">
            {smallNews.map((item) => (
              <div className="col-6" key={item.id}>
                <div className="card h-100 border-0 shadow-sm">
                  {item.image && (
                    <img
                      src={
                        item.image.startsWith("http")
                          ? item.image
                          : `http://localhost:6969/storage/${item.image}`
                      }
                      className="card-img-top"
                      alt={item.title}
                      style={{
                        height: "150px",
                        objectFit: "cover",
                      }}
                    />
                  )}
                  <div className="card-body p-2 d-flex flex-column">
                    <span className="badge bg-secondary mb-1">
                      {item.category?.name}
                    </span>
                    <h6 className="card-title mb-1">
                      <NavLink
                        to={`/berita/${item.slug}`}
                        className="text-decoration-none text-dark"
                      >
                        {item.title}
                      </NavLink>
                    </h6>
                    <p className="small text-muted mb-0">
                      Oleh {item.user?.name || "-"}
                    </p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Kanan: Berita Terbaru */}
        <div className="col-lg-4 mt-4 mt-lg-0">
          <h5 className="mb-3 fw-bold">Berita Terbaru</h5>
          {terbaru.map((item, index) => (
            <div key={item.id} className="mb-3 border-bottom pb-2">
              <div className="d-flex">
                <span className="fw-bold me-2 text-primary">
                  {index + 1}.
                </span>
                <div>
                  <NavLink
                    to={`/berita/${item.slug}`}
                    className="text-decoration-none text-dark fw-medium"
                  >
                    {item.title}
                  </NavLink>
                  <div className="small text-muted">
                    {item.category?.name?.toUpperCase()} •{" "}
                    {item.created_at &&
                      new Date(item.created_at).toLocaleDateString("id-ID")}
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default HomePage;
