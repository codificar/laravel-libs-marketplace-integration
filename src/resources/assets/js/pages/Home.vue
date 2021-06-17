<template>
    <v-app>
        <v-col cols="4" class="d-inline-flex">
            <v-select
                :items="$store.state.shops"
                v-model="$store.state.selectedShop"
                item-value="id"
                item-text="name"
                label="Lojas"
                dense
                outlined
                ></v-select>
                <v-btn
                    class="ma-1"
                    fab
                    dark
                    x-small
                    color="success"
                    @click="addShop"
                >
                <v-icon dark>
                    mdi-plus
                </v-icon>
            </v-btn>    
        </v-col>
        <v-card
            elevation="2"
        >
            <div :class="$vuetify.theme.dark ? 'grey darken-3' : 'grey lighten-4'">
                <v-card-title
                    :class="$vuetify.theme.dark ? 'grey darken-3 title font-weight-regular justify-space-between' : 'grey lighten-4 title font-weight-regular justify-space-between' "
                >
                    <span> Pedidos </span>
                    <refresh-screen/>

                    <v-btn
                        v-if="$store.state.orders.length > 0"
                        class="ma-2"
                        :loading="loading"
                        :disabled="loading"
                        color="success"
                        @click="makeRequest()"
                        small
                    >
                        SOLICITAR ENTREGA
                    </v-btn>
                </v-card-title>
            </div>
            <v-card-text v-if="loading">
                <v-sheet
                    v-for="index in 10"
                    :key="index"
                >
                    <v-skeleton-loader
                        class="pa-md-1 mx-lg-auto"
                        max-width="999"
                        max-height="100"
                        v-bind="attrs"
                        type="list-item-avatar-three-line, card-heading, actions"
                    ></v-skeleton-loader>
                </v-sheet>
            </v-card-text>
            <v-card-text v-if="!loading && $store.state.orders.length == 0">
                <div class="card-body">                   
                    Não existe ordens para entrega!
                </div>
            </v-card-text>
            <v-card-text v-if="!loading && $store.state.orders.length > 0">
                <v-card 
                    class="pa-md-4 mx-lg-auto mb-2"
                    elevation="2"
                    v-for="order in $store.state.orders"
                    :key="order.orderId"
                >
                    <div class="card-body">                   
                        <div class="d-flex justify-space-between caption">
                            <div class="font-weight-black mr-3">
                                <div class="font-weight-medium">
                                    <v-avatar
                                        size="64"
                                        class="mr-5"
                                    >
                                        <v-img
                                            :src="require('../images/ifood.jpg')"
                                            alt="iFood"
                                        />
                                    </v-avatar>
                                </div>
                            </div>
                            <div class="font-weight-black">
                                <div class="font-weight-medium">
                                    Pedido: {{order.displayId}}
                                </div>
                                <div class="font-weight-medium">
                                    Status: {{order.fullCode}}
                                </div>
                            </div>
                            <div class="font-weight-black">
                                <div class="font-weight-medium">
                                    Status: {{order.fullCode}}
                                </div>
                                <div class="font-weight-medium">
                                    Distancia: {{parseFloat(order.distance).toFixed()}}MT
                                </div>
                            </div>
                            <div class="font-weight-black">
                                <div class="font-weight-medium">
                                    Valor: {{order.orderAmount ? formatCurrency(order.orderAmount) : '-'}} -
                                </div>
                            </div>
                            <div>
                                <v-row class="grey--text text-darken-1 ma-2 mt-6">
                                    <v-checkbox
                                        v-model="selected"
                                        label="Adicionar a entrega"
                                        class="ma-2 mt-1"
                                        :value="order"
                                        :id="order.orderId"
                                    ></v-checkbox>
                                    <v-btn
                                        color="primary"
                                        dark
                                        v-bind="attrs"
                                        @click="showDetails(order)"
                                        small
                                    >
                                        DETALHES
                                    </v-btn>
                                </v-row>
                            </div>
                        </div>
                    </div>
                </v-card>
            </v-card-text>
        </v-card>
        <modal-component v-if="$store.state.sheet"/>
    </v-app>
</template>

<script>
import ModalComponent from "../components/Modal.vue";
import RefreshScreen from "../components/RefreshScreen.vue";
    export default {
        components: {
            ModalComponent,
            RefreshScreen
        },
        data: () => ({
            loading: true,
            loader: null,
            attrs: {
                class: 'mb-2',
                boilerplate: true,
                elevation: 2,
            },
            sheet: false,
            selected: [],
            enabled: true,
            sliderValue: 0,
            ReloadScreen: {
                type: [Boolean, String],
                default: true
            },
        }),
        created(){
            this.getShop();
        },
        mounted() {
            console.log('Component mounted.')
            if (this.$store.state.shops) {
                // setTimeout(() => {
                    this.getOrders();
                // }, 10000);
            }  
        },
        methods: {
            formatNumber(number)
            {
                number = number.toFixed(2) + '';
                x = number.split('.');
                x1 = x[0];
                x2 = x.length > 1 ? '.' + x[1] : '';
                var rgx = /(\d+)(\d{3})/;
                while (rgx.test(x1)) {
                    x1 = x1.replace(rgx, '$1' + ',' + '$2');
                }
                return x1 + x2;
            },
            getShop(){
                this.$store.dispatch('getShops');
            },
            addShop(){
                this.$store.dispatch('showDetail', {key: 'addShop', data: ''})
            },
            setDrawer(){
                this.$store.commit('toggleDrawer', this.$store.state.drawer)
            },
            getOrders() {
                if (this.$store.state.orders) {
                    
                    this.loading = !this.loading;
                }
                if (this.$store.state.orders.length === 0) {
                    console.log("Vazio");
                }
            },
            formatCurrency(value){
                return (value).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL',
                });
            }, 
            makeRequest(){
                this.$store.dispatch('makeRequest', this.selected)
            },
            showDetails(order) {
                console.log("Sheet", this.$store.state.sheet);
                this.$store.dispatch('showDetail', { key: 'orderDetails', data: order})
            },
        },
        watch: {
            selected: {
                handler: function(newVal, oldVal){
                    console.log("OldVal: ", oldVal);
                    console.log("newVal: ", newVal);
                },
                deep: true
            },
            loader () {
                const l = this.loader
                this[l] = !this[l]

                setTimeout(() => (this[l] = false), 3000)

                this.loader = null
            },
        }
    }
</script>