<template>
  <div class="text-center justify-end body-2">
    <h5 class="m-b-0 text-white">Atualizar Pedidos</h5>
    <h5 class="m-b-0 text-white">{{ sliderValue }}</h5>
    <div>
      <div cols="4" xs="4" md="4">
      </div>
      <div cols="6" xs="6" md="6">
        <div class="custom-control custom-switch">
          <input type="checkbox" v-model="enabled" @change="onChangeEnable(Boolean(!isEnable))" class="custom-control-input" id="customSwitch1">
          <label class="custom-control-label" for="customSwitch1"><span class="justify-end text-white">{{enabled ? 'Desabilitar' : 'Habilitar'}}</span></label>
        </div>
      </div>
    </div>
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
      type: [Boolean, String, Number],
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
        if (window.location.pathname != '/corp/marketplace/integration') {
          this.enabled = false;
        }
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
    // this.countTime();
  },
};
</script>

<style></style>
