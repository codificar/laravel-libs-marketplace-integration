<template>
<v-card
    elevation="2"
>
  <div :class="$vuetify.theme.dark ? 'grey darken-3' : 'grey lighten-4' + 'mr-8 '">
      <v-card-title
        class="title font-weight-regular justify-space-between"
      >
        <span> Adicionar Lojas </span>
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
            :rules="[v => !!v || 'Item is required']"
            label="Marketplace"
            @change="checkAnswer"
            required
          ></v-select>

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
      <!-- 
          <v-btn
            color="error"
            class="mr-4"
            @click="reset"
          >
            Reset Form
          </v-btn>

          <v-btn
            color="warning"
            @click="resetValidation"
          >
            Reset Validation
          </v-btn> -->
        </v-form>
    </v-card-text>
    
</v-card>
  
</template>

<script>
export default {
    name: 'FormShop',
    data: () => ({
      valid: true,
      nameRules: [
        v => !!v || 'Name is required',
        v => (v && v.length <= 10) || 'Name must be less than 10 characters',
      ],
      emailRules: [
          v => !!v || 'E-mail is required',
          v => /.+@.+\..+/.test(v) || 'E-mail must be valid',
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
        email: '',
        select: null,
        client_id: '',
        clinent_secret: ''
      },
    }),

    methods: {
      saveShop() {
        this.$store.dispatch('saveShopConfigs', this.form);
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
        this.$store.dispatch('showDetail', this.$store.state.sheet)
      }
    },
}
</script>

<style>

</style>