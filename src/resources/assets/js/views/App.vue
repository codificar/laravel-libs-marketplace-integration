<template>
  <v-app>
    <v-main>
      <v-container fluid>
        <router-view></router-view>
        <v-speed-dial
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
                @click="fab = !fab"
                :to="fab ? '/corp/marketplace/settings' : '/corp/marketplace/integration'"
              >
                <v-icon>
                  mdi-cart-variant
                </v-icon>
                
              </v-btn>
            </template>
        </v-speed-dial>
      </v-container>
    </v-main>   
  </v-app>
</template>

<script>
import ModalComponent from '../components/Modal';
export default {
  components: {
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