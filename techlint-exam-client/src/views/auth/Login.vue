<template>
    <div class="min-h-screen flex items-center justify-center bg-background p-4">
      <Card class="w-full max-w-md">
        <CardHeader>
          <CardTitle class="text-2xl text-center">IP Management System</CardTitle>
          <CardDescription class="text-center">Login to your account</CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="handleSubmit">
            <div class="grid gap-6">
              <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="name@example.com"
                  :disabled="isSubmitting"
                  autocomplete="email"
                />
                <p v-if="v$.email.$error" class="text-sm text-destructive">
                  {{ v$.email.$errors[0].$message }}
                </p>
              </div>
              <div class="grid gap-2">
                <Label for="password">Password</Label>
                <Input
                  id="password"
                  v-model="form.password"
                  type="password"
                  placeholder="********"
                  :disabled="isSubmitting"
                  autocomplete="current-password"
                />
                <p v-if="v$.password.$error" class="text-sm text-destructive">
                  {{ v$.password.$errors[0].$message }}
                </p>
              </div>
              <Alert v-if="error" variant="destructive">
                <AlertTitle>Error</AlertTitle>
                <AlertDescription>{{ error }}</AlertDescription>
              </Alert>
              <Button type="submit" class="w-full" :disabled="isSubmitting">
                <i-lucide-loader-2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                {{ isSubmitting ? 'Logging in...' : 'Login' }}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </template>
  
  <script setup lang="ts">
  import { ref, reactive } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import { useVuelidate } from '@vuelidate/core';
  import { required, email, helpers } from '@vuelidate/validators';
  import { useAuthStore } from '@/stores/auth';
  import { Input } from '@/components/ui/input';
  import { Label } from '@/components/ui/label';
  import { Button } from '@/components/ui/button';
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
  import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
  
  const router = useRouter();
  const route = useRoute();
  const authStore = useAuthStore();
  
  const form = reactive({
    email: '',
    password: ''
  });
  
  const rules = {
    email: { 
      required: helpers.withMessage('Email is required', required),
      email: helpers.withMessage('Please enter a valid email address', email)
    },
    password: { 
      required: helpers.withMessage('Password is required', required)
    }
  };
  
  const v$ = useVuelidate(rules, form);
  const isSubmitting = ref(false);
  const error = ref('');
  
  const handleSubmit = async () => {
    error.value = '';
    const isValid = await v$.value.$validate();
    
    if (!isValid) {
      return;
    }
    
    isSubmitting.value = true;
    
    try {
      await authStore.login(form.email, form.password);
      
      const redirectPath = route.query.redirect as string || '/';
      router.push(redirectPath);
    } catch (err: any) {
      if (err.response?.status === 401) {
        error.value = 'Invalid email or password';
      } else {
        error.value = 'An error occurred. Please try again.';
      }
    } finally {
      isSubmitting.value = false;
    }
  };
  </script>