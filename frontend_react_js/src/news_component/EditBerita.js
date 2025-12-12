import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { news, categories } from '../api';

const EditBeritaPage = () => {
  const { id } = useParams();
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    title: '',
    content: '',
    category_id: '',
    status: 'draft',
  });
  const [imageFile, setImageFile] = useState(null);
  const [existingImage, setExistingImage] = useState(null);
  const [removeExistingImage, setRemoveExistingImage] = useState(false);

  const [daftarKategori, setDaftarKategori] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [kategoriRes, newsRes] = await Promise.all([
          categories.getAllCategories(),
          news.getNewsDetails(id)
        ]);
        setDaftarKategori(kategoriRes.data);
        setFormData({
          title: newsRes.data.title || '',
          content: newsRes.data.content || '',
          category_id: newsRes.data.category_id?.toString() || '',
          status: newsRes.data.status || 'draft',
        });
        setExistingImage(newsRes.data.image);
      } catch (err) {
        console.error(err);
        setError("Gagal memuat data.");
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [id]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const handleImageChange = (e) => {
    setImageFile(e.target.files[0]);
    setRemoveExistingImage(false);
  };

  const handleRemoveExistingImage = (e) => {
    setRemoveExistingImage(e.target.checked);
    if (e.target.checked) {
      setImageFile(null);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setError(null);
    setSuccessMessage(null);

    const dataToSend = new FormData();
    dataToSend.append('title', formData.title);
    dataToSend.append('content', formData.content);
    dataToSend.append('category_id', formData.category_id);
    dataToSend.append('status', formData.status);

    if (imageFile) {
      dataToSend.append('image', imageFile);
    } else if (removeExistingImage) {
      dataToSend.append('image_remove', 'true');
    }

    dataToSend.append('_method', 'PUT');

    try {
      await news.updateNews(id, dataToSend);
      setSuccessMessage("Berita berhasil diperbarui!");
      setTimeout(() => navigate('/berita/manajemen'), 1500);
    } catch (err) {
      console.error(err);
      setError(err.response?.data?.message || "Terjadi kesalahan.");
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="container py-4">Memuat data...</div>;
  }

  return (
    <div className="container py-4">
      <h2 className="mb-4">Edit Berita</h2>

      {error && <div className="alert alert-danger">{error}</div>}
      {successMessage && <div className="alert alert-success">{successMessage}</div>}

      <form onSubmit={handleSubmit}>
        <div className="mb-3">
          <label htmlFor="title" className="form-label">Judul Berita</label>
          <input
            type="text"
            className="form-control"
            id="title"
            name="title"
            value={formData.title}
            onChange={handleChange}
            required
          />
        </div>

        <div className="mb-3">
          <label htmlFor="content" className="form-label">Isi Berita</label>
          <textarea
            className="form-control"
            id="content"
            name="content"
            rows="8"
            value={formData.content}
            onChange={handleChange}
            required
          ></textarea>
        </div>

        <div className="mb-3">
          <label htmlFor="category_id" className="form-label">Kategori</label>
          <select
            className="form-select"
            id="category_id"
            name="category_id"
            value={formData.category_id}
            onChange={handleChange}
            required
          >
            <option value="">Pilih Kategori</option>
            {daftarKategori.map((kat) => (
              <option key={kat.id} value={kat.id}>{kat.name}</option>
            ))}
          </select>
        </div>

        <div className="mb-3">
          <label htmlFor="status" className="form-label">Status</label>
          <select
            className="form-select"
            id="status"
            name="status"
            value={formData.status}
            onChange={handleChange}
            required
          >
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
        </div>

        <div className="mb-3">
          <label htmlFor="image" className="form-label">Gambar Utama</label>
          <input
            type="file"
            className="form-control"
            id="image"
            name="image"
            accept="image/*"
            onChange={handleImageChange}
          />
        </div>

        {existingImage && !imageFile && (
          <div className="mb-3">
            <p>Gambar Saat Ini:</p>
            <img
              src={`http://localhost:6969/storage/${existingImage}`}
              alt="Gambar Berita"
              className="img-thumbnail mb-2"
              style={{ maxWidth: '200px' }}
            />
            <div className="form-check">
              <input
                className="form-check-input"
                type="checkbox"
                id="removeImage"
                checked={removeExistingImage}
                onChange={handleRemoveExistingImage}
              />
              <label className="form-check-label" htmlFor="removeImage">
                Hapus Gambar Ini
              </label>
            </div>
          </div>
        )}

        <button type="submit" className="btn btn-primary" disabled={saving}>
          {saving ? 'Menyimpan...' : 'Update Berita'}
        </button>
      </form>
    </div>
  );
};

export default EditBeritaPage;
