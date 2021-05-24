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
                    class="pa-md-4 mx-lg-auto mb-2 col-6"
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
                            <div class="font-weight-black">
                                <v-list rounded>
                                    <v-list-item-group
                                        color="primary"
                                    >
                                        <v-list-item
                                        v-for="(item, i) in shop.get_config"
                                        :key="i"
                                        >
                                            <v-avatar
                                                size="32"
                                                class="mr-5"
                                            >
                                                <v-img
                                                    :src="require('../assets/images/ifood.jpg')"
                                                    alt="iFood"
                                                />
                                            </v-avatar>
                                            <v-list-item-content>
                                                <v-list-item-title v-text="toUpperCase(item.market)"></v-list-item-title>
                                            </v-list-item-content>
                                            <v-list-item-icon>
                                                <v-btn
                                                    class="ml-2"
                                                    fab
                                                    dark
                                                    small
                                                    color="cyan"
                                                >
                                                    <v-icon dark>
                                                        mdi-pencil
                                                    </v-icon>
                                                </v-btn>
                                            </v-list-item-icon>
                                            <v-list-item-icon>
                                                <v-btn
                                                    class="mr-2"
                                                    fab
                                                    dark
                                                    small
                                                    color="red"
                                                >
                                                    <v-icon dark>
                                                        mdi-delete-outline
                                                    </v-icon>
                                                </v-btn>
                                            </v-list-item-icon>
                                        </v-list-item>
                                    </v-list-item-group>
                                </v-list>
                            </div>
                            <div class="font-weight-black">
                                <v-btn
                                    class="ma-2"
                                    color="cyan"
                                >
                                    Editar Loja
                                </v-btn>
                                <v-btn
                                    class="ma-2"
                                    color="cyan"
                                >
                                    Remover Loja
                                </v-btn>
                                <v-btn
                                    class="ma-2"
                                    fab
                                    dark
                                    x-small
                                    color="success"
                                >
                                    <v-icon dark>
                                        mdi-plus
                                    </v-icon>
                                </v-btn> 
                            </div>
                        </div>
                    </div>
                </v-card>
        </v-card-text>
        
    </v-card>
   
</template>

<script>
import FormShop from '../components/FormShop';
    export default {
        components:{
            FormShop
        },
        data: () => {
            selectedItem: ''
        },
        mounted() {
            console.log('Component mounted.')
            if (this.$store.state.shops == 0) {
                this.listStores();
            }
            console.log('Config: ', this.$store.state.shops);
            // this.selectedItem = this.$store.state.shops[0].get_config[0];
        },
        methods: {
            listStores(){
                this.$store.dispatch('getShops');
            },
            toUpperCase(string){
                return string.toUpperCase();
            }
        }
    }
</script>
