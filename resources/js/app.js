import './bootstrap';
import { createApp } from 'vue';
import OrderTracker from './Components/OrderTracker.vue';
import Login from './Components/Login.vue';
import Dashboard from './Components/Dashboard.vue';

const root = document.getElementById('app');
const page = root?.dataset.page;

const pages = {
    login: Login,
    dashboard: Dashboard,
};

createApp(pages[page] || OrderTracker).mount('#app');
