import Vue from 'vue';
import VueRouter from 'vue-router';

import Home from './pages/Home';
import ListMode from './pages/ListMode';
import MapMode from './pages/MapMode';
import Credentials from './pages/Credentials';
import ZeDeliveryCredentials from './pages/ZeDeliveryCredentials';
import ZeDeliveryImport from './pages/ZeDeliveryImport';
import Settings from './pages/Settings';

Vue.use(VueRouter);

const router = new VueRouter({
    mode: 'history',
    routes: [
        {
            path: '/corp/marketplace/integration/list',
            name: 'list',
            component: ListMode,
        },
        {
            path: '/corp/marketplace/integration/map',
            name: 'map',
            component: MapMode,
        },
        {
            path: '/corp/marketplace/settings',
            name: 'settings',
            component: Settings,
        },
        {
            path: '/admin/marketplace-integration/credentials',
            name: 'credentials',
            component: Credentials,
        },
        {
            path: '/admin/marketplace-integration/credentials/zedelivery',
            name: 'credentials_zedelivery',
            component: ZeDeliveryCredentials,
        },
        {
            path: '/admin/marketplace-integration/zedelivery/import',
            name: 'zedeliveryimport',
            component: ZeDeliveryImport,
        },
    ],
});

export default router;
