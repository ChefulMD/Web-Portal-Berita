import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { news } from '../api';

const DetailPage = () => {
  const { identifier } = useParams();
  const [berita, setBerita] = useState(null);

  const loadNewsDetails = async () => {
    try {
      const response = await news.getNewsDetails(identifier);
      setBerita(response.data);
    } catch (err) {
      console.error("Gagal memuat detail berita:", err);
    }
  };

  useEffect(() => {
    loadNewsDetails();
  }, [identifier]);

  if (!berita) {
    return (
      <div className="container py-4">
        <h2>Memuat Detail Berita...</h2>
        <p>Jika terlalu lama, mungkin berita tidak ditemukan atau ada masalah koneksi.</p>
      </div>
    );
  }

  return (
    <div className="container py-4">
      <h1 className="mb-3">{berita.title}</h1>
      <p className="text-muted mb-1">
        Kategori: {berita.category ? berita.category.name : '-'}
      </p>
      <p className="text-muted mb-1">
        Oleh: {berita.user ? berita.user.name : '-'}
      </p>
      <p className="text-muted mb-3">
        Tanggal Publikasi: {berita.published_at 
          ? new Date(berita.published_at).toLocaleDateString('id-ID') 
          : '-'}
      </p>

      {berita.image && (
        <div className="text-center mb-4">
          <img
            src={`http://localhost:6969/storage/${berita.image}`}
            alt={berita.title}
            style={{
              maxWidth: '100%',
              width: '600px',
              height: 'auto',
              borderRadius: '8px',
              boxShadow: '0 0 10px rgba(0,0,0,0.1)',
            }}
          />
        </div>
      )}

      <div
        dangerouslySetInnerHTML={{ __html: berita.content }}
      ></div>
    </div>
  );
};

export default DetailPage;
