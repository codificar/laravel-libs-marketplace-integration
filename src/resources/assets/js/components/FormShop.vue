<template>
  <div
    class="card card-outline-info"
  >
    <div class="modal-header">
      <span v-if="$store.state.modalContent == 'addShop'"> Adicionar Loja </span>
      <span v-if="$store.state.modalContent == 'edit_shop'"> Editar da Loja </span>
      <span v-if="$store.state.modalContent == 'add_marketPlace'"> Adicionar da Marketplace </span>
      <span v-if="$store.state.modalContent == 'edit_marketPlace'"> Editar da Marketplace </span>
      <button type="button" @click="closeModal()"><span aria-hidden="true">&times;</span></button>
    </div>
    <div class="panel-body">
      <div class="modal-body">
        <v-form
          ref="form"
          v-model="valid"
          lazy-validation
        >
          <v-text-field
            v-if="$store.state.modalContent == 'addShop'"
            v-model="form.name"
            :rules="nameRules"
            label="Nome da Loja"
            required
          ></v-text-field>
            <v-select
              v-if="$store.state.modalContent == 'add_marketPlace' || $store.state.modalContent == 'edit_marketPlace'"
              v-model="form.select"
              :items="items"
              item-value="id"
              item-text="name"
              :rules="[v => !!v || 'Item é obrigatório']"
              label="Marketplace"
              @change="checkAnswer"
              required
            ></v-select>
          <v-text-field
            v-if="form.select || $store.state.modalContent == 'edit_marketPlace'"
            v-model="form.merchant_id"
            label="MERCHANT_ID"
            required
          ></v-text-field>
          <v-btn
            :disabled="!valid"
            color="success"
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
            id: 1,
            name: 'iFood'
        },
        {
            id: 2,
            name: 'Rappi'
        }       
      ],
      form: {
        id: '',
        name: '',
        select: null,
        client_id: '',
        client_secret: '',
        merchant_id: ''
      },
    }),
    mounted(){
      console.log("Props: ", this.data);
      if (this.$store.state.modalContent == 'edit_shop') {
        this.form.id = this.data.data.id;
        this.form.name = this.data.data.name;        
        this.items.forEach(element => {
          if (element.name.toLowerCase() == this.data.data.get_config[0].market) {
            this.form.select = element
          }
        });
        this.form.client_id = this.data.data.get_config[0].client_id;
        this.form.client_secret = this.data.data.get_config[0].client_secret;
        this.form.merchant_id = this.data.data.get_config[0].merchant_id;
        
      } else if (this.$store.state.modalContent == 'edit_marketPlace') {
        this.form.id = this.data.data.id;
        this.form.name = this.data.data.name;        
        this.items.forEach(element => {
          if (element.name.toLowerCase() == this.data.data.market) {
            this.form.select = element
          }
        });
        this.form.client_id = this.data.data.client_id;
        this.form.client_secret = this.data.data.client_secret;
        this.form.merchant_id = this.data.data.merchant_id;
        
      } else if (this.$store.state.modalContent == 'add_marketPlace') {
        this.form.id = this.data.data.id;
      }
    },
    methods: {
      saveShop() {
        console.log("SaveShop: ", this.form);
        switch (this.$store.state.modalContent) {
          case 'addShop':
            this.$store.dispatch('saveShopConfigs', {key: this.$store.state.modalContent, data: this.form});
          break;
          case 'edit_shop':
            this.$store.dispatch('editShopConfigs', this.form);
          break;
          case 'add_marketPlace':
            this.$store.dispatch('addMarketConfig', this.form);
          break;
          case 'edit_marketPlace':
            console.log("edit_marketplace Form");
            this.$store.dispatch('editMarketConfig', this.form);
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
        console.log(this.form.select);
      },
      closeModal() {
        this.$store.dispatch('showModal', this.$store.state.sheet)
      }
    },
    watch: {
        'form.select': {
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