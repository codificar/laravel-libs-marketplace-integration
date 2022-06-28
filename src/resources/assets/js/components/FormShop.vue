<template>
  <div
    class="card card-outline-info"
  >
    <div class="modal-header">
      <span v-if="$store.state.modalContent == 'addShop'"> Adicionar Loja / Localização </span>
      <span v-if="$store.state.modalContent == 'editShop'"> Editar Loja / Localização </span>
      <span v-if="$store.state.modalContent == 'addMarketplace'"> Adicionar configuração marketplace </span>
      <span v-if="$store.state.modalContent == 'editMarketplace'"> Editar configuração marketplace </span>
      <button type="button" @click="closeModal()"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="panel-body">
      <div class="modal-body">
        <v-form
          ref="form"
          v-model="valid"
          lazy-validation
          data-test="shop_form"
        >
          <v-text-field
            v-if="$store.state.modalContent == 'addShop'"
            v-model="form.name"
            :rules="nameRules"
            label="Nome da Loja"
            data-test="shop_name"
            required
          >
          </v-text-field>

          <v-select
            v-if="$store.state.modalContent == 'addMarketplace' || $store.state.modalContent == 'editMarketplace'"
            v-model="form.marketplace"
            :items="items"
            item-value="id"

            data-test="marketplace_name"
            item-text="name"
            :rules="[v => !!v || 'Item é obrigatório']"
            label="Marketplace"
            @change="checkAnswer"
            required
          >
          </v-select>
          
          <v-text-field
            v-if="form.marketplace || $store.state.modalContent == 'editMarketplace'"
            v-model="form.merchant_name"

            data-test="merchant_name"
            label="Nome da loja no marketplace"
            required
          >
          </v-text-field>

          <v-text-field
            v-if="form.marketplace || $store.state.modalContent == 'editMarketplace'"
            v-model="form.merchant_id"
            data-test="merchant_id"
            label="Id da loja no marketplace"
            required
          >
          </v-text-field>

          <v-btn
            :disabled="!valid"
            color="success"
            data-test="saveShop"
            class="mr-0"
            @click="saveShop"
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
        id: '',
        name: '',
        marketplace: null,
        client_id: '',
        client_secret: '',
        merchant_id: '',
        merchant_name: ''
      },
    }),
    mounted(){
      console.log("Props: ", this.data);
      if (this.$store.state.modalContent == 'editShop') {
        this.form.id = this.data.data.id;
        this.form.name = this.data.data.name;        
        this.items.forEach(element => {
          if (element.name.toLowerCase() == this.data.data.get_config[0].market) {
            this.form.marketplace = element
          }
        });
        this.form.client_id = this.data.data.get_config[0].client_id;
        this.form.client_secret = this.data.data.get_config[0].client_secret;
        this.form.merchant_id = this.data.data.get_config[0].merchant_id;
        this.form.merchant_name = this.data.data.get_config[0].merchant_name;
        
      } else if (this.$store.state.modalContent == 'editMarketplace') {
        this.form.id = this.data.data.id;
        this.form.name = this.data.data.name;        
        this.items.forEach(element => {
          if (element.name.toLowerCase() == this.data.data.market) {
            this.form.marketplace = element
          }
        });
        this.form.client_id = this.data.data.client_id;
        this.form.client_secret = this.data.data.client_secret;
        this.form.merchant_id = this.data.data.merchant_id;
        this.form.merchant_name = this.data.data.merchant_name;
        
      } else if (this.$store.state.modalContent == 'addMarketplace') {
        this.form.id = this.data.data.id;
      }
    },
    methods: {
      saveShop() {
        console.log("SaveShop: ", this.form);
        switch (this.$store.state.modalContent) {
          case 'addShop':
          case 'editShop':
            this.$store.dispatch('storeShop', this.form);
          break;
          case 'addMarketplace':
          case 'editMarketplace':
            this.$store.dispatch('storeMarketConfig', this.form);
        
          break;
          default:

          break;
        }
      },
      editShop(id){
        this.$store.dispatch('editShop', id);
      },
      deleteShop(id){
        this.$store.dispatch('deleteShop', id);
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
      checkAnswer(item) {
        console.log(item);
        console.log(this.form.marketplace);
      },
      closeModal() {
        this.$store.dispatch('showModal', this.$store.state.sheet)
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