<template>
    <div 
      class="bg-card border-r shadow-sm z-20 transition-all duration-300"
      :class="isOpen ? 'w-64' : 'w-0 md:w-16'"
    >
      <div class="flex flex-col h-full">
        <!-- Logo and Close Button -->
        <div class="p-4 flex items-center justify-between border-b">
          <div class="flex items-center gap-2">
            <img v-if="isOpen" src="@/assets/logo.svg" alt="Logo" class="h-8 w-8" />
            <span v-if="isOpen" class="font-bold">IP Management</span>
          </div>
          <Button variant="ghost" size="icon" @click="$emit('toggle-sidebar')" class="md:hidden">
            <i-lucide-x class="h-5 w-5" />
          </Button>
        </div>
        
        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto py-4">
          <ul class="space-y-1 px-2">
            <li v-for="link in navLinks" :key="link.path">
              <RouterLink 
                :to="link.path" 
                v-slot="{ isActive }"
                custom
              >
                <a 
                  href="#" 
                  @click.prevent="navigateTo(link.path)"
                  class="flex items-center p-2 rounded-md hover:bg-accent group transition-colors"
                  :class="[
                    isActive ? 'bg-accent text-accent-foreground' : 'text-muted-foreground',
                    !isOpen ? 'justify-center' : ''
                  ]"
                >
                  <component :is="link.icon" class="h-5 w-5" />
                  <span v-if="isOpen" class="ml-3">{{ link.name }}</span>
                </a>
              </RouterLink>
            </li>
          </ul>
        </nav>
        
        <!-- User Info at Bottom -->
        <div v-if="isOpen" class="p-4 border-t">
          <div class="flex items-center">
            <Avatar>
              <AvatarFallback>
                {{ userInitials }}
              </AvatarFallback>
            </Avatar>
            <div class="ml-3">
              <p class="text-sm font-medium">{{ user?.name }}</p>
              <p class="text-xs text-muted-foreground">{{ user?.email }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import { computed } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '@/stores/auth';
  import { Button } from '@/components/ui/button';
  import { Avatar, AvatarFallback } from '@/components/ui/avatar';
  
  const props = defineProps({
    isOpen: {
      type: Boolean,
      default: true
    }
  });
  
  const emit = defineEmits(['toggle-sidebar']);
  const router = useRouter();
  const authStore = useAuthStore();
  
  const user = computed(() => authStore.user);
  
  const userInitials = computed(() => {
    if (!user.value?.name) return '?';
    
    const nameParts = user.value.name.split(' ');
    if (nameParts.length >= 2) {
      return `${nameParts[0][0]}${nameParts[1][0]}`;
    }
    return nameParts[0][0];
  });
  
  const isAdmin = computed(() => authStore.isAdmin);
  
  const navLinks = computed(() => {
    const links = [
      { 
        name: 'IP Addresses', 
        path: '/ip-addresses', 
        icon: 'i-lucide-network', 
        requiresAdmin: false 
      },
    ];
    
    if (isAdmin.value) {
      links.push({ 
        name: 'Audit Logs', 
        path: '/audit-logs', 
        icon: 'i-lucide-history', 
        requiresAdmin: true 
      });
    }
    
    return links;
  });
  
  const navigateTo = (path: string) => {
    router.push(path);
    if (window.innerWidth < 768) {
      emit('toggle-sidebar');
    }
  };
  </script>