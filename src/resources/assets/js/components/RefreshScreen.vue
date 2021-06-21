<template>
<div class="text-center body-2">
    <h5>Atualizar Pedidos</h5>
    <h5>{{ sliderValue }}</h5>
    <v-switch
      v-model="enabled"
      :sync="true"
      :label="enabled ? 'Desabilitar' : 'Habilitar'"
      @change="onChangeEnable"
      hide-details
      class="pb-6"
    ></v-switch>
</div>
  
</template>

<script>
/**
 * @author Gustavo Silva  <gustavo.silva@codifica.com.br> 08/07/2020
 *
 * Count And Call Function Compontent
 *
 */
export default {
  name: "RefreshCount",
  props: {
    CountLimit: {
      type: Number,
      default: 30
    },
    CountStart: {
      type: Number,
      default: 0
    },
    ReloadScreen: {
      type: [Boolean, String],
      default: true
    },
    isEnable: {
      type: [Boolean, String],
      default: false
    }
  },
  data() {
    return {
      sliderValue: 0,
      enabled: this.isEnable
    };
  },
  methods: {
    onChangeEnable(data){
      // this.sliderValue = 30; 
      console.log("Data: ", data);
      this.$store.dispatch('saveRealodStatus', data)
      this.countTime();
    },
    async onFinish() {
      if(this.ReloadScreen){
        window.location.reload();
      }else {
        this.$emit("on-finish-count");
      }
    },
    countTime() {
      setInterval(() => {
        console.log("Enebled 2: ", this.enable);
        if (this.enabled) {
          if (this.sliderValue < this.CountLimit) {
            this.sliderValue = this.sliderValue + 1;
          } else {
            this.sliderValue = 0;
            this.onFinish();
          }
        }
      }, 1000);
    },
  },
  mounted() {
    // this.enabled = this.isEnable
    console.log("Enebled: ", this.enabled);
    this.countTime();
  },
};
</script>

<style></style>
