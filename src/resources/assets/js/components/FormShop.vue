<template>
  <div
    class="card card-outline-info"
  >
    <div class="modal-header">
      <span v-if="$store.state.shop == undefined"> Adicionar Loja / Localização </span>
      <span v-if="$store.state.shop != undefined"> Editar Loja / Localização </span>
      
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
            v-model="form.name"
            :rules="nameRules"
            label="Nome da Loja"
            data-test="shop_name"
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
        shop_id: '',
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
      
      if(this.data) {
        this.form.shop_id = this.data.id;
        this.form.name = this.data.name;        
      }

    },
    methods: {
      saveShop() {
        this.$store.dispatch('storeShop', this.form);
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