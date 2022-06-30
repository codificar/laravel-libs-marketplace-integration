<template>
    <div>
        <list-mode v-if="!$route.query.mapMode" />
        <map-mode v-else />
    </div>
</template>

<script>
import ListMode from './ListMode.vue'
import MapMode from './MapMode.vue'

export default {
  components: { ListMode, MapMode },
  data() {
    return {
        mapMode: false,
    }
  },
  mounted() {
    this.$store.dispatch("getOrders");
  }
}
</script>

<style>

</style>
