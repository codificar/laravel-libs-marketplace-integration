<template>
  <v-navigation-drawer v-model="$store.state.drawer" :color="$vuetify.theme.dark ? 'grey darken-3' : 'grey lighten-4'" app>
    <v-list-item :class="$vuetify.theme.dark ? 'grey darken-3' : 'red lighten-1'">
      <v-list-item-content>
        <v-list-item-title class="title">
          iFood
          
        </v-list-item-title>
        <v-list-item-subtitle>
          INTEGRAÇÃO
        </v-list-item-subtitle>
      </v-list-item-content>
    </v-list-item>
    <v-divider></v-divider>
    <v-list
      dense
      nav
    >
      <v-list-item
        v-for="item in items"
        :key="item.title"
        link
        :to="item.link"
      >
        <v-list-item-icon>
          <v-icon>{{ item.icon }}</v-icon>
        </v-list-item-icon>

        <v-list-item-content>
          <v-list-item-title>{{ item.title }}</v-list-item-title>
        </v-list-item-content>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
export default {
  data() {
    return {
      items: [
        { title: 'Dashboard', icon: 'mdi-view-dashboard', link: '/' },
        { title: 'Configurações', icon: 'mdi-cogs', link: '/settings' },
        { title: 'About', icon: 'mdi-help-box', link: '/about' },
      ],
    }
  },
  mounted(){
    console.log("Store: ", this.$store.state);
  }
}
</script>

<style>

</style>