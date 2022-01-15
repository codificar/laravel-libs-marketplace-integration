require('lodash');

import './bootstrap';
import Vue from 'vue';
import vuetify from './plugins/vuetify';
import Vuex from 'vuex'
import store from './stores/index.js'
import Route from './router.js';
import App from './views/App';
import VueSweetalert2 from 'vue-sweetalert2';
import DatePicker from 'vue2-datepicker';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
import ToggleButton from 'vue-js-toggle-button';

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap-vue/dist/bootstrap-vue.css'

// Make BootstrapVue available throughout your project
Vue.use(BootstrapVue)
// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin)

Vue.use(VueSweetalert2);
Vue.use(Vuex)

// Make BootstrapVue available throughout your project
Vue.use(BootstrapVue)

// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin)

//Allows localization using trans()
Vue.prototype.trans = (key) => {
    return _.get(window.lang, key, key);
};
//Tells if an JSON parsed object is empty
Vue.prototype.isEmpty = (obj) => {
    return _.isEmpty(obj);
};

Vue.use(ToggleButton);

Vue.component("pagination", require("laravel-vue-pagination"));

Vue.component("datepicker", require('vue2-datepicker'));
Vue.use(Datepicker);
import 'vue2-datepicker/index.css';

Vue.config.productionTip = false

Vue.use(VueSweetalert2)

new Vue({
  el: '#marketplace-integration',
  VueSweetalert2,
  router,
  store,
  components: { App },
  template: '<App/>'
})
