// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import './bootstrap'
import Vue from 'vue'
import App from './views/App.vue'
import router from './router.js'
import store from './store/index.js'
import VueSweetalert2 from 'vue-sweetalert2'
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'

// Make BootstrapVue available throughout your project
Vue.use(BootstrapVue)
// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin)

import 'sweetalert2/dist/sweetalert2.min.css'

Vue.config.productionTip = false

Vue.use(VueSweetalert2)

new Vue({
  el: '#ifood',
  VueSweetalert2,
  router,
  store,
  components: { App },
  template: '<App/>'
})
