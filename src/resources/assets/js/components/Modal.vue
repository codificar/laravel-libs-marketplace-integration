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
    <div v-if="$store.state.modalContent == 'orderDetails'">
      <order-details/>
    </div>
  </v-dialog>
</template>

<script>
import FormComponent from "../components/FormShop.vue";
import OrderDetails from "../components/OrderDetails.vue";
export default {
  components: {
    FormComponent,
    OrderDetails
  },
  mounted() {
    console.log("Modal: ", this.$store.state.sheet);
    console.log("ModalContent: ", this.$store.state.modalContent);
  },
  methods: {
    closeModal() {
      this.$store.dispatch("showDetail", this.$store.state.sheet);
    },
    formatCurrency(value) {
      return value.toLocaleString("pt-BR", {
        style: "currency",
        currency: "BRL",
      });
    },
  },
};
</script>

<style>
</style>