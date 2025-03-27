import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
import router from '@/router';

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

apiClient.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore();
    if (authStore.token) {
      console.log('Adding token to request:', config.url);
      config.headers.Authorization = `Bearer ${authStore.token}`;
    } else {
      console.log('No token available for request:', config.url);
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

apiClient.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error) => {
    const originalRequest = error.config;
    const authStore = useAuthStore();

    if (error.response?.status === 401 && !originalRequest._retry && authStore.token) {
      originalRequest._retry = true;

      try {
        const response = await apiClient.post('/v1/auth/refresh');
        const newToken = response.data.access_token;

        // Save new token
        authStore.setToken(newToken);
        
        originalRequest.headers.Authorization = `Bearer ${newToken}`;
        return apiClient(originalRequest);
      } catch (refreshError) {
        authStore.logout();
        router.push('/login');
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

export default apiClient;