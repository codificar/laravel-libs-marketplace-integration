<template>
    <div class="col-lg-12">
        <div class="col-lg-4 d-inline-flex" v-if="$store.state.shops.length > 0">
            <b-form-select v-model="$store.state.selectedShop" class="mb-3">
                <b-form-select-option-group v-for="item in $store.state.shops" v-bind:key="item.id" :label="item.name">
                    <b-form-select-option v-for="market in item.get_config" v-bind:key="market.id" :value="market.id">{{market.name}} - {{market.status == 'AVAILABLE' ? 'ABERTA' : 'FECHADA' }}</b-form-select-option>
                </b-form-select-option-group>
            </b-form-select>
        </div>
        <div class="card col-lg-12 w-100 h-50 card card-outline-info">
            <div class="card-header justify-space-between">
                <div class="ma-5 col-lg-4 col-md-4 align-center justify-start">
                    <h4 class="m-b-0 text-white"> Pedidos</h4>
                </div>
                <div class="ma-5 col-lg-4 col-md-4 align-center justify-center">
                    <refresh-screen
                        v-if="$store.state.shops.length > 0"
                        :isEnable="$store.state.selectedShop.status_reload"
                    />
                </div>
                <div class="ma-5 col-lg-4 col-md-4 align-center ">
                    <div class="row col-md-12 justify-end"> 
                        <b-button
                            class="ma-lg-2 justify-end"
                            v-if="selected.length > 0"
                            :loading="$store.state.requestStatus"
                            :disabled="$store.state.requestStatus"
                            color="success"
                            @click="makeRequest('makeManualRequest')"
                            small
                        >
                            <i class="mdi mdi-google-maps"></i>
                                Montar Corrida Manualmente
                        </b-button>
                        <b-button
                            class=" ma-lg-2 justify-end"
                            v-if="selected.length > 0"
                            :loading="$store.state.requestStatus"
                            :disabled="$store.state.requestStatus"
                            color="success"
                            @click="makeRequest()"
                            small
                        >
                            <i class="mdi mdi-motorbike"></i>
                                Solicitar Prestador
                        </b-button>
                    </div>
                </div>
            </div>
            <div class="card-body" v-if="$store.state.requestStatus">
                <div
                    v-for="index in 10"
                    :key="index"
                >
                    <Preloader color="red" scale="0.6" />
                </div>
            </div>
            <div class="card-body" v-if="!loading && $store.state.orders.length == 0">
                <div class="card-body">                   
                    Não existe ordens para entrega!
                </div>
            </div>
            <div class="card-body" v-if="!loading && $store.state.orders.length > 0">
                <div 
                    class="pa-md-4 mx-lg-auto mb-2"
                    elevation="2"
                    v-for="order in $store.state.orders"
                    :key="order.order_id"
                >
                    <div class="card-body">                   
                        <div class="d-flex justify-space-between caption">
                            <div class="font-weight-black mr-3">
                                <div class="font-weight-medium">
                                    <b-avatar src="http://entregas:8003/images/ifood.jpg" size="6rem"></b-avatar>
                                </div>
                                <div class="font-weight-medium">
                                    {{$store.state.shops.filter(element => element.id == order.shop_id)[0]}}
                                </div>
                            </div>
                            <div class="font-weight-black">
                                <div class="font-weight-medium">
                                    Pedido: {{order.display_id}}
                                </div>
                                <div class="font-weight-medium">
                                    Cliente: {{order.client_name}}
                                </div>
                                <div class="font-weight-bold">
                                    Bairro: {{order.neighborhood}}
                                </div>
                                <div class="font-weight-medium">
                                    Endereço: {{order.formatted_address}}
                                </div>
                                <div class="font-weight-medium" v-if="order.complement != ''">
                                    Complemento: {{order.complement}}
                                </div>
                            </div>
                            <div class="font-weight-black">
                                <div class="font-weight-medium">
                                    Status: {{(order.code == 'RDA' || order.code == 'CFM') ? 'PARA ENTREGA' : order.full_code}}
                                </div>
                                <div class="font-weight-medium">
                                    Distancia: {{parseFloat(order.distance).toFixed()/1000}} KM
                                </div>
                            </div>
                            <div class="font-weight-black">
                                <div class="font-weight-medium">
                                    Valor: {{order.order_amount ? formatCurrency(order.order_amount) : '-'}}
                                </div>
                                <div class="font-weight-medium" v-if="!order.prepaid && order.method_payment === 'CASH'">
                                    Pagamento: DINHEIRO
                                </div>
                                <div class="font-weight-medium" v-if="!order.prepaid && order.method_payment === 'CASH'">
                                    Troco para: {{formatCurrency(order.change_for)}}
                                </div>
                                <div class="font-weight-medium" v-if="!order.prepaid && order.method_payment === 'CREDIT'">
                                    Pagamento: MÁQUINA
                                </div>
                                <div class="font-weight-medium" v-if="!order.prepaid && order.method_payment == 'CREDIT'">
                                    Bandeira: {{order.card_brand}}
                                </div>
                                <div class="font-weight-medium" v-if="order.prepaid">
                                    Pagamento: ONLINE
                                </div>
                            </div>
                            <div class="font-weight-black">
                                <div class="font-weight-medium">
                                    <b-button
                                        class="ma-1"
                                        small
                                        depressed
                                        color="primary"
                                        @click="showDetails(order)"
                                        style="width:150px;"
                                    >
                                        <i class="mdi mdi-clipboard-text"></i>
                                        <span class="font-weight-white white--text"> Detalhes</span>
                                    </b-button>
                                </div>
                            </div>
                            <div>
                                <div class="grey--text text-darken-1 ma-2 mt-6">
                                    <div
                                        class="font-weight-white"
                                    >
                                        <input
                                            type="checkbox"
                                            v-model="selected"
                                            v-if="order.request_id == null && order.code == 'CFM' || order.code == 'RDA'"
                                            label="Adicionar a entrega"
                                            class="ma-2 mt-1"
                                            :value="order"
                                            :id="order.order_id"
                                        />
                                    </div>
                                    <b-button
                                        class="ma-1"
                                        small
                                        depressed
                                        v-if="order.request_id"
                                        color="primary"
                                        style="width:150px;"
                                        v-bind="attrs"
                                        :href="'/corp/request/tracking/'+order.tracking_route"
                                    >
                                        <i class="mdi mdi-map"></i>
                                        
                                        ACOMPANHAR
                                    </b-button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <modal-component v-if="$store.state.sheet"/> -->
    </div>
