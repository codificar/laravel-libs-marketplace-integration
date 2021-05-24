<template>
  <v-app>
    <v-bottom-sheet
      v-model="$store.state.sheet"
      inset
    >
      <v-sheet
        class="text-center"
        height="500px"
      >
        <v-btn
          class="mt-6"
          text
          color="error"
          @click="closeDetails()"
        >
            close
        </v-btn>
        <div class="my-3" v-if="$store.state.modalContent == 'addShop'">
          <form-component />
        </div>
        <div class="my-3" v-if="$store.state.modalContent == 'orderDetails'">
          
        </div>
      </v-sheet>
    </v-bottom-sheet>
  </v-app>
</template>

<script>
import FormComponent from "../components/FormShop.vue";
export default {
  components:{
    FormComponent
  },
  mounted() {
    console.log('Modal: ', this.$store.state.sheet);
  },
  methods:{
    closeDetails() {
      this.$store.dispatch('showDetail', this.$store.state.sheet)
    }
  }
}
</script>

<style>

</style>