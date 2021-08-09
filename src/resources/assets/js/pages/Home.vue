<template>
    <div class="col-lg-12">
        <v-col cols="4" class="d-inline-flex">
            <v-select
                :items="$store.state.shops"
                v-model="$store.state.selectedShop"
                item-value="id"
                item-text="name"
                label="Lojas"
                dense
                outlined
                >
                    <template slot='selection' slot-scope='{ item }'>
                        {{ item.name }} - {{ item.get_config[0].status == 'AVAILABLE' ? 'ABERTA' : 'FECHADA' }}
                    </template>
                    <template slot='item' slot-scope='{ item }'>
                        {{ item.name }} - {{ item.get_config[0].status == 'AVAILABLE' ? 'ABERTA' : 'FECHADA' }}
                    </template>
                </v-select>
                <v-btn
                    class="ma-1"
                    small
                    depressed
                    color="success"
                    @click="addShop"
                    style="width:100px;"
                >
                    <v-icon 
                        color="white"
                        left
                    >mdi-plus
                    </v-icon>
                    <span class="font-weight-white white--text"> Loja</span>
                </v-btn>   
        </v-col>
        <div class="col-lg-12 w-100 h-50 card card-outline-info">
            <div class="card-header">
                <div class="row justify-space-between"> 
                    <div class="ma-5 align-center justify-start">
                        <h4 class="m-b-0 text-white"> Pedidos </h4>
                    </div>
                    <div class="ma-5 col-lg-4 col-md-4 align-center justify-center">
                        <refresh-screen
                            v-if="$store.state.shops.length > 0"
                            :isEnable="$store.state.status_reload"
                        />
                    </div>
                    <div class="ma-5 align-center justify-end">
                        <v-btn
                            class="justify-end"
                            v-if="selected.length > 0"
                            :loading="$store.state.requestStatus"
                            :disabled="$store.state.requestStatus"
                            color="success"
                            @click="makeRequest()"
                            small
                        >
                            SOLICITAR ENTREGA
                        </v-btn>
                    </div>
                </div>
            </div>
            <v-card-text v-if="$store.state.requestStatus">
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
                    :key="order.order_id"
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
                                <div class="font-weight-medium">
                                    {{$store.state.shops.filter(element => element.id == order.shop_id)[0].name}}
                                </div>
                            </div>
                            <div class="font-weight-black">
                                <div class="font-weight-medium">
                                    Pedido: {{order.display_id}}
                                </div>
                                <div class="font-weight-medium">
                                    Order ID: {{order.order_id}}
                                </div>
                            </div>
                            <div class="font-weight-black">
                                <div class="font-weight-medium">
                                    Status: {{order.full_code == 'READY_TO_PICKUP' ? 'PARA ENTREGA' : order.full_code}}
                                </div>
                                <div class="font-weight-medium">
                                    Distancia: {{parseFloat(order.distance).toFixed()}}MT
                                </div>
                            </div>
                            <div class="font-weight-black">
                                <div class="font-weight-medium">
                                    Valor: {{order.order_amount ? formatCurrency(order.order_amount) : '-'}}
                                </div>
                                <div class="font-weight-medium">
                                    Pagamento: {{order.method_payment != '' ? order.method_payment : '-'}}
                                </div>
                                <div class="font-weight-medium" v-if="order.method_payment === 'CASH' && !order.prepaid">
                                    Troco para: {{formatCurrency(order.change_for)}}
                                </div>
                                <div class="font-weight-medium" v-if="order.method_payment === 'CREDIT'">
                                    Troco para: {{order.brand}}
                                </div>
                                <div class="font-weight-medium" v-if="order.prepaid">
                                    Pago: SIM
                                </div>
                            </div>
                            <div class="font-weight-black">
                                <div class="font-weight-medium">
                                    <v-btn
                                        class="ma-1"
                                        small
                                        depressed
                                        color="primary"
                                        @click="showDetails(order)"
                                        style="width:150px;"
                                    >
                                        <v-icon 
                                            color="white"
                                            left
                                        >mdi-clipboard-text</v-icon>
                                            <span class="font-weight-white white--text"> Detalhes</span>
                                    </v-btn>
                                </div>
                            </div>
                            <div>
                                <div class="grey--text text-darken-1 ma-2 mt-6">
                                    <div
                                        class="font-weight-white"
                                        v-if="order.request_id == null && order.code == 'RTP'"
                                    >
                                        <v-checkbox
                                            v-model="selected"
                                            label="Adicionar a entrega"
                                            class="ma-2 mt-1"
                                            :value="order"
                                            :id="order.order_id"
                                        ></v-checkbox>
                                    </div>
                                    <div
                                        class="font-weight-white"
                                        v-if="order.code == 'CFM'"
                                    >
                                        <v-btn
                                            class="ma-1"
                                            small
                                            depressed
                                            color="success"
                                            @click="readyToPickup(order)"
                                            style="width:150px;"
                                        >
                                            <v-icon 
                                                color="white"
                                                left
                                            >mdi-motorbike</v-icon>
                                                <span class="font-weight-white white--text"> Liberar</span>
                                        </v-btn>
                                    </div>
                                    <div
                                        class="font-weight-white"
                                        v-if="order.code == 'PLC'"
                                    >
                                        <v-btn
                                            class="ma-1"
                                            small
                                            depressed
                                            color="success"
                                            @click="confirmOrder(order)"
                                            style="width:150px;"
                                        >
                                            <v-icon 
                                                color="white"
                                                left
                                            >mdi-check</v-icon>
                                                <span class="font-weight-white white--text"> Confirmar</span>
                                        </v-btn>
                                    </div>
                                    <div
                                        class="font-weight-white"
                                        v-if="order.code == 'PLC'"
                                    >
                                        <v-btn
                                            class="ma-1"
                                            small
                                            depressed
                                            color="error"
                                            @click="cancelOrder(order)"
                                            style="width:150px;"
                                        >
                                            <v-icon 
                                                color="white"
                                                left
                                            >mdi-cancel</v-icon>
                                                <span class="font-weight-white white--text"> Cancelar</span>
                                        </v-btn>
                                    </div>
                                    <v-btn
                                        class="ma-1"
                                        small
                                        depressed
                                        v-if="order.request_id"
                                        color="primary"
                                        style="width:150px;"
                                        v-bind="attrs"
                                        :href="'/corp/request/tracking/'+order.tracking_route"
                                    >
                                        <v-icon 
                                            color="white"
                                            left
                                        >mdi-map</v-icon>
                                        ACOMPANHAR
                                    </v-btn>
                                </div>
                            </div>
                        </div>
                    </div>
                </v-card>
            </v-card-text>
        </div>
        <modal-component v-if="$store.state.sheet"/>
    </div>
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
            console.log('Component mounted.');
            this.getOrders();            
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
                console.log("getShops");
                this.$store.dispatch('getShops');
            },
            addShop(){
                this.$store.dispatch('showModal', {key: 'addShop', data: ''})
            },
            getOrders() {
                if (this.$store.state.orders) {
                    this.loading = !this.loading;
                    
                }
                if (this.$store.state.orders.length == 0) {
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
                this.loading = true;
                this.$store.dispatch('makeRequest', this.selected)
            },
            showDetails(order) {
                console.log("Sheet", this.$store.state.sheet);
                this.$store.dispatch('showDetail', { key: 'orderDetails', data: order})
            },
            trackingRoute(order) {
                this.$router.push('/corp/request/tracking/'+order.tracking_route);
            },
            confirmOrder(item){
                console.log("Item: ", item);
                this.$store.dispatch('confirmOrder', item)
            },
            cancelOrder(item){
                this.$store.dispatch('cancelOrder', item)
            },
            readyToPickup(item){
                this.$store.dispatch('readyToPickup', item)
            }
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