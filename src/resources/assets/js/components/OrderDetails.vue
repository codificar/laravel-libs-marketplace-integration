<template>
    <div
        class="card card-outline-info"
    >
        <div class="modal-header">
          <span >Pedido: {{$store.state.dataOrder.data.display_id}}</span>
          <button type="button" @click="closeModal()"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="panel-body">
          <div class="modal-body">
            <v-row>
                <v-col class="d-flex justify-space-between caption">
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
                            {{$store.state.shops.filter(element => element.id == $store.state.dataOrder.data.shop_id)[0].name}}
                        </div>
                    </div>
                </v-col>
                <v-col class="d-flex justify-space-between caption">
                    <div class="font-weight-black">
                        <div class="font-weight-medium">
                            Pedido: {{$store.state.dataOrder.data.display_id}}
                        </div>
                        <div class="font-weight-medium">
                            Endereço: {{$store.state.dataOrder.data.formatted_address}}
                        </div>
                    </div>
                </v-col>
                <v-col class="d-flex justify-space-between caption">
                    <div class="font-weight-black">
                        <div class="font-weight-medium">
                            Status: {{$store.state.dataOrder.data.full_code == 'DISPATCHED' ? 'PARA ENTREGA' : $store.state.dataOrder.data.request_id ? 'EM ENTREGA' : '-'}}
                        </div>
                        <div class="font-weight-medium">
                            Distância: {{parseFloat($store.state.dataOrder.data.distance).toFixed()/1000}} KM
                        </div>
                    </div>
                </v-col>
                <v-col class="d-flex justify-space-between caption">
                    <div class="font-weight-black">
                        <div class="font-weight-medium">
                            Valor: {{$store.state.dataOrder.data.order_amount ? formatCurrency($store.state.dataOrder.data.order_amount) : '-'}}
                        </div>
                        <div class="font-weight-medium" v-if="!$store.state.dataOrder.data.prepaid && $store.state.dataOrder.data.method_payment === 'CASH'">
                            Pagamento: DINHEIRO
                        </div>
                        <div class="font-weight-medium" v-if="!$store.state.dataOrder.data.prepaid && $store.state.dataOrder.data.method_payment === 'CASH'">
                            Troco para: {{formatCurrency($store.state.dataOrder.data.change_for)}}
                        </div>
                        <div class="font-weight-medium" v-if="!$store.state.dataOrder.data.prepaid && $store.state.dataOrder.data.method_payment === 'CREDIT'">
                            Pagamento: MÁQUINA
                        </div>
                        <div class="font-weight-medium" v-if="!$store.state.dataOrder.data.prepaid && $store.state.dataOrder.data.method_payment == 'CREDIT'">
                            Bandeira: {{$store.state.dataOrder.data.card_brand}}
                        </div>
                        <div class="font-weight-medium" v-if="$store.state.dataOrder.data.prepaid">
                            Pagamento: ONLINE
                        </div>
                    </div>
                </v-col>
            </v-row>
            <!-- <v-row>
                <v-col class="d-flex justify-space-between caption">
                    <div class="font-weight-black">
                        <div class="font-weight-medium">
                            Valor: {{$store.state.dataOrder.data.order_amount ? formatCurrency($store.state.dataOrder.data.order_amount) : '-'}}
                        </div>
                        <div class="font-weight-medium">
                            Pagamento: {{$store.state.dataOrder.data.method_payment != '' ? $store.state.dataOrder.data.method_payment : '-'}}
                        </div>
                        <div class="font-weight-medium" v-if="$store.state.dataOrder.data.method_payment == 'CASH'">
                            Troco para: {{formatCurrency($store.state.dataOrder.data.change_for)}}
                        </div>
                    </div>
                </v-col>
            </v-row> -->
            
          </div>
        </div>
    </div>
</template>

<script>
export default {
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
    }
}
</script>

<style>

</style>