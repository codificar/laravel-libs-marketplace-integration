import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
  state: {
    drawer: true,
    themeDark: true,
    shops: [],
    orders: [],
    socket: {
      isConnected: false,
      message: '',
      reconnectError: false,
    },
    sheet: false,
    order: '',
    modalContent: null,
    orderDetail: '',
    selectedShop: '',
    selectedOrders: ''

  },
  mutations: {
    toggleDrawer (state) {
      state.drawer = !state.drawer;
    },
    showDetails (state, key) {
      state.sheet = !state.sheet;
      state.modalContent = key
    },
    chageTheme (state) {
        state.themeDark = !state.themeDark;
    },
    FETCH_SELECTED_SHOP(state, shop) {
      return state.selectedShop = shop;
    },
    CREATE_SHOP(state, shop) {
      state.shops.unshift(shop)
    },
    FETCH_SHOPS(state, shops) {
        return state.shops = shops;
    },
    DELETE_SHOP(state, shop) {
      let index = state.shops.findIndex(item => item.id === shop.id);
      state.shops.splice(index, 1);
    },
    CREATE_ORDER(state, order) {
      state.orders.push(order);
    },
    FETCH_ORDERS(state, orders) {
      return state.orders = orders;
    },
    REQUEST_ORDERS(state, orders) {
      return state.selectedOrders = orders;
    },
    DELETE_ORDER(state, order) {
      let index = state.orders.findIndex(item => item.id === order.id);
      state.shops.splice(index, 1);
    },
    UPDATE_ORDER(state, id, order){
      state.orders = [
        ...state.orders.filter(element => element.id !== id),
        order
      ]
    }
  },
  actions: {
    makeRequest({commit}, data){
      console.log("MakeRequest", data);
      var request = {
        points:[],
        return_to_start:false,
        type:22,
        category_id:null,
        payment_mode:1,
        // payment_opt: 1,
        user_card_id:'',
        promo_code:'',
        user_id:'',
        token:'',
        institution_id:1,
        costcentre_id:3,
        provider_id:null,
        is_admin:false,
        contact_info_enable:false,
        request_info_enable:false,
        contact_info_name:null,
        contact_info_phone:null,
        request_info_number:null,
        request_info_document:null
      };
      data.forEach((element, index) => {
        console.log("points ", request);
         request.points.push({
          address: element.formattedAddress,
          formatted_address: element.formattedAddress,
          geometry:{
             location:{
                lat:element.latitude,
                lng:element.longitude
             }
          },
          title: element.displayId,
          action:"",
          action_type:1,
          complement:"",
          collect_value:'',
          change:null,
          form_of_receipt:null,
          collect_pictures:1,
          collect_signature:1
        })
        request.institution_id = this.state.shops[0].institution_id
        // request.user_id = this.state.shops[0].institution_id
      });

      axios.post(`/api/v1/corp/request/create`, request)
        .then(res => {
          console.log("Res: ", res.data);
        })
        .catch(err => {
          console.log("Erro: ", err);
        });
    },
    showDetail({commit}, data){
      console.log("Details", data);
      commit('showDetails', data.key);
    },
    confirmOrder({commit}, id) {
      console.log("Entrou dispatch", id);
      axios.post(`/corp/api/order/${id}/confirm`)
      .then(res => {
        console.log('Save ',res.data.data);
        commit('UPDATE_ORDER', res.data.data)
        console.log(res);
        if (res.status == 200) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Confirmado com sucesso!",
            icon: 'success',
            confirmButtonText: 'OK'
          })
        } else {
          Vue.swal.fire({
            title: 'Atenção!',
            text: res.data,
            icon: 'warning',
            confirmButtonText: 'OK'
          })
        }
      }).catch(err => {
        Vue.swal.fire({
          title: 'Error!',
          text: err,
          icon: 'error',
          confirmButtonText: 'OK'
        })
      })
    },
    getOrders({commit}, id){
      console.log("Entrou getOrders");
      axios.get('/corp/api/orders/'+id, id)
        .then(res => {
          console.log("Orders", res.data);
          res.data.forEach(element => {
            commit('CREATE_ORDER', element);
          });
          if (res.status == 200) {
            Vue.swal.fire({
              title: 'Sucesso!',
              text: "Lista atualizada com sucesso!",
              icon: 'success',
              showConfirmButton: false,
              timer: 1500
            })
          } else {
            // Vue.swal.fire({
            //   title: 'Atenção!',
            //   text: res.data.errors,
            //   icon: 'warning',
            //   confirmButtonText: 'OK'
            // })
          }
        }).catch(err => {
          Vue.swal.fire({
            title: 'Error!',
            text: err,
            icon: 'error',
            confirmButtonText: 'OK'
          })
        }
      );
    },
    saveShopConfigs({commit}, data) {
      console.log("Entrou dispatch");
      axios.post('/corp/api/shop', data)
      .then(res => {
        console.log('Save ',res.data);
        commit('CREATE_SHOP', res.data)
        console.log(res);
        if (res.status == 200) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Salvo com sucesso!",
            icon: 'success',
            confirmButtonText: 'OK'
          })
          commit('showDetails', data.key);
        } else if (res.data.data) {
          Vue.swal.fire({
            title: 'Atenção!',
            text: res.data.errors,
            icon: 'warning',
            confirmButtonText: 'OK'
          })
        }
      }).catch(err => {
        Vue.swal.fire({
          title: 'Error!',
          text: err,
          icon: 'error',
          confirmButtonText: 'OK'
        })
      })
    },
    getShops({commit}){
      console.log("Entrou dispatch");
      axios.get('/corp/api/shop')
      .then(res => {
        commit('FETCH_SHOPS', res.data);
        commit('FETCH_SELECTED_SHOP', res.data[0])
        console.log("Shops: ",res);
        if (res.status == 200) {
          this.dispatch('getOrders', res.data[0].id);
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Salvo com sucesso!",
            icon: 'success',
            confirmButtonText: 'OK'
          });
          
        } else if (res.data == 0) {
          Vue.swal.fire({
            title: 'Atenção!',
            text: 'Sem lojas cadastradas. Adicione sua primeira Loja!',
            icon: 'warning',
            confirmButtonText: 'OK'
          });
        }
        if (res.data > 0) {
          this.dispatch('getOrders', res.data[0].id);
        }
      }).catch(err => {
        Vue.swal.fire({
          title: 'Error!',
          text: err,
          icon: 'error',
          confirmButtonText: 'OK'
        })
      })
    }
  },
  getters: {
    drawer: state => {
      return state.drawer;
    },
    sheet: state => {
      return state.sheet;
    },
    themeDark: state => {
      return state.themeDark;
    }
  }
})

export default store;