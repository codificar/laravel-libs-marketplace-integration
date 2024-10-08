/**
 * First, we will load all of this project's Javascript utilities and other
 * dependencies. Then, we will be ready to develop a robust and powerful
 * application frontend using useful Laravel and JavaScript libraries.
 */

require('lodash');

import './bootstrap';
import Vue from 'vue';
import vuetify from './plugins/vuetify';
import Vuex from 'vuex';
import store from './stores/index.js';
import Route from './router.js';
import App from './views/App';
import VueSweetalert2 from 'vue-sweetalert2';
import DatePicker from 'vue2-datepicker';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import ToggleButton from 'vue-js-toggle-button';
import laravelVuePaginationUmd from 'laravel-vue-pagination';

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap-vue/dist/bootstrap-vue.css';

import 'sweetalert2/dist/sweetalert2.min.css';
import Datepicker from 'vue2-datepicker';

import * as CodificarMaps from 'vue-maps';

Vue.use(CodificarMaps, {
    key: window.marketplaceSettings.googleMapsKey,
    provider: window.marketplaceSettings.mapsProvider,
});

Vue.use(VueSweetalert2);
Vue.use(Vuex);

// Make BootstrapVue available throughout your project
Vue.use(BootstrapVue);

// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin);

//Allows localization using trans()
Vue.prototype.trans = (key) => {
    return _.get(window.lang, key, key);
};
//Tells if an JSON parsed object is empty
Vue.prototype.isEmpty = (obj) => {
    return _.isEmpty(obj);
};

Vue.use(ToggleButton);

Vue.component('pagination', laravelVuePaginationUmd);

Vue.component('datepicker', require('vue2-datepicker'));
Vue.use(Datepicker);
import 'vue2-datepicker/index.css';

const opts = {};

const app = new Vue({
    el: '#marketplace-integration',
    vuetify,
    VueSweetalert2,
    DatePicker,
    store: store,
    router: Route,
    render: (h) => h(App),
});

export default app;
