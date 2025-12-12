import axios from 'axios';

// URL dasar API backend Anda
const API_BASE = 'http://localhost:8000/api';

// Instance Axios global (dengan interceptor token)
const apiClient = axios.create({
  baseURL: API_BASE,
  headers: {
    'Accept': 'application/json',
  },
});

// Interceptor untuk melampirkan token JWT
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('jwt_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);


// --- PANGGILAN API UNTUK AUTENTIKASI ---
export const auth = {
  login: (credentials) => apiClient.post('/auth/login', credentials),
  register: (userData) => apiClient.post('/auth/register', userData),
  logout: () => apiClient.post('/auth/logout'),
  refreshToken: () => apiClient.post('/auth/refresh'),
  getUserProfile: () => apiClient.get('/auth/user-profile'),
};

// --- PANGGILAN API UNTUK BERITA (Sesuai Controller Anda) ---
export const news = {
  getAllNews: (page = 1) => apiClient.get('/berita', { params: { page } }),
  getNewsDetails: (identifier) => apiClient.get(`/berita/${identifier}`),
  searchNews: (keyword, page = 1) => apiClient.get('/berita/search', { params: { q: keyword, page } }),
  getFilteredNews: (filterParams = {}, page = 1) => {
    return apiClient.get('/berita/filter', { params: { ...filterParams, page } });
  },

  createNews: (newsData) => apiClient.post('/berita', newsData),
  updateNews: (id, newsData) => apiClient.post(`/berita/${id}`, newsData),
  deleteNews: (id) => apiClient.delete(`/berita/${id}`),

  // --- FUNGSI BARU UNTUK MANAJEMEN BERITA ---
  getManagedNews: (params = {}) => apiClient.get('/berita/manajemen', { params }), // <-- Tambahkan ini
  // --- AKHIR FUNGSI BARU ---
};

// --- PANGGILAN API UNTUK KATEGORI (Hanya untuk mengambil daftar, jika ada endpointnya) ---
// Karena di backend Anda tidak ada CategoryController yang diimpor atau rute '/categories',
// bagian ini saya asumsikan SEMENTARA tidak ada endpoint API di backend Anda.
// Jika Anda membuat CategoryController nanti, baru tambahkan ini.
export const categories = {
  getAllCategories: () => apiClient.get('/categories'), // Ini yang akan dipanggil di HomePage.jsx
  // Anda bisa tambahkan getCategoryDetails, create, update, delete kategori jika diperlukan
};

// Anda bisa mengekspor apiClient jika ingin menggunakannya langsung
// export default apiClient;

export const admin = {
  getDashboardStats: () => apiClient.get('/admin/dashboard/stats'),
};
