import axios from 'axios';
import { useAuthStore } from './store';

const API_BASE = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE,
});

api.interceptors.request.use((config) => {
  const token = useAuthStore.getState().token;
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export const authAPI = {
  register: (email, password, name) =>
    api.post('/auth/register', { email, password, name }),
  
  login: (email, password) =>
    api.post('/auth/login', { email, password }),
};

export const deviceAPI = {
  create: (data) => api.post('/devices', data),
  
  getList: () => api.get('/devices'),
  
  getPublic: (id) => api.get(`/devices/${id}`),
  
  getFullPublic: (id) => api.get(`/devices/${id}/full`),
  
  update: (id, data) => api.put(`/devices/${id}`, data),
  
  delete: (id) => api.delete(`/devices/${id}`),
  
  generateQR: (id) => api.post(`/devices/${id}/generate-qr`),
};

export const recordAPI = {
  create: (deviceId, data) =>
    api.post(`/devices/${deviceId}/records`, data),
  
  getList: (deviceId) =>
    api.get(`/devices/${deviceId}/records`),
};

export default api;
