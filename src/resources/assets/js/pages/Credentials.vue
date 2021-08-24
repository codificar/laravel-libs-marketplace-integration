<template>
    <div class="col-lg-12 col-md-12 w-100 h-50 card card-outline-info">
        <v-card-title class="card-header title font-weight-regular justify-space-between">
            <h4 class="white--text"> Configurações iFood</h4>
        </v-card-title>
        <v-card-text>
            <div class="card-body">
                <div class="panel-body">
                    <div class="modal-body">
                        <v-form
                            ref="form"
                            v-model="valid"
                            lazy-validation
                        >
                            <v-text-field
                                v-model="form.ifood_client_id"
                                label="CLIENT_ID"
                                required
                            ></v-text-field>
                            <v-text-field
                                v-model="form.ifood_client_secret"
                                label="CLIENT_SECRET"
                                required
                            ></v-text-field>
                            <v-btn
                                :disabled="!valid"
                                color="success"
                                class="mr-0"
                                @click="saveCredentials"
                            >
                                Salvar
                            </v-btn>
                        </v-form>
                    </div>
                </div>
            </div>
        </v-card-text>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data: () => ({
            valid: true,
            form: {
                ifood_client_id: '',
                ifood_client_secret: '',
            },
        }),
        methods:{
            saveCredentials(){
                this.$store.dispatch('saveCredentials', this.form);
            }
        }
    }
</script>
