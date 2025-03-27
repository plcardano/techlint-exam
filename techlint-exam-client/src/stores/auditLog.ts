import { defineStore } from 'pinia';
import apiClient from '@/services/api';
import type { AuditLog, PaginatedResponse } from '@/types';

export const useAuditLogStore = defineStore('auditLog', {
  state: () => ({
    auditLogs: [] as AuditLog[],
    loading: false,
    paginationMeta: null as PaginatedResponse<AuditLog>['meta'] | null,
  }),
  actions: {
    async fetchAuditLogs(page = 1, perPage = 10) {
      this.loading = true;
      try {
        const response = await apiClient.get<PaginatedResponse<AuditLog>>('/v1/audit-logs', {
          params: { page, per_page: perPage }
        });
        this.auditLogs = response.data.data;
        this.paginationMeta = response.data.meta;
      } catch (error) {
        console.error('Error fetching audit logs:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async fetchAuditLogsForIpAddress(ipAddressId: number, page = 1, perPage = 10) {
      this.loading = true;
      try {
        const response = await apiClient.get<PaginatedResponse<AuditLog>>(`/v1/audit-logs/ip-address/${ipAddressId}`, {
          params: { page, per_page: perPage }
        });
        return response.data;
      } catch (error) {
        console.error(`Error fetching audit logs for IP address ${ipAddressId}:`, error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async fetchAuditLogsForUser(userId: number, page = 1, perPage = 10) {
      this.loading = true;
      try {
        const response = await apiClient.get<PaginatedResponse<AuditLog>>(`/v1/audit-logs/user/${userId}`, {
          params: { page, per_page: perPage }
        });
        return response.data;
      } catch (error) {
        console.error(`Error fetching audit logs for user ${userId}:`, error);
        throw error;
      } finally {
        this.loading = false;
      }
    }
  }
});