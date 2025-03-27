<template>
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Button variant="ghost" class="relative h-10 w-10 rounded-full">
          <Avatar>
            <AvatarFallback>{{ userInitials }}</AvatarFallback>
          </Avatar>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent class="w-56" align="end">
        <DropdownMenuLabel>My Account</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuItem disabled>
          <i-lucide-user class="mr-2 h-4 w-4" />
          <span>Profile</span>
        </DropdownMenuItem>
        <DropdownMenuItem disabled>
          <i-lucide-settings class="mr-2 h-4 w-4" />
          <span>Settings</span>
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem @click="handleLogout">
          <i-lucide-log-out class="mr-2 h-4 w-4" />
          <span>Logout</span>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  </template>
  
  <script setup lang="ts">
  import { computed } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '@/stores/auth';
  import { useToast } from '@/components/ui/toast/use-toast';
  import { 
    DropdownMenu,
    DropdownMenuTrigger,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator
  } from '@/components/ui/dropdown-menu';
  import { Avatar, AvatarFallback } from '@/components/ui/avatar';
  import { Button } from '@/components/ui/button';
  
  const router = useRouter();
  const authStore = useAuthStore();
  const { toast } = useToast();
  
  const user = computed(() => authStore.user);
  
  const userInitials = computed(() => {
    if (!user.value?.name) return '?';
    
    const nameParts = user.value.name.split(' ');
    if (nameParts.length >= 2) {
      return `${nameParts[0][0]}${nameParts[1][0]}`;
    }
    return nameParts[0][0];
  });
  
  const handleLogout = async () => {
    try {
      await authStore.logout();
      router.push('/login');
      toast({
        title: 'Logged out successfully',
        variant: 'default',
      });
    } catch (error) {
      console.error('Error logging out:', error);
      toast({
        title: 'Error logging out',
        description: 'Please try again',
        variant: 'destructive',
      });
    }
  };
  </script>