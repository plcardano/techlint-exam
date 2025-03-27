import { defineStore } from 'pinia';
import apiClient from '@/services/api';
import type { IpAddress, PaginatedResponse } from '@/types';

export const useIpAddressStore = defineStore('ipAddress', {
  state: () => ({
    ipAddresses: [] as IpAddress[],
    currentIpAddress: null as IpAddress | null,
    loading: false,
    paginationMeta: null as PaginatedResponse<IpAddress>['meta'] | null,
  }),
  actions: {
    async fetchIpAddresses(page = 1, perPage = 10) {
      this.loading = true;
      try {
        const response = await apiClient.get<PaginatedResponse<IpAddress>>('/v1/ip-address', {
          params: { page, per_page: perPage }
        });
        this.ipAddresses = response.data.data;
        this.paginationMeta = response.data.meta;
      } catch (error) {
        console.error('Error fetching IP addresses:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async fetchIpAddress(id: number) {
      this.loading = true;
      try {
        const response = await apiClient.get<{ data: IpAddress }>(`/v1/ip-address/${id}/show`);
        this.currentIpAddress = response.data.data;
        return response.data.data;
      } catch (error) {
        console.error(`Error fetching IP address ${id}:`, error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async createIpAddress(data: Omit<IpAddress, 'id' | 'created_by' | 'created_at' | 'updated_at'>) {
      this.loading = true;
      try {
        const response = await apiClient.post<{ data: IpAddress }>('/v1/ip-address/store', data);
        return response.data.data;
      } catch (error) {
        console.error('Error creating IP address:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async updateIpAddress(id: number, data: Pick<IpAddress, 'label' | 'comment'>) {
      this.loading = true;
      try {
        const response = await apiClient.put<{ data: IpAddress }>(`/v1/ip-address/${id}/update`, data);
        if (this.currentIpAddress && this.currentIpAddress.id === id) {
          this.currentIpAddress = response.data.data;
        }
        return response.data.data;
      } catch (error) {
        console.error(`Error updating IP address ${id}:`, error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async deleteIpAddress(id: number) {
      this.loading = true;
      try {
        await apiClient.delete(`/v1/ip-address/${id}/delete`);
        if (this.currentIpAddress && this.currentIpAddress.id === id) {
          this.currentIpAddress = null;
        }
        return true;
      } catch (error) {
        console.error(`Error deleting IP address ${id}:`, error);
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});