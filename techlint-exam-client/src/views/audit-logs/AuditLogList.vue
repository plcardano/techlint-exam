<template>
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Audit Logs</h2>
        <div class="flex items-center space-x-2">
          <!-- Additional filters could go here -->
        </div>
      </div>
  
      <Card>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Action</TableHead>
                <TableHead>User</TableHead>
                <TableHead>Entity</TableHead>
                <TableHead>Details</TableHead>
                <TableHead>Date</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="loading">
                <TableCell colspan="5" class="text-center py-6">
                  <div class="flex justify-center items-center">
                    <i-lucide-loader-2 class="h-6 w-6 animate-spin mr-2" />
                    <span>Loading...</span>
                  </div>
                </TableCell>
              </TableRow>
              <TableRow v-else-if="!auditLogStore.auditLogs.length">
                <TableCell colspan="5" class="text-center py-6">
                  No audit logs found.
                </TableCell>
              </TableRow>
              <TableRow v-for="log in auditLogStore.auditLogs" :key="log.id">
                <TableCell>
                  <Badge :variant="getActionBadgeVariant(log.action)">
                    {{ formatAction(log.action) }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div class="flex items-center space-x-2">
                    <Avatar class="h-6 w-6">
                      <AvatarFallback class="text-xs">
                        {{ getUserInitials(log.user.name) }}
                      </AvatarFallback>
                    </Avatar>
                    <span>{{ log.user.name }}</span>
                  </div>
                </TableCell>
                <TableCell>
                  <div v-if="log.entity_type">
                    <Badge variant="outline">
                      {{ formatEntityType(log.entity_type) }}
                    </Badge>
                    <span v-if="log.entity_id" class="ml-2 text-sm">
                      #{{ log.entity_id }}
                    </span>
                  </div>
                  <span v-else>-</span>
                </TableCell>
                <TableCell>
                  <Button variant="ghost" size="sm" @click="viewLogDetails(log)">
                    View Details
                  </Button>
                </TableCell>
                <TableCell>{{ formatDate(log.created_at) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
        <div v-if="auditLogStore.paginationMeta && auditLogStore.paginationMeta.last_page > 1" class="py-4 px-6">
          <PaginationControls
            :current-page="currentPage"
            :total-pages="auditLogStore.paginationMeta.last_page"
            @page-change="changePage"
          />
        </div>
      </Card>
  
      <!-- Log Details Dialog -->
      <Dialog :open="showLogDetailsDialog" @update:open="showLogDetailsDialog = $event">
        <DialogContent class="sm:max-w-2xl">
          <DialogHeader>
            <DialogTitle>Audit Log Details</DialogTitle>
            <DialogDescription v-if="selectedLog">
              {{ formatAction(selectedLog.action) }} by {{ selectedLog.user.name }} on {{ formatDate(selectedLog.created_at) }}
            </DialogDescription>
          </DialogHeader>
          <div v-if="selectedLog" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <h4 class="font-medium">User</h4>
                <p>{{ selectedLog.user.name }} ({{ selectedLog.user.email }})</p>
              </div>
              <div>
                <h4 class="font-medium">IP Address</h4>
                <p>{{ selectedLog.ip_address }}</p>
              </div>
              <div>
                <h4 class="font-medium">Entity Type</h4>
                <p>{{ formatEntityType(selectedLog.entity_type) }}</p>
              </div>
              <div>
                <h4 class="font-medium">Entity ID</h4>
                <p>{{ selectedLog.entity_id || 'N/A' }}</p>
              </div>
              <div>
                <h4 class="font-medium">Action</h4>
                <p>{{ formatAction(selectedLog.action) }}</p>
              </div>
              <div>
                <h4 class="font-medium">Timestamp</h4>
                <p>{{ formatDate(selectedLog.created_at) }}</p>
              </div>
            </div>
  
            <div v-if="selectedLog.old_values || selectedLog.new_values" class="border rounded-md p-4 space-y-4">
              <h4 class="font-medium">Changes</h4>
              
              <div v-if="selectedLog.action === 'create'">
                <h5 class="text-sm font-medium text-muted-foreground mb-2">New Values</h5>
                <pre class="bg-muted p-2 rounded text-sm overflow-auto">{{ JSON.stringify(selectedLog.new_values, null, 2) }}</pre>
              </div>
              
              <div v-else-if="selectedLog.action === 'update'" class="space-y-4">
                <div v-if="selectedLog.old_values">
                  <h5 class="text-sm font-medium text-muted-foreground mb-2">Old Values</h5>
                  <pre class="bg-muted p-2 rounded text-sm overflow-auto">{{ JSON.stringify(selectedLog.old_values, null, 2) }}</pre>
                </div>
                <div v-if="selectedLog.new_values">
                  <h5 class="text-sm font-medium text-muted-foreground mb-2">New Values</h5>
                  <pre class="bg-muted p-2 rounded text-sm overflow-auto">{{ JSON.stringify(selectedLog.new_values, null, 2) }}</pre>
                </div>
              </div>
              
              <div v-else-if="selectedLog.action === 'delete'">
                <h5 class="text-sm font-medium text-muted-foreground mb-2">Deleted Values</h5>
                <pre class="bg-muted p-2 rounded text-sm overflow-auto">{{ JSON.stringify(selectedLog.old_values, null, 2) }}</pre>
              </div>
            </div>
          </div>
          <DialogFooter>
            <Button @click="showLogDetailsDialog = false">Close</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </template>
  
  <script setup lang="ts">
  import { ref, computed, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useAuditLogStore } from '@/stores/auditLog';
  import { format } from 'date-fns';
  import { useToast } from '@/components/ui/toast/use-toast';
  import { Button } from '@/components/ui/button';
  import { Card, CardContent } from '@/components/ui/card';
  import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
  } from '@/components/ui/table';
  import { Badge } from '@/components/ui/badge';
  import { Avatar, AvatarFallback } from '@/components/ui/avatar';
  import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
  } from '@/components/ui/dialog';
  import PaginationControls from '@/components/PaginationControls.vue';
  import { AuditLog } from '@/types';
  
  const route = useRoute();
  const router = useRouter();
  const auditLogStore = useAuditLogStore();
  const { toast } = useToast();
  
  const loading = ref(true);
  const showLogDetailsDialog = ref(false);
  const selectedLog = ref<AuditLog | null>(null);
  
  const currentPage = computed(() => {
    return Number(route.query.page) || 1;
  });
  
  onMounted(async () => {
    try {
      await auditLogStore.fetchAuditLogs(currentPage.value);
    } catch (error) {
      toast({
        title: 'Error loading audit logs',
        description: 'Please try again later',
        variant: 'destructive',
      });
    } finally {
      loading.value = false;
    }
  });
  
  const formatDate = (dateString: string) => {
    return format(new Date(dateString), 'MMM d, yyyy h:mm a');
  };
  
  const formatAction = (action: string) => {
    switch (action) {
      case 'create':
        return 'Created';
      case 'update':
        return 'Updated';
      case 'delete':
        return 'Deleted';
      case 'login':
        return 'Login';
      case 'logout':
        return 'Logout';
      default:
        return action.charAt(0).toUpperCase() + action.slice(1);
    }
  };
  
  const formatEntityType = (entityType: string) => {
    if (!entityType) return '';
    
    switch (entityType) {
      case 'ip_address':
        return 'IP Address';
      case 'user':
        return 'User';
      default:
        // Convert snake_case to Title Case
        return entityType
          .split('_')
          .map(word => word.charAt(0).toUpperCase() + word.slice(1))
          .join(' ');
    }
  };
  
  const getActionBadgeVariant = (action: string) => {
    switch (action) {
      case 'create':
        return 'outline';
      case 'update':
        return 'secondary';
      case 'delete':
        return 'destructive';
      case 'login':
      case 'logout':
        return 'default';
      default:
        return 'default';
    }
  };
  
  const getUserInitials = (name: string) => {
    const nameParts = name.split(' ');
    if (nameParts.length >= 2) {
      return `${nameParts[0][0]}${nameParts[1][0]}`;
    }
    return nameParts[0][0];
  };
  
  const viewLogDetails = (log: AuditLog) => {
    selectedLog.value = log;
    showLogDetailsDialog.value = true;
  };
  
  const changePage = async (page: number) => {
    loading.value = true;
    try {
      await router.push({ query: { ...route.query, page: page.toString() } });
      await auditLogStore.fetchAuditLogs(page);
    } catch (error) {
      toast({
        title: 'Error loading page',
        description: 'Please try again later',
        variant: 'destructive',
      });
    } finally {
      loading.value = false;
    }
  };
  </script>