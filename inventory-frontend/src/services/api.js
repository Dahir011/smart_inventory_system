/**
 * API Service
 * Centralized API communication with backend
 */

import axios from 'axios';

const API_BASE_URL = 'http://localhost/inventory-backend/api';

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add auth token to requests
api.interceptors.request.use((config) => {
  const user = localStorage.getItem('user');
  if (user) {
    const userData = JSON.parse(user);
    // Simple token format: user_id:role
    config.headers.Authorization = `Bearer ${userData.id}:${userData.role}`;
  }
  return config;
});

// Handle response errors
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Unauthorized - redirect to login
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

// Auth APIs
export const authAPI = {
  login: (username, password) =>
    api.post('/auth?action=login', { username, password }),
  
  register: (userData) =>
    api.post('/auth?action=register', userData),
};

// User APIs
export const userAPI = {
  getAll: () => api.get('/users'),
  getById: (id) => api.get(`/users?id=${id}`),
  update: (id, data) => api.put(`/users?id=${id}`, data),
  delete: (id) => api.delete(`/users?id=${id}`),
};

// Product APIs
export const productAPI = {
  getAll: (filters = {}) => {
    const params = new URLSearchParams();
    Object.keys(filters).forEach(key => {
      if (filters[key]) params.append(key, filters[key]);
    });
    return api.get(`/products?${params.toString()}`);
  },
  getById: (id) => api.get(`/products?id=${id}`),
  create: (data) => api.post('/products', data),
  update: (id, data) => api.put(`/products?id=${id}`, data),
  delete: (id) => api.delete(`/products?id=${id}`),
};

// Category APIs
export const categoryAPI = {
  getAll: () => api.get('/categories'),
  getById: (id) => api.get(`/categories?id=${id}`),
  create: (data) => api.post('/categories', data),
  update: (id, data) => api.put(`/categories?id=${id}`, data),
  delete: (id) => api.delete(`/categories?id=${id}`),
};

// Dashboard APIs
export const dashboardAPI = {
  getStats: () => api.get('/dashboard?action=stats'),
  getCategories: () => api.get('/dashboard?action=categories'),
  getMonthlyChanges: () => api.get('/dashboard?action=monthly-changes'),
  getFastMoving: () => api.get('/dashboard?action=fast-moving'),
  getSlowMoving: () => api.get('/dashboard?action=slow-moving'),
};

// Alert APIs
export const alertAPI = {
  getAll: (unreadOnly = false) => 
    api.get(`/alerts${unreadOnly ? '?unread_only=1' : ''}`),
  check: () => api.get('/alerts?action=check'),
  markAsRead: (id) => api.put(`/alerts?action=read&id=${id}`),
  markAllAsRead: () => api.put('/alerts?action=read-all'),
};

// AI Analytics APIs
export const aiAnalyticsAPI = {
  getDailyUsage: (productId, days = 30) =>
    api.get(`/ai-analytics?action=daily-usage&product_id=${productId}&days=${days}`),
  getStockoutPrediction: (productId) =>
    api.get(`/ai-analytics?action=stockout-prediction&product_id=${productId}`),
  getRestockRecommendation: (productId, leadTime = 7, safetyStock = 20) =>
    api.get(`/ai-analytics?action=restock-recommendation&product_id=${productId}&lead_time=${leadTime}&safety_stock=${safetyStock}`),
  getUsageTrend: (productId, days = 30) =>
    api.get(`/ai-analytics?action=usage-trend&product_id=${productId}&days=${days}`),
  getInsights: (productId) =>
    api.get(`/ai-analytics?action=insights&product_id=${productId}`),
};

export default api;
