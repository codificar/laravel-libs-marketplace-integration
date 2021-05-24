<template>
  <v-app>
    <!-- <header-component/> -->
    <v-main>
      <v-container fluid>
        <router-view></router-view>
        <v-speed-dial
            v-model="fab"
            bottom
            right
            fixed
        >
            <template v-slot:activator>
              <v-btn
                v-model="fab"
                color="success darken-2"
                dark
                fab
                link
                :to="fab ? '/settings' : '/'"
              >
                <v-icon>
                  mdi-cart-variant
                </v-icon>
                
              </v-btn>
            </template>
        </v-speed-dial>
      </v-container>
    </v-main>
    
    <!-- <sidebar-component/> -->
    
    <!-- <footer-component/> -->
    
  </v-app>
</template>

<script>
import FooterComponent from '../components/Footer.vue';
import HeaderComponent from '../components/Header.vue';
import SidebarComponent from '../components/Sidebar.vue';
import ModalComponent from '../components/Modal';
export default {
  components: {
    FooterComponent,
    HeaderComponent,
    SidebarComponent,
    ModalComponent
  },
  data() {
    return {
      drawer: true,
      fab: true
    }
  },
}
</script>

<style>

</style>