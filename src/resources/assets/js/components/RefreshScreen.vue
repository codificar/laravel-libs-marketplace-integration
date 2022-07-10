<template>
    <div class="text-center justify-end body-2">
        <v-row>
            <v-col cols="2" xs="2" md="2" class="align-start d-flex">
                <v-switch
                    v-model="enabled"
                    :sync="true"
                    @change="onChangeEnable"
                    class="ma-0 pa-0"
                >
                </v-switch>
            </v-col>
            <v-col class="align-start d-flex ">
                <h5 class="m-b-0">Atualizar Pedidos ({{ sliderValue }})</h5>
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
    name: 'RefreshCount',
    props: {
        CountLimit: {
            type: Number,
            default: 30,
        },
        CountStart: {
            type: Number,
            default: 0,
        },
        ReloadScreen: {
            type: [Boolean, String],
            default: false,
        },
        isEnable: {
            type: [Boolean, String],
            default: false,
        },
    },
    data() {
        return {
            sliderValue: 0,
            enabled: this.isEnable,
        };
    },
    methods: {
        onChangeEnable() {
            clearInterval(this.timer);
            this.timer = setInterval(this.countTime, 1000);
        },
        async onFinish() {
            if (this.ReloadScreen) {
                window.location.reload();
            } else {
                this.$emit('on-finish-count');
            }
        },
        countTime() {
            if (this.enabled) {
                if (this.sliderValue < this.CountLimit) {
                    this.sliderValue = this.sliderValue + 1;
                } else {
                    this.sliderValue = 0;
                    this.onFinish();
                }
            }
        },
    },
    mounted() {
        // this.enabled = this.isEnable
        console.log('Enebled: ', this.enabled);
        this.countTime();
    },
};
</script>

<style></style>
