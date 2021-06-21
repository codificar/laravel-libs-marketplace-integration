<template>
  <v-card
      elevation="2"
  >
    <div :class="$vuetify.theme.dark ? 'grey darken-3' : 'grey lighten-4' + 'mr-8 '">
        <v-card-title
          class="title font-weight-regular justify-space-between"
        >
          <span v-if="$store.state.modalContent == 'addShop'"> Adicionar Loja </span>
          <span v-if="$store.state.modalContent == 'orderDetails'"> Detalhes da Loja </span>
          <span v-if="$store.state.modalContent == 'add_marketPlace'"> Adicionar da Marketplace </span>
          <span v-if="$store.state.modalContent == 'edit_marketPlace'"> Editar da Marketplace </span>
          <v-btn
            class="mt-6"
            text
            color="error"
            @click="closeModal()"
          >
              <v-icon dark>
                  mdi-close
              </v-icon>
          </v-btn>
        </v-card-title>
    </div>
    <v-card-text>
        <v-form
          ref="form"
          v-model="valid"
          lazy-validation
        >
          <v-text-field
            v-if="$store.state.modalContent != 'add_marketPlace'"
            v-model="form.name"
            :rules="nameRules"
            label="Nome da Loja"
            required
          ></v-text-field>

          <v-select
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
            v-if="form.select == 1"
            v-model="form.merchant_id"
            label="MERCHANT_ID"
            required
          ></v-text-field>

          <v-text-field
            v-if="form.select == 1"
            v-model="form.client_id"
            label="CLIENT_ID"
            required
          ></v-text-field>

          <v-text-field
            v-if="form.select == 1"
            v-model="form.client_secret"
            label="CLIENT_SECRET"
            required
          ></v-text-field>

          <v-btn
            :disabled="!valid"
            color="success"
            class="mr-4"
            @click="saveShop"
          >
            Salvar
          </v-btn>
        </v-form>
    </v-card-text>
      
  </v-card>
  
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
        v => (v && v.length <= 10) || 'Name must be less than 10 characters',
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
        name: '',
        select: null,
        client_id: '',
        clinent_secret: '',
        merchant_id: ''
      },
    }),
    mounted(){
      if (this.$store.state.modalContent == 'edit_marketPlace') {
        this.form.name = this.data.data.name;        
        this.items.forEach(element => {
          if (element.name.toLowerCase() == this.data.data.get_config[0].market) {
            this.form.select = element
          }
        });
        this.form.client_id = this.data.data.get_config[0].client_id;
        this.form.client_secret = this.data.data.get_config[0].client_secret;
        this.form.merchant_id = this.data.data.merchant_id;
        console.log("DataForm: ", this.form);
      }
      
    },
    methods: {
      saveShop() {
        this.$store.dispatch('saveShopConfigs', this.form);
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
        select: {
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