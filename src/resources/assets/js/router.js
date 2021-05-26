import Vue from 'vue';
import VueRouter from "vue-router";

import Home from "./pages/Home";
import About from "./pages/About";
import Settings from './pages/Settings';

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
            path: '/about',
            name: 'about',
            component: About
        },
        {
            path: '/corp/settings',
            name: 'settings',
            component: Settings
        }
    ]
});

export default router;