import { defineStore } from 'pinia';
import apiClient from '@/services/api';
import type { AuthResponse, User } from '@/types';

export const useAuthStore = defineStore('auth', {
  state: () => {
    let user = null;
    const userStr = localStorage.getItem('user');
    if (userStr) {
      try {
        user = JSON.parse(userStr);
      } catch (e) {
        console.error('Failed to parse user from localStorage');
        localStorage.removeItem('user'); 
      }
    }

    return {
      token: localStorage.getItem('token') || '',
      user: user as User | null,
    };
  },
  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    isAdmin: (state) => state.user?.role === 'super-admin',
  },
  actions: {
    setToken(token: string) {
      this.token = token;
      localStorage.setItem('token', token);
    },
    setUser(user: User) {
      this.user = user;
      localStorage.setItem('user', JSON.stringify(user));
    },

    async login(email: string, password: string) {
      try {
        const response = await apiClient.post<AuthResponse>('/v1/auth/login', { email, password });
        this.setToken(response.data.access_token);
        this.setUser(response.data.user);
        return true;
      } catch (error) {
        this.logout();
        throw error;
      }
    },
    
    logout() {
      if (this.token) {
        try {
          apiClient.post('/v1/auth/logout').catch(() => {});
        } catch (error) {
          console.error('Error during logout:', error);
        }
      }
      this.token = '';
      this.user = null;
      localStorage.removeItem('token');
      localStorage.removeItem('user');
    },
  },
});