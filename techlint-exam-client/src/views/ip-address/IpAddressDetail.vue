<template>
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <Button variant="ghost" size="icon" @click="router.back()">
            <i-lucide-arrow-left class="h-4 w-4" />
          </Button>
          <h2 class="text-2xl font-bold">{{ isNew ? 'Create IP Address' : 'IP Address Details' }}</h2>
        </div>
        <div class="flex space-x-2">
          <Button v-if="!isNew && !isEditing" variant="outline" @click="toggleEdit">
            <i-lucide-pencil class="h-4 w-4 mr-2" />
            Edit
          </Button>
          <Button 
            v-if="isAdmin && !isNew && !isEditing" 
            variant="destructive" 
            @click="confirmDelete"
          >
            <i-lucide-trash class="h-4 w-4 mr-2" />
            Delete
          </Button>
        </div>
      </div>
  
      <div v-if="loading" class="flex justify-center items-center py-12">
        <i-lucide-loader-2 class="h-8 w-8 animate-spin" />
      </div>
  
      <div v-else>
        <Tabs v-if="!isNew" default-value="details">
          <TabsList>
            <TabsTrigger value="details">Details</TabsTrigger>
            <TabsTrigger value="audit-logs">Audit Logs</TabsTrigger>
          </TabsList>
          <TabsContent value="details">
            <Card>
              <CardContent class="pt-6">
                <form @submit.prevent="saveIpAddress">
                  <div class="grid gap-6">
                    <div class="grid gap-3">
                      <Label for="ip_address">IP Address</Label>
                      <Input
                        id="ip_address"
                        v-model="form.ip_address"
                        :disabled="!isNew || isSaving"
                        placeholder="192.168.1.1"
                      />
                      <p v-if="v$.ip_address.$error" class="text-sm text-destructive">
                        {{ v$.ip_address.$errors[0].$message }}
                      </p>
                    </div>
                    
                    <div class="grid gap-3">
                      <Label for="label">Label</Label>
                      <Input
                        id="label"
                        v-model="form.label"
                        :disabled="!isEditing && !isNew || isSaving"
                        placeholder="Office Router"
                      />
                      <p v-if="v$.label.$error" class="text-sm text-destructive">
                        {{ v$.label.$errors[0].$message }}
                      </p>
                    </div>
                    
                    <div class="grid gap-3">
                      <Label for="comment">Comment</Label>
                      <Textarea
                        id="comment"
                        v-model="form.comment"
                        :disabled="!isEditing && !isNew || isSaving"
                        placeholder="Additional details about this IP address"
                        rows="4"
                      />
                    </div>
  
                    <div v-if="!isNew" class="grid grid-cols-2 gap-4 pt-2">
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">Created By</p>
                        <p>{{ ipAddressStore.currentIpAddress?.creator?.name }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">Created At</p>
                        <p>{{ formatDate(ipAddressStore.currentIpAddress?.created_at) }}</p>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-muted-foreground">Last Updated</p>
                        <p>{{ formatDate(ipAddressStore.currentIpAddress?.updated_at) }}</p>
                      </div>
                    </div>
                    
                    <div v-if="isEditing || isNew" class="flex justify-end space-x-2 pt-4">
                      <Button v-if="!isNew" type="button" variant="outline" @click="cancelEdit">
                        Cancel
                      </Button>
                      <Button type="submit" :disabled="isSaving">
                        <i-lucide-loader-2 v-if="isSaving" class="mr-2 h-4 w-4 animate-spin" />
                        {{ isSaving ? 'Saving...' : 'Save' }}
                      </Button>
                    </div>
                  </div>
                </form>
              </CardContent>
            </Card>
          </TabsContent>
          
          <TabsContent value="audit-logs">
            <IpAddressAuditLogs :ip-address-id="id" />
          </TabsContent>
        </Tabs>
  
        <!-- If new, just show the form without tabs -->
        <Card v-else>
          <CardContent class="pt-6">
            <form @submit.prevent="saveIpAddress">
              <div class="grid gap-6">
                <div class="grid gap-3">
                  <Label for="ip_address">IP Address</Label>
                  <Input
                    id="ip_address"
                    v-model="form.ip_address"
                    :disabled="isSaving"
                    placeholder="192.168.1.1"
                  />
                  <p v-if="v$.ip_address.$error" class="text-sm text-destructive">
                    {{ v$.ip_address.$errors[0].$message }}
                  </p>
                </div>
                
                <div class="grid gap-3">
                  <Label for="label">Label</Label>
                  <Input
                    id="label"
                    v-model="form.label"
                    :disabled="isSaving"
                    placeholder="Office Router"
                  />
                  <p v-if="v$.label.$error" class="text-sm text-destructive">
                    {{ v$.label.$errors[0].$message }}
                  </p>
                </div>
                
                <div class="grid gap-3">
                  <Label for="comment">Comment</Label>
                  <Textarea
                    id="comment"
                    v-model="form.comment"
                    :disabled="isSaving"
                    placeholder="Additional details about this IP address"
                    rows="4"
                  />
                </div>
                
                <div class="flex justify-end space-x-2 pt-4">
                  <Button type="button" variant="outline" @click="router.back()">
                    Cancel
                  </Button>
                  <Button type="submit" :disabled="isSaving">
                    <i-lucide-loader-2 v-if="isSaving" class="mr-2 h-4 w-4 animate-spin" />
                    {{ isSaving ? 'Saving...' : 'Save' }}
                  </Button>
                </div>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
  
      <!-- Delete Confirmation Dialog -->
      <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Are you sure?</AlertDialogTitle>
            <AlertDialogDescription>
              This will permanently delete the IP address "{{ ipAddressStore.currentIpAddress?.label }}" 
              ({{ ipAddressStore.currentIpAddress?.ip_address }}).
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
  import { ref, computed, reactive, onMounted, watch } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import { useVuelidate } from '@vuelidate/core';
  import { required, helpers } from '@vuelidate/validators';
  import { useIpAddressStore } from '@/stores/ipAddress';
  import { useAuthStore } from '@/stores/auth';
  import { format } from 'date-fns';
  import { useToast } from '@/components/ui/toast/use-toast';
  import { Button } from '@/components/ui/button';
  import { Input } from '@/components/ui/input';
  import { Textarea } from '@/components/ui/textarea';
  import { Label } from '@/components/ui/label';
  import { Card, CardContent } from '@/components/ui/card';
  import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
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
  import IpAddressAuditLogs from './IpAddressAuditLogs.vue';
  
  const props = defineProps<{
    id?: string;
  }>();
  
  const router = useRouter();
  const route = useRoute();
  const ipAddressStore = useIpAddressStore();
  const authStore = useAuthStore();
  const { toast } = useToast();
  
  const loading = ref(true);
  const isSaving = ref(false);
  const isEditing = ref(false);
  const showDeleteDialog = ref(false);
  const isDeleting = ref(false);
  
  const isNew = computed(() => !props.id);
  const isAdmin = computed(() => authStore.isAdmin);
  
  const form = reactive({
    ip_address: '',
    label: '',
    comment: ''
  });
  
  const ipAddressValidator = helpers.regex(/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$|^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/);
  
  const rules = {
    ip_address: { 
      required, 
      validIp: helpers.withMessage('Please enter a valid IPv4 or IPv6 address', ipAddressValidator)
    },
    label: { required }
  };
  
  const v$ = useVuelidate(rules, form);
  
  onMounted(async () => {
    isEditing.value = route.query.edit === 'true';
    
    if (!isNew.value && props.id) {
      try {
        await ipAddressStore.fetchIpAddress(parseInt(props.id));
        if (ipAddressStore.currentIpAddress) {
          form.ip_address = ipAddressStore.currentIpAddress.ip_address;
          form.label = ipAddressStore.currentIpAddress.label;
          form.comment = ipAddressStore.currentIpAddress.comment || '';
        }
      } catch (error) {
        toast({
          title: 'Error loading IP address',
          description: 'Please try again later',
          variant: 'destructive',
        });
        router.push('/ip-addresses');
      }
    }
    
    loading.value = false;
  });
  
  const formatDate = (dateString?: string) => {
    if (!dateString) return 'N/A';
    return format(new Date(dateString), 'MMM d, yyyy h:mm a');
  };
  
  const toggleEdit = () => {
    isEditing.value = !isEditing.value;
  };
  
  const cancelEdit = () => {
    if (ipAddressStore.currentIpAddress) {
      form.label = ipAddressStore.currentIpAddress.label;
      form.comment = ipAddressStore.currentIpAddress.comment || '';
    }
    isEditing.value = false;
  };
  
  const saveIpAddress = async () => {
    const isValid = await v$.value.$validate();
    if (!isValid) return;
    
    isSaving.value = true;
    
    try {
      if (isNew.value) {
        const newIp = await ipAddressStore.createIpAddress({
          ip_address: form.ip_address,
          label: form.label,
          comment: form.comment
        });
        
        toast({
          title: 'IP address created',
          description: `${newIp.ip_address} was created successfully`,
        });
        
        router.push(`/ip-addresses/${newIp.id}`);
      } else if (props.id) {
        await ipAddressStore.updateIpAddress(parseInt(props.id), {
          label: form.label,
          comment: form.comment
        });
        console.log('ERROR', ipAddressStore.errorMessage)
        toast({
          title: 'IP address updated',
          description: `${form.ip_address} was updated successfully`,
        });
        
        isEditing.value = false;
      }
    } catch (error) {
      toast({
        title: 'Error saving IP address',
        description: 'Please try again later',
        variant: 'destructive',
      });
    } finally {
      isSaving.value = false;
    }
  };
  
  const confirmDelete = () => {
    showDeleteDialog.value = true;
  };
  
  const deleteIpAddress = async () => {
    if (!props.id) return;
    
    isDeleting.value = true;
    
    try {
      await ipAddressStore.deleteIpAddress(parseInt(props.id));
      
      toast({
        title: 'IP address deleted',
        description: `${ipAddressStore.currentIpAddress?.ip_address} was deleted successfully`,
      });
      
      router.push('/ip-addresses');
    } catch (error) {
      toast({
        title: 'Error deleting IP address',
        description: 'Please try again later',
        variant: 'destructive',
      });
    } finally {
      isDeleting.value = false;
      showDeleteDialog.value = false;
    }
  };
  </script>