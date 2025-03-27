import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import type { RouteRecordRaw } from 'vue-router';

// Layouts
import MainLayout from '@/components/layout/MainLayout.vue';

// Auth
import Login from '@/views/auth/Login.vue';

// IP Management
import IpAddressList from '@/views/ip-address/IpAddressList.vue';
import IpAddressDetail from '@/views/ip-address/IpAddressDetail.vue';
import IpAddressCreate from '@/views/ip-address/IpAddressCreate.vue';

// Audit Logs
import AuditLogList from '@/views/audit-logs/AuditLogList.vue';

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: { name: 'ip-address-list' }
      },
      // IP Address routes
      {
        path: 'ip-addresses',
        name: 'ip-address-list',
        component: IpAddressList,
        meta: { title: 'IP Addresses' }
      },
      {
        path: 'ip-addresses/create',
        name: 'ip-address-create',
        component: IpAddressCreate,
        meta: { title: 'Create IP Address' }
      },
      {
        path: 'ip-addresses/:id',
        name: 'ip-address-detail',
        component: IpAddressDetail,
        props: true,
        meta: { title: 'IP Address Details' }
      },
      // Audit Log routes
      {
        path: 'audit-logs',
        name: 'audit-log-list',
        component: AuditLogList,
        meta: { title: 'Audit Logs', requiresAdmin: true }
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guard
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  // Check if route requires authentication
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin);
  
  // If we have a token but no user, try to load user data
  if (authStore.token && !authStore.user) {
    try {
      console.log('Fetching user profile...');
      await authStore.fetchUserProfile();
    } catch (error) {
      console.error('Failed to fetch profile:', error);
      // Don't redirect yet, let the auth check below handle it
    }
  }
  
  // Check authentication for protected routes
  if (requiresAuth && !authStore.isAuthenticated) {
    console.log('Authentication required, redirecting to login');
    next({ name: 'login', query: { redirect: to.fullPath } });
    return;
  }
  
  // Check admin permission for admin routes
  if (requiresAdmin && !authStore.isAdmin) {
    console.log('Admin permission required, redirecting to home');
    next({ name: 'ip-address-list' });
    return;
  }
  
  // If route is login and user is already authenticated, redirect to home
  if (to.name === 'login' && authStore.isAuthenticated) {
    console.log('Already authenticated, redirecting to home');
    next({ name: 'ip-address-list' });
    return;
  }
  
  // Otherwise, proceed
  next();
});

export default router;