<template>
  <div class="text-center justify-end body-2">
    <h5 class="m-b-0 text-white">Atualizar Pedidos</h5>
    <h5 class="m-b-0 text-white">{{ sliderValue }}</h5>
    <v-row>
      <v-col cols="4" xs="4" md="4">
      </v-col>
      <v-col cols="6" xs="6" md="6">
        <v-switch
          v-model="enabled"
          :sync="true"
          @change="onChangeEnable"
          class="justify-end text-white"
        >
          <template v-slot:label>
            <span class="justify-end text-white">{{enabled ? 'Desabilitar' : 'Habilitar'}}</span>
          </template>
        </v-switch>
      </v-col>
    </v-row>
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
    this.countTime();
  },
};
</script>

<style></style>
