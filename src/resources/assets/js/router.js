import Vue from 'vue';
import VueRouter from 'vue-router';

import Home from './pages/Home.vue';
import Credentials from './pages/Credentials.vue';
import Settings from './pages/Settings.vue';

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/corp/marketplace/integration',
            name: 'home',
            component: Home
        },
        {
            path: '/admin/settings/credentials',
            name: 'credentials',
            component: Credentials
        },
        {
            path: '/corp/marketplace/settings',
            name: 'settings',
            component: Settings
        }
    ]
});

export default router;