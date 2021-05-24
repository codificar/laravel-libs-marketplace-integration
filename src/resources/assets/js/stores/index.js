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
    selectedShop: ''
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
      state.orders.unshift(order);
    },
    FETCH_ORDERS(state, orders) {
      return state.orders = orders;
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
      console.log("MakeRequest");
      axios.post(`api/v1/admin/request/create`, data)
        .then(res => {
          console.log("Res: ", res.data.data);
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
      axios.post(`/api/order/${id}/confirm`)
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
      axios.get('/api/orders/'+id, id)
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
      axios.post('/api/shop', data)
      .then(res => {
        console.log('Save ',res.data.data);
        commit('CREATE_SHOP', res.data.data)
        console.log(res);
        if (res.status == 201) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Salvo com sucesso!",
            icon: 'success',
            confirmButtonText: 'OK'
          })
        } else {
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
      axios.get('/api/shop')
      .then(res => {
        commit('FETCH_SHOPS', res.data);
        commit('FETCH_SELECTED_SHOP', res.data[0])
        console.log("Shops: ",res);
        if (res.status == 201) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Salvo com sucesso!",
            icon: 'success',
            confirmButtonText: 'OK'
          });
          
        } else {
          // Vue.swal.fire({
          //   title: 'Atenção!',
          //   text: res.data.errors,
          //   icon: 'warning',
          //   confirmButtonText: 'OK'
          // });
        }
        this.dispatch('getOrders', res.data[0].id);
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