<template>
    <div class="col-lg-12 col-md-12 w-100 h-50 card card-outline-info">
        <v-card-title class="card-header title font-weight-regular justify-space-between">
            <h4 class="white--text"> Configurações </h4>
            <v-btn
                class="ma-1"
                small
                depressed
                color="success"
                @click="showModalShop()"
                style="width:100px;"
            >
                <v-icon 
                    color="white"
                    left
                >mdi-plus
                </v-icon>
                <span class="font-weight-white white--text"> Loja</span>
            </v-btn>
        </v-card-title>
        <v-card-text>
            <v-card 
                class="pa-md-4 mx-lg-auto mb-2"
                elevation="2"
                v-for="shop in $store.state.shops"
                :key="shop.id"
            >
                <div class="card-body">                   
                    <div class="d-flex justify-space-between caption">
                        <div class="font-weight-black">
                            <h3>
                                {{shop.name}}
                            </h3>
                            <div class="font-weight-white">
                                <v-btn
                                    class="ma-1"
                                    small
                                    depressed
                                    color="success"
                                    @click="showModalMarketConfig(shop.id)"
                                    style="width:150px;"
                                >
                                    <v-icon 
                                        color="white"
                                        left
                                    >mdi-plus
                                    </v-icon>
                                    <span class="font-weight-white white--text"> Marketplace</span>
                                </v-btn>
                            </div>
                            <div class="font-weight-white">
                                <v-btn
                                    class="ma-1"
                                    small
                                    depressed
                                    color="cyan"
                                    @click="showModalShop(shop)"
                                    style="width:150px;"
                                >
                                    <v-icon 
                                        color="white"
                                        left
                                    >mdi-pencil</v-icon>
                                        <span class="font-weight-white white--text"> Editar</span>
                                </v-btn>
                            </div>
                            <div class="font-weight-white">
                                <v-btn
                                    class="ma-1"
                                    small
                                    depressed
                                    color="red"
                                    @click="deleteShop(shop.id)"
                                    style="width:150px;"
                                >
                                    <v-icon 
                                        color="white"
                                        left
                                    >mdi-delete-outline</v-icon>
                                    <span class="font-weight-white white--text"> Apagar</span>
                                </v-btn>
                            </div>
                        </div>
                        <div class="font-weight-black col-10">
                            <v-expansion-panels>
                                <v-expansion-panel
                                    v-for="(item,i) in shop.get_config"
                                    :key="i"
                                >
                                    <v-expansion-panel-header>
                                        {{ `${item.name} - ${item.market}`.toUpperCase()}}
                                    </v-expansion-panel-header>
                                    <v-expansion-panel-content>
                                        <div class="font-weight-black">
                                            <div class="font-weight-medium">
                                                MERCHANT_ID: {{item.merchant_id}}
                                            </div>
                                        </div>
                                        <div class="font-weight-white">
                                            <div class="font-weight-white">
                                                <v-btn
                                                    class="ma-1"
                                                    small
                                                    depressed
                                                    color="cyan"
                                                    @click="showModalMarketConfig(shop.id, item)"
                                                    style="width:150px;"
                                                >
                                                    <v-icon 
                                                        color="white"
                                                        left
                                                    >mdi-pencil</v-icon>
                                                        <span class="font-weight-white white--text"> Editar</span>
                                                </v-btn>
                                            </div>
                                            <div class="font-weight-white">
                                                <v-btn
                                                    class="ma-1"
                                                    small
                                                    depressed
                                                    color="red"
                                                    @click="deleteMarketConfig(item.id)"
                                                    style="width:150px;"
                                                >
                                                    <v-icon 
                                                        color="white"
                                                        left
                                                    >mdi-delete-outline</v-icon>
                                                    <span class="font-weight-white white--text"> Apagar</span>
                                                </v-btn>
                                            </div>
                                        </div>
                                    </v-expansion-panel-content>
                                </v-expansion-panel>
                            </v-expansion-panels>
                        </div>
                    </div>
                </div>
            </v-card>
        </v-card-text>
        <modal-component v-if="$store.state.sheet"/>
    </div>
</template>

<script>
import FormShop from '../components/FormShop';
import ModalComponent from "../components/Modal.vue";

export default {
    components:{
        FormShop,
        ModalComponent
    },
    data: () => ({
        sheet: false,
        selectedItem: '',
    }),
    mounted() {
        console.log('Component mounted.')
        if (this.$store.state.shops == 0) {
            this.listStores();
        }
    },
    methods: {
        listStores(){
            this.$store.dispatch('getShops');
        },
        showModalShop(shop = null){
            this.$store.dispatch('showModal', {key: 'shop', shop: shop});
        },
        showModalMarketConfig(shopId = null, marketConfig = null){
            this.$store.dispatch('showModal', {key: 'marketConfig', shopId : shopId, marketConfig: marketConfig});
        },
        deleteShop(id){
            this.$store.dispatch('deleteShop', id);
        },
        deleteMarketConfig(id){
            this.$store.dispatch('deleteMarketConfig', id);
        }
    }
}
</script>
