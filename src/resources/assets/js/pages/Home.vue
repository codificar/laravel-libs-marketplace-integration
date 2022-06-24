<template>
    <div class="col-lg-12 card card-outline-info">
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
                <div class="ma-5 align-center ">
                    <div class="row col-md-12 justify-end"> 
                        <v-btn
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
                        </v-btn>
                        <v-btn
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
                        </v-btn>
                    </div>
                </div>
            </div>
        </div>
        <v-row class="col-sm-12 col-md-12 col-lg-12">
            <v-col cols="4" class="d-inline-flex float-left" v-if="$store.state.shops.length > 0">
                <div class="search-wrapper panel-heading col-sm-12">
                <select class="custom-select custom-select-lg col-sm-12 pa-2" name="shops" id="shops" v-model="data.marketId">
                    <optgroup v-for="item in $store.state.shops" v-bind:key="item.id" :label="item.name">
                        <option v-for="market in item.get_config" v-bind:key="market.id" :value="market.id">{{market.name}} - {{market.status == 'AVAILABLE' ? 'ABERTA' : 'FECHADA' }}</option>
                    </optgroup>
                </select>
                </div>
            </v-col>
            <v-col cols="4" class="d-inline-flex" >
                <DatePicker
                    v-model="data.range"
                    lang="pt-br"
                    format="YYYY-MM-DD"
                    formatted="YYYY-MM-DD"
                    placeholder="Por período"
                    range
                    class="ma-auto"
                />
            </v-col>
            <v-col cols="4" class="d-inline-flex float-right" v-if="$store.state.orders">
                <div class="search-wrapper panel-heading col-sm-12">
                    <input class="form-control" type="text" v-model="data.keyword" placeholder="Buscar por Pedido, Nome do Cliente ou Bairro" />
                </div>
            </v-col>
        </v-row>
        
        <div class="col-lg-12 w-100 h-50">
            <v-card-text v-if="!loading && !$store.state.orders">
                <div class="card-body">                   
                    Não existe ordens para entrega!
                </div>
            </v-card-text>
            <div v-if="!loading && $store.state.orders" class="card-body">
                <table class="table">
                    <th>Loja</th>
                    <th>Pedido</th>
                    <th>Status</th>
                    <th>Pagamento</th>
                    <th>Detalhes</th>
                    <th>Seleção</th>
                    <tbody>
                        <tr v-for="order in resultQuery()"
                            :key="order.order_id"
                        >
                            <!-- <div class="d-flex justify-space-between caption"> -->
                                <td class="font-weight-black mr-3">
                                    <div class="font-weight-medium">
                                        <v-avatar
                                            size="64"
                                            class="mr-5"
                                        >
                                            <v-img
                                                :src="require('../../img/ifood.jpg')"
                                                alt="iFood"
                                            />
                                        </v-avatar>
                                    </div>
                                    <div class="font-weight-medium ml-5">
                                        {{$store.state.shops.filter(element => element.id == order.shop_id)[0].name}}
                                    </div>
                                </td>
                                <td class="font-weight-black">
                                    <div class="font-weight-medium">
                                        Pedido: {{order.display_id}}
                                    </div>
                                    <div class="font-weight-medium">
                                        Cliente: {{order.client_name}}
                                    </div>
                                    <div class="font-weight-medium">
                                        Bairro: {{order.neighborhood}}
                                    </div>
                                    <div class="font-weight-medium">
                                        Endereço: {{order.formatted_address}}
                                    </div>
                                    <div class="font-weight-medium" v-if="order.complement != ''">
                                        Complemento: {{order.complement}}
                                    </div>
                                </td>
                                <td class="font-weight-black">
                                    <div class="font-weight-medium">
                                        Status: {{(order.code == 'RDA' || order.code == 'CFM') ? 'PARA ENTREGA' : order.full_code}}
                                    </div>
                                    <div class="font-weight-medium">
                                        Distancia: {{parseFloat(order.distance).toFixed()/1000}} KM
                                    </div>
                                </td>
                                <td class="font-weight-black">
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
                                </td>
                                <td class="font-weight-black">
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
                                </td>
                                <td>
                                    <div class="grey--text text-darken-1 ma-2 mt-6">
                                        <div
                                            class="font-weight-white"
                                        >
                                            <v-checkbox
                                                v-model="selected"
                                                v-if="order.request_id == null && (order.code == 'CFM' || order.code == 'RDA')"
                                                label="Adicionar a entrega"
                                                class="ma-2 mt-1"
                                                :value="order"
                                                :id="order.order_id"
                                            ></v-checkbox>
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
                                </td>
                            <!-- </div> -->
                        </tr>
                    </tbody>

                </table>
            </div>
        </div>
        <pagination
            :data="$store.state.orders"
            @pagination-change-page="fetch"
            align="right"
        >
        </pagination>
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
                ],
                keyword: '',
                marketId : null 
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
                axios.post('/corp/api/orders/?page='+page, this.data)
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
                console.log("resultquery > this.data.keyword:", this.data.keyword);
                if(this.data.keyword){
                    console.log("filter");
                    return this.$store.state.orders.data.filter((item)=>{
                        return this.data.keyword.toLowerCase().split(' ').every(v => item.display_id.toLowerCase().includes(v))
                        || this.data.keyword.toLowerCase().split(' ').every(v => item.neighborhood.toLowerCase().includes(v)) 
                        || this.data.keyword.toLowerCase().split(' ').every(v => item.client_name.toLowerCase().includes(v))
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
                this.$store.dispatch('getOrders');
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
                console.log("order", order);
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
            },
            "data.marketId": {
                handler: function(newVal, oldVal){
                    this.fetch();
                }
            }
        }
    }
</script>