</template>

<script>
// import ModalComponent from "../components/Modal.vue";
import RefreshScreen from "../components/RefreshScreen.vue";
    export default {
        components: {
            // ModalComponent,
            RefreshScreen
        },
        data: () => ({
            searchQuery: null,
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
            objectData: {},
            data: {
                pagination: {
                    actual : 1,
                    itensPerPage : 10
                },
                filters: {
                    institution: '',
                    ItensPerPage: 10
                },
                order: {
                    field: '',
                    direction: ''
                },
                range: [
                    null,
                    null
                ]
            }
        }),
        created(){
            this.getShop();
        },
        mounted() {
            console.log('Component mounted.');
            this.getOrders();
            this.resultQuery();
        },
        methods: {
            returnString(){
                return JSON.stringify(this.$store.state.orders);
            },
            disabledBeforeToday(date){
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                return date < today;
            },
            fetch(page = 1) {
                console.log("Log in fetch");
                var component = this;
                // fazemos isso porque as datas são pegas como objetos
                // então transformamos elas em string pra enviar ao backend
                this.$store.commit('CLEAR_ORDERS');
                this.data.pagination.page = page;
                axios.post('/corp/api/orders/'+this.$store.state.selectedShop.id+'?page='+page, this.data)
                .then(
                    response => {
                        console.log('sucesso');
                        console.log(response.data);
                        component.$store.commit('CREATE_ORDER', response.data);
                    },
                    response => {
                        //console.log(response.data);
                    // error callback
                    }
                );
                this.$nextTick();
            },
            resultQuery(){
                if(this.searchQuery){
                    console.log("filter");
                    return this.$store.state.orders.data.filter((item)=>{
                        return this.searchQuery.toLowerCase().split(' ').every(v => item.display_id.toLowerCase().includes(v))
                        || this.searchQuery.toLowerCase().split(' ').every(v => item.neighborhood.toLowerCase().includes(v)) 
                        || this.searchQuery.toLowerCase().split(' ').every(v => item.client_name.toLowerCase().includes(v))
                    });
                }else{
                    console.log("filter else");
                    return this.$store.state.orders.data;
                }
            },
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
            makeRequest(type = 'makeRequest'){
                // this.loading = true;
                this.$store.dispatch(type, this.selected)
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
            "data.range": {
                handler: function(newVal, oldVal){
                    console.log("OldVal: ", oldVal);
                    console.log("newVal: ", newVal);
                    if (newVal == undefined) {
                        this.data.range = oldVal;
                    } else {
                        this.data.range = newVal;
                    }
                    this.fetch();
                },
                deep: true
            }
        }
    }
</script>