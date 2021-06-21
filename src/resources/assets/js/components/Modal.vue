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
          />
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
    console.log("ModalContent: ", this.$store.state.modalContent);
  },
  methods:{
    
  }
}
</script>

<style>

</style>