<template>
  <v-dialog
    v-model="$store.state.sheet"
    class="card card-outline-info"
    width="800"
  >
    <div v-if="$store.state.modalContent == 'shop'">
      <form-shop
        :data="$store.state.shop"
      />
    </div>
    <div v-if="$store.state.modalContent == 'marketConfig'">
      <form-market-config
        :data="$store.state.marketConfig"
      />
    </div>
    <div v-if="$store.state.modalContent == 'orderDetails'">
      <order-details/>
    </div>
  </v-dialog>
</template>

<script>
import FormShop from "../components/FormShop.vue";
import FormMarketConfig from "../components/FormMarketConfig.vue";
import OrderDetails from "../components/OrderDetails.vue";
export default {
  components: {
    FormShop,
    FormMarketConfig,
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