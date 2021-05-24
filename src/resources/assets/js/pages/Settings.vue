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
            <!-- <form-shop/> -->
        </v-card-text>
        
    </v-card>
   
</template>

<script>
import FormShop from '../components/FormShop';
    export default {
        components:{
            FormShop
        },
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
