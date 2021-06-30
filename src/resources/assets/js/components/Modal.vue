<template>
  <v-dialog
    v-model="$store.state.sheet"
    class="card card-outline-info"
    width="800"
  >
    <div v-if="$store.state.modalContent == 'addShop'">
      <form-component
      />
    </div>
    <div v-if="$store.state.modalContent == 'edit_shop'">
      <form-component
        :data="$store.state.dataShop"
      />
    </div>
    <div v-if="$store.state.modalContent == 'edit_marketPlace'">
      <form-component
        :data="$store.state.dataShop"
      />
    </div>
    <div v-if="$store.state.modalContent == 'add_marketPlace'">
      <form-component
        :data="$store.state.dataShop"
      />
    </div>
  </v-dialog>
</template>

<script>
import FormComponent from "../components/FormShop.vue";
export default {
  components:{
    FormComponent
  },
  mounted() {
    console.log('Modal: ', this.$store.state.sheet);
    console.log("ModalContent: ", this.$store.state.modalContent);
  },
  methods:{
    
  }
}
</script>

<style>

</style>