<template>
    <v-card
        elevation="2"
    >
        <div :class="$vuetify.theme.dark ? 'grey darken-3' : 'grey lighten-4'">
            <v-card-title
                class="title font-weight-regular justify-space-between"
            >
                <span> Configurações </span>
                <v-avatar
                    color="primary lighten-2"
                    class="subheading white--text"
                    size="24"
                ></v-avatar>
            </v-card-title>
        </div>
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
                                <div class="font-weight-medium">
                                    Nome: {{shop.name}}
                                </div>
                            </div>
                            <div class="font-weight-black col-8">
                                <v-expansion-panels>
                                    <v-expansion-panel
                                        v-for="(item,i) in shop.get_config"
                                        :key="i"
                                    >
                                    <v-expansion-panel-header>
                                        {{item.market.toUpperCase()}}
                                    </v-expansion-panel-header>
                                    <v-expansion-panel-content>
                                        <div class="font-weight-black">
                                            <div class="font-weight-medium">
                                                MERCHANT_ID: {{item.merchant_id}}
                                            </div>
                                        </div>
                                        <div class="font-weight-black">
                                            <div class="font-weight-medium">
                                                CLIENT_ID: {{item.client_id}}
                                            </div>
                                        </div>
                                        <div class="font-weight-black">
                                            <div class="font-weight-medium">
                                                CLIENT_SECRET: {{item.client_secret}}
                                            </div>
                                        </div>
                                        <div class="font-weight-black">
                                            <v-btn-toggle >
                                                <v-btn
                                                    fab
                                                    x-small
                                                    color="success"
                                                    @click="addShop('add_marketPlace', shop)"
                                                >
                                                    <v-icon dark>
                                                        mdi-plus
                                                    </v-icon>
                                                </v-btn> 
                                                <v-btn
                                                    fab
                                                    x-small
                                                    color="cyan"
                                                    @click="addShop('edit_marketPlace', item,shop.merchant_id)"
                                                >
                                                    <v-icon>mdi-pencil</v-icon>
                                                </v-btn>

                                                <v-btn
                                                    fab
                                                    x-small
                                                    color="red"
                                                    @click="deleteShop('delete_marketPlace', item.id)"
                                                >
                                                    <v-icon>mdi-delete-outline</v-icon>
                                                </v-btn>
                                            </v-btn-toggle>
                                        </div>
                                    </v-expansion-panel-content>
                                    </v-expansion-panel>
                                </v-expansion-panels>
                            </div>
                            <div class="font-weight-black">
                                <v-btn-toggle >
                                    <v-btn
                                        fab
                                        x-small
                                        color="cyan"
                                        @click="addShop('edit_shop', shop)"
                                    >
                                        <v-icon>mdi-pencil</v-icon>
                                    </v-btn>

                                    <v-btn
                                        fab
                                        x-small
                                        color="red"
                                        @click="deleteShop('delete_marketPlace', shop.id)"
                                    >
                                        <v-icon>mdi-delete-outline</v-icon>
                                    </v-btn>

                                    <v-btn
                                        fab
                                        x-small
                                        color="success"
                                        @click="addShop('addShop')"
                                    >
                                        <v-icon dark>
                                            mdi-plus
                                        </v-icon>
                                    </v-btn>  
                                </v-btn-toggle>
                            </div>
                        </div>
                    </div>
                </v-card>
        </v-card-text>
        <modal-component v-if="$store.state.sheet"/>
    </v-card>
   
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
            if (this.$store.state.status_reload) {
                this.$store.commit('statusReload',!this.$store.state.status_reload)
            }
            console.log('Config: ', this.$store.state.shops);
            console.log('Url: ', window);
            console.log('Status Relaod: ', this.$store.state.status_reload);
            // this.selectedItem = this.$store.state.shops[0].get_config[0];
        },
        methods: {
            listStores(){
                this.$store.dispatch('getShops');
            },
            addShop(key, data = null, merchant_id = null){
                console.log("Data z:", data);
                this.$store.dispatch('showModal', {key: key, data: data, merchant_id: merchant_id})
            },
            deleteShop(id){
                this.$store.dispatch('deleteShop', id);
            }
        }
    }
</script>
