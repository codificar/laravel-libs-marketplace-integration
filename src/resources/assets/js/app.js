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
import DatePicker from 'vue2-datepicker';

import 'sweetalert2/dist/sweetalert2.min.css';
import Datepicker from 'vue2-datepicker';

Vue.use(VueSweetalert2);
Vue.use(Vuex)
Vue.component("pagination", require("laravel-vue-pagination"));
Vue.component("datepicker", require('vue2-datepicker'));
Vue.use(Datepicker);
import 'vue2-datepicker/index.css';

const opts = {}

const app = new Vue({
    el: '#marketplace-integration',
    vuetify,
    VueSweetalert2,
    DatePicker,
    store: store,
    router: Route,
    render: h => h(App),
});

export default app