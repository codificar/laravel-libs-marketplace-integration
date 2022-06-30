<template>
  <div
    class="card card-outline-info"
  >
    <div class="modal-header">
      <span v-if="$store.state.marketConfig == undefined"> Adicionar configuração marketplace </span>
      <span v-if="$store.state.marketConfig != undefined"> Editar configuração marketplace </span>
      <button type="button" @click="closeModal()"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="panel-body">
      <div class="modal-body">
        <v-form
          ref="form"
          v-model="valid"
          lazy-validation
          data-test="market_form"
        >
        
          <v-select
            v-model="form.marketplace"
            :items="items"
            item-value="id"

            data-test="marketplace_name"
            item-text="name"
            :rules="[v => !!v || 'Item é obrigatório']"
            label="Marketplace"
            required
          >
          </v-select>
          
          <v-text-field
            v-if="form.marketplace"
            v-model="form.merchant_name"

            data-test="merchant_name"
            label="Nome da loja no marketplace"
            required
          >
          </v-text-field>

          <v-text-field
            v-if="form.marketplace"
            v-model="form.merchant_id"
            data-test="merchant_id"
            label="Id da loja no marketplace"
            required
          >
          </v-text-field>

          <v-btn
            :disabled="!valid"
            color="success"
            data-test="storeMarketConfig"
            class="mr-0"
            @click="storeMarketConfig"
          >
            Salvar
          </v-btn>

        </v-form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
    name: 'FormShop',
    props: {
      data: {
        type: Object,
      },
    },
    data: () => ({
      valid: true,
      nameRules: [
        v => !!v || 'Nome é obrigatório',
      ],
      items: [
        {
            id: 'ifood',
            name: 'iFood'
        },
        {
            id: 'hubster',
            name: 'Hubster'
        },
        {
            id: '99food',
            name: '99 Food'
        },
        {
            id: 'zedelivery',
            name: 'Zé Delivery'
        },
        {
            id: 'rappi',
            name: 'Rappi'
        }       
      ],
      form: {
        shop_id: '',
        marketplace: null,
        client_id: '',
        client_secret: '',
        merchant_id: '',
        merchant_name: ''
      },
    }),
    mounted(){
      console.log("Props: ", this.data);

      if(this.data){
        this.form.shop_id       = this.data.shop_id;
        this.form.merchant_name = this.data.name;     
        this.form.marketplace   = this.data.market;     
        this.form.merchant_id   = this.data.merchant_id;     
      }
       
    },
    methods: {
      storeMarketConfig() {
        this.$store.dispatch('storeMarketConfig', this.form);
      },
      validate () {
        this.$refs.form.validate()
      },
      reset () {
        this.$refs.form.reset()
      },
      resetValidation () {
        this.$refs.form.resetValidation()
      },
      closeModal() {
        this.$store.state.sheet = false ;
      }
    },
    watch: {
        'form.marketplace': {
            handler: function(newVal, oldVal){
                console.log("OldVal settings: ", oldVal);
                console.log("newVal settings: ", newVal);
            },
            deep: true
        },
    }
}
</script>

<style>

</style>