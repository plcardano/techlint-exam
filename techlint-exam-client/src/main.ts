import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import { createHead } from '@vueuse/head';
import './index.css';
import { LucideEye, LucidePencil, LucideTrash } from 'lucide-vue-next';

const app = createApp(App);
const pinia = createPinia();
const head = createHead();

app.use(pinia);
app.use(router);
app.use(head);

app.component('i-lucide-eye', LucideEye);
app.component('i-lucide-pencil', LucidePencil);
app.component('i-lucide-trash', LucideTrash);

app.mount('#app');