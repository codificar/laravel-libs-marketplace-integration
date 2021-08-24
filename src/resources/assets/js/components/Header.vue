<template>
  <v-app-bar
    app
    :color="$vuetify.theme.dark ? 'grey darken-3' : 'red lighten-1'"
    tag="header"
  >
    <v-row justify="space-between" align-content="space-between" align-content-md="space-between">
      <v-col cols="2" class="float-right col-2 col-md-2 d-flex mt-2 mb-6">
        <!-- <v-app-bar-nav-icon @click.stop="setDrawer"></v-app-bar-nav-icon> -->
        <!-- iFood INTEGRATION -->
         <v-list
          nav
          :color="$vuetify.theme.dark ? 'grey darken-3 mt-1' : 'red lighten-1 mt-1'"
        >
          <v-list-item link to="/" :class="$vuetify.theme.dark ? 'grey darken-3' : 'red lighten-1'">
            <v-list-item-content>
              <v-list-item-title >
                iFood              
              </v-list-item-title>
              <v-list-item-subtitle>
                INTEGRAÇÃO
              </v-list-item-subtitle>
            </v-list-item-content>
          </v-list-item>
         </v-list>
      </v-col>
      <v-col cols="4" class="d-inline-flex mt-8">
        <v-select
          :items="$store.state.shops"
          v-model="$store.state.selectedShop"
          item-value="id"
          item-text="name"
          label="Lojas"
          dense
          outlined
        ></v-select>
        <v-btn
          class="ma-1"
          fab
          dark
          x-small
          color="success"
          @click="addShop"
        >
          <v-icon dark>
            mdi-plus
          </v-icon>
        </v-btn>
      </v-col>
      <v-col cols="2" class="float-right col-2 col-md-2 d-flex mt-2 mb-6">
        <v-list
          nav
          :color="$vuetify.theme.dark ? 'grey darken-3 mt-1' : 'red lighten-1 mt-1'"
        >
          <v-list-item link to="/settings" :class="$vuetify.theme.dark ? 'grey darken-3' : 'red lighten-1'">
            <v-list-item-icon>
              <v-icon>mdi-cogs</v-icon>
            </v-list-item-icon>
            <v-list-item-content>
              <v-list-item-title>Configurações</v-list-item-title>
            </v-list-item-content>  
          </v-list-item>
        </v-list>
      </v-col>
    </v-row>
    
  </v-app-bar>
</template>

<script>
export default {
  data() {
    return {
      items: [
        { title: 'Dashboard', icon: 'mdi-view-dashboard', link: '/' },
        { title: 'Configurações', icon: 'mdi-cogs', link: '/settings' },
        { title: 'Credentials', icon: 'mdi-help-box', link: '/credentials' },
      ],
    }
  },
  methods:{
    setDrawer(){
      this.$store.commit('toggleDrawer', this.$store.state.drawer)
    },
    getShop(){
      this.$store.dispatch('getShops');
    },
    addShop(){
      this.$store.dispatch('showModal', {key: 'addShop', data: ''})
    }
  },
  mounted(){
    this.getShop();
    console.log("Store Header: ", this.$store.state.selectedShop);
  }
}
</script>

<style>

</style>