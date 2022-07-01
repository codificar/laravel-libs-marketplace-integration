import Vue from 'vue';
import VueRouter from "vue-router";

import Home from "./pages/Home";
import ListMode from "./pages/ListMode";
import MapMode from "./pages/MapMode";
import Credentials from "./pages/Credentials";
import Settings from './pages/Settings';

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/corp/marketplace/integration/list',
            name: 'list',
            component: ListMode
        },
        {
            path: '/corp/marketplace/integration/map',
            name: 'map',
            component: MapMode
        },
        {
            path: '/admin/marketplace-integration/credentials',
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