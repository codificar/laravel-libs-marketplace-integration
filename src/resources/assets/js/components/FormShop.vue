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


          <VueAddressAutocomplete
              class="autocompleteAdress"
              ref="address_autocomplete"
              :PlaceHolderText="
                trans('requests.type_and_select_address')
              "
              :AutocompleteParams="getAutocompleteParams"
              :AutocompleteUrl="autocompleteUrl"
              :GeocodeUrl="geocodeUrl"
              :GetPlaceDetailsRoute="placeDetailUrl"
              :MinLength="5"
              :Delay="1000"
              @addressSelected="setPlace"
              :Address="form.full_address"
              
              :NeedAddressNumberText="
                trans('common_address.with_no_number')
              "
              :PurveyorPlaces="placesProvider"
              :RefreshSessionDeflateSearch="true"
          />

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

import VueAddressAutocomplete from "vue-address-autocomplete";

export default {
    name: 'FormShop',
    components: {
      VueAddressAutocomplete
    },
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
        full_address: '',
        latitude: 0,
        longitude: 0
      },
    }),
    mounted(){
      console.log("Props: ", this.data);
      
      if(this.data) {
        this.form.shop_id       = this.data.id;
        this.form.name          = this.data.name;   
        this.form.full_address  = this.data.full_address;   
        this.$refs.address_autocomplete.setPropsAdress(this.form.full_address);
        this.form.latitude      = this.data.latitude;   
        this.form.longitude     = this.data.longitude;        
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
      },
      setPlace(place) {
        console.log('place:', place);
        this.form.full_address  = place.address;
        this.form.latitude      = place.latitude;
        this.form.longitude     = place.longitude;
        
      },
    },
    computed: {
      autocompleteUrl() {
        return  window.marketplaceSettings.autocompleteUrl ;
      },
      geocodeUrl() {
        return  window.marketplaceSettings.geocodeUrl ;
      },
      placeDetailUrl() {
        return  window.marketplaceSettings.placeDetailUrl ;
      },
      placesProvider() {
        return  window.marketplaceSettings.placesProvider ;
      },
      fullAddress() {
        return  form.full_address ;
      },
      getAutocompleteParams() {
        const params = {
          id: window.marketplaceSettings.userId,
          user_id: window.marketplaceSettings.userId,
          token: window.marketplaceSettings.userToken,
          latitude: 25,
          longitude: 45,
        };

        return params;
      },
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

  .autocompleteAdress .vs__dropdown-toggle{
    border:none;
    border-bottom: 1px solid black;
    border-radius: 0 ;
  } 


  .autocompleteAdress .vs__dropdown-menu {
    position:relative;
  }

</style>