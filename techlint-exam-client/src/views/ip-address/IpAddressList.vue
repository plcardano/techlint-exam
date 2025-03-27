<template>
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">IP Addresses</h2>
        <Button @click="router.push('/ip-addresses/create')">
          <i-lucide-plus class="h-4 w-4 mr-2" />
          Add IP Address
        </Button>
      </div>
  
      <Card>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>IP Address</TableHead>
                <TableHead>Label</TableHead>
                <TableHead>Added By</TableHead>
                <TableHead>Date Added</TableHead>
                <TableHead class="text-right">Actions</TableHead>
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
              <TableRow v-else-if="!ipAddressStore.ipAddresses.length">
                <TableCell colspan="5" class="text-center py-6">
                  No IP addresses found. Add an IP address to get started.
                </TableCell>
              </TableRow>
              <TableRow v-for="ip in ipAddressStore.ipAddresses" :key="ip.id">
                <TableCell class="font-mono">{{ ip.ip_address }}</TableCell>
                <TableCell>{{ ip.label }}</TableCell>
                <TableCell>{{ ip.createdBy?.name }}</TableCell>
                <TableCell>{{ formatDate(ip.created_at) }}</TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end space-x-2">
                    <Button variant="ghost" size="icon" @click="viewIpAddress(ip.id)">
                      <i-lucide-eye class="h-4 w-4" />
                      <span class="sr-only">View</span>
                    </Button>
                    <Button variant="ghost" size="icon" @click="editIpAddress(ip.id)">
                      <i-lucide-pencil class="h-4 w-4" />
                      <span class="sr-only">Edit</span>
                    </Button>
                    <Button 
                      v-if="isAdmin" 
                      variant="ghost" 
                      size="icon" 
                      @click="confirmDelete(ip)"
                    >
                      <i-lucide-trash class="h-4 w-4 text-destructive" />
                      <span class="sr-only">Delete</span>
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
        <div v-if="ipAddressStore.paginationMeta && ipAddressStore.paginationMeta.last_page > 1" class="py-4 px-6">
          <PaginationControls
            :current-page="currentPage"
            :total-pages="ipAddressStore.paginationMeta.last_page"
            @page-change="changePage"
          />
        </div>
      </Card>
  
      <!-- Delete Confirmation Dialog -->
      <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Are you sure?</AlertDialogTitle>
            <AlertDialogDescription>
              This will permanently delete the IP address "{{ ipToDelete?.label }}" ({{ ipToDelete?.ip_address }}).
              This action cannot be undone.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>Cancel</AlertDialogCancel>
            <AlertDialogAction @click="deleteIpAddress" :disabled="isDeleting">
              <i-lucide-loader-2 v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
              {{ isDeleting ? 'Deleting...' : 'Delete' }}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </div>
  </template>
  
  <script setup lang="ts">
  import { ref, computed, onMounted } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import { useIpAddressStore } from '@/stores/ipAddress';
  import { useAuthStore } from '@/stores/auth';
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
  import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
  } from '@/components/ui/alert-dialog';
  import { IpAddress } from '@/types';
  import PaginationControls from '@/components/PaginationControls.vue';
  
  const router = useRouter();
  const route = useRoute();
  const ipAddressStore = useIpAddressStore();
  const authStore = useAuthStore();
  const { toast } = useToast();
  
  const loading = ref(true);
  const showDeleteDialog = ref(false);
  const ipToDelete = ref<IpAddress | null>(null);
  const isDeleting = ref(false);
  
  const isAdmin = computed(() => authStore.isAdmin);
  
  const currentPage = computed(() => {
    return Number(route.query.page) || 1;
  });
  
  onMounted(async () => {
    try {
      await ipAddressStore.fetchIpAddresses(currentPage.value);
    } catch (error) {
      toast({
        title: 'Error loading IP addresses',
        description: 'Please try again later',
        variant: 'destructive',
      });
    } finally {
      loading.value = false;
    }
  });
  
  const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    
    try {
      // Check if the date is valid
      const date = new Date(dateString);
      if (isNaN(date.getTime())) {
        return 'Invalid date';
      }
      return format(date, 'MMM d, yyyy');
    } catch (error) {
      console.error('Error formatting date:', dateString, error);
      return 'Invalid date';
    }
  };
  
  const viewIpAddress = (id: number) => {
    router.push(`/ip-addresses/${id}`);
  };
  
  const editIpAddress = (id: number) => {
    router.push(`/ip-addresses/${id}?edit=true`);
  };
  
  const confirmDelete = (ip: IpAddress) => {
    ipToDelete.value = ip;
    showDeleteDialog.value = true;
  };
  
  const deleteIpAddress = async () => {
    if (!ipToDelete.value) return;
    
    isDeleting.value = true;
    try {
      await ipAddressStore.deleteIpAddress(ipToDelete.value.id);
      toast({
        title: 'IP address deleted',
        description: `${ipToDelete.value.ip_address} was deleted successfully`,
      });
      // Refresh the list
      await ipAddressStore.fetchIpAddresses(currentPage.value);
    } catch (error) {
      toast({
        title: 'Error deleting IP address',
        description: 'Please try again later',
        variant: 'destructive',
      });
    } finally {
      isDeleting.value = false;
      showDeleteDialog.value = false;
      ipToDelete.value = null;
    }
  };
  
  const changePage = async (page: number) => {
    loading.value = true;
    try {
      await router.push({ query: { ...route.query, page: page.toString() } });
      await ipAddressStore.fetchIpAddresses(page);
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