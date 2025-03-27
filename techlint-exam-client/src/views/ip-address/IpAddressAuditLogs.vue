<template>
    <Card>
      <CardContent class="p-0">
        <div v-if="loading" class="flex justify-center items-center py-8">
          <i-lucide-loader-2 class="h-6 w-6 animate-spin" />
        </div>
        <div v-else>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Action</TableHead>
                <TableHead>User</TableHead>
                <TableHead>Changes</TableHead>
                <TableHead>Date</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="!auditLogs.length">
                <TableCell colspan="4" class="text-center py-6">
                  No audit logs found for this IP address.
                </TableCell>
              </TableRow>
              <TableRow v-for="log in auditLogs" :key="log.id">
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
                  <div v-if="log.action === 'create'">
                    <span class="text-sm">Created with label: <strong>{{ log.new_values?.label }}</strong></span>
                  </div>
                  <div v-else-if="log.action === 'update'">
                    <div v-if="log.old_values?.label !== log.new_values?.label" class="text-sm">
                      Label: <strong>{{ log.old_values?.label }}</strong> → <strong>{{ log.new_values?.label }}</strong>
                    </div>
                    <div v-if="log.old_values?.comment !== log.new_values?.comment" class="text-sm">
                      Comment updated
                    </div>
                  </div>
                  <div v-else-if="log.action === 'delete'">
                    <span class="text-sm">IP address deleted</span>
                  </div>
                </TableCell>
                <TableCell>{{ formatDate(log.created_at) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
      <div v-if="paginationMeta && paginationMeta.last_page > 1" class="py-4 px-6">
        <PaginationControls
          :current-page="currentPage"
          :total-pages="paginationMeta.last_page"
          @page-change="changePage"
        />
      </div>
    </Card>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useAuditLogStore } from '@/stores/auditLog';
import { format } from 'date-fns';
import { useToast } from '@/components/ui/toast/use-toast';
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
import PaginationControls from '@/components/PaginationControls.vue';
import { AuditLog, PaginationMeta } from '@/types';

const props = defineProps<{
  ipAddressId: string;
}>();

const auditLogStore = useAuditLogStore();
const { toast } = useToast();

const loading = ref(true);
const auditLogs = ref<AuditLog[]>([]);
const paginationMeta = ref<PaginationMeta | null>(null);
const currentPage = ref(1);

onMounted(async () => {
  await fetchAuditLogs();
});

const fetchAuditLogs = async () => {
  loading.value = true;
  try {
    const response = await auditLogStore.fetchAuditLogsForIpAddress(
      parseInt(props.ipAddressId),
      currentPage.value
    );
    auditLogs.value = response.data;
    paginationMeta.value = response.meta;
  } catch (error) {
    toast({
      title: 'Error loading audit logs',
      description: 'Please try again later',
      variant: 'destructive',
    });
  } finally {
    loading.value = false;
  }
};

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
    default:
      return action.charAt(0).toUpperCase() + action.slice(1);
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

const changePage = async (page: number) => {
  currentPage.value = page;
  await fetchAuditLogs();
};
</script>