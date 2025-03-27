<template>
    <div class="min-h-screen bg-background">
      <div class="flex min-h-screen">
        <!-- Sidebar -->
        <Sidebar :is-open="isSidebarOpen" @toggle-sidebar="toggleSidebar" />
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
          <!-- Header -->
          <header class="bg-card p-4 border-b flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center">
              <Button variant="ghost" size="icon" @click="toggleSidebar" class="md:hidden">
                <i-lucide-menu class="h-5 w-5" />
              </Button>
              <h1 class="text-xl font-bold ml-2">{{ currentPageTitle }}</h1>
            </div>
            
            <div class="flex items-center gap-4">
              <UserMenu />
            </div>
          </header>
          
          <!-- Content -->
          <main class="flex-1 p-6 overflow-auto">
            <RouterView />
          </main>
        </div>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import { ref, computed } from 'vue';
  import { useRoute } from 'vue-router';
  import Sidebar from './Sidebar.vue';
  import UserMenu from './UserMenu.vue';
  import { Button } from '@/components/ui/button';
  
  const route = useRoute();
  const isSidebarOpen = ref(window.innerWidth >= 768); // Open by default on desktop
  
  const currentPageTitle = computed(() => {
    return route.meta.title || 'IP Management System';
  });
  
  const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
  };
  </script>