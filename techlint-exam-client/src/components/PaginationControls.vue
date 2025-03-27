<template>
    <div class="flex items-center justify-between">
      <div class="text-sm text-muted-foreground">
        Page {{ currentPage }} of {{ totalPages }}
      </div>
      <div class="flex items-center space-x-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="currentPage <= 1"
          @click="handlePageChange(currentPage - 1)"
        >
          <i-lucide-chevron-left class="h-4 w-4" />
          Previous
        </Button>
        <Button
          variant="outline"
          size="sm"
          :disabled="currentPage >= totalPages"
          @click="handlePageChange(currentPage + 1)"
        >
          Next
          <i-lucide-chevron-right class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </template>
  
  <script setup lang="ts">
  import { Button } from '@/components/ui/button';
  
  const props = defineProps<{
    currentPage: number;
    totalPages: number;
  }>();
  
  const emit = defineEmits<{
    (e: 'pageChange', page: number): void;
  }>();
  
  const handlePageChange = (page: number) => {
    if (page < 1 || page > props.totalPages) return;
    emit('pageChange', page);
  };
  </script>