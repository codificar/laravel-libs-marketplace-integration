/**
 * First, we will load all of this project's Javascript utilities and other
 * dependencies. Then, we will be ready to develop a robust and powerful
 * application frontend using useful Laravel and JavaScript libraries.
 */

import './bootstrap';
import Vue from 'vue';
import vuetify from './plugins/vuetify';
import Vuex from 'vuex'
import store from './stores/index.js'
import Route from './router.js';
import App from './views/App';
import VueSweetalert2 from 'vue-sweetalert2';

import 'sweetalert2/dist/sweetalert2.min.css';

Vue.use(VueSweetalert2);
Vue.use(Vuex)

const opts = {}

const app = new Vue({
    el: '#app',
    vuetify,
    VueSweetalert2,
    store: store,
    router: Route,
    render: h => h(App),
});

export default app