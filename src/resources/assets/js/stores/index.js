import Vue from 'vue'
import Vuex from 'vuex'
import Echo from 'laravel-echo';

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
    selectedOrders: '',
    status_reload: false,
    dataShop: '',
    requestStatus: false
  },
  mutations: {
    toggleDrawer (state) {
      state.drawer = !state.drawer;
    },
    statusReload (state, status) {
      state.status_reload = status;
    },
    showDetails (state, key) {
      state.sheet = !state.sheet;
      state.modalContent = key
    },
    STATUS_REQUEST(state){
      state.requestStatus = !state.requestStatus;
    },
    DATA_SHOP (state, data){
      state.dataShop = data;
    },
    chageTheme (state) {
        state.themeDark = !state.themeDark;
    },
    FETCH_SELECTED_SHOP(state, shop) {
      return state.selectedShop = shop;
    },
    CREATE_SHOP(state, shop) {
      state.shops.push(shop)
    },
    UPDATE_SHOP(state, shop){
      state.shops = [
        ...state.shops.filter(element => element.id !== shop.id),
        shop
      ]
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
    CLEAR_ORDERS(state) {
      return state.orders = [];
    },
    REQUEST_ORDERS(state, orders) {
      return state.selectedOrders = orders;
    },
    DELETE_ORDER(state, order) {
      let index = state.orders.findIndex(item => item.id === order.id);
      state.shops.splice(index, 1);
    },
    UPDATE_ORDER(state, order){
      state.orders = [
        ...state.orders.filter(element => element.displayId !== order.displayId),
        order
      ]
    }
  },
  actions: {
    makeRequest({commit}, data){
      commit('STATUS_REQUEST');
      console.log("MakeRequest", data);
      var request = {
        points:[],
        return_to_start:false,
        type:22,
        category_id:null,
        payment_mode:1,
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
        request_info_document:null, 
        is_automation: true
      };
      request.points.push({
        address: this.state.shops[0].get_config[0].address.street,
        formatted_address: this.state.shops[0].get_config[0].address.street,
        geometry:{
          location:{
            lat:this.state.shops[0].get_config[0].address.latitude,
            lng:this.state.shops[0].get_config[0].address.longitude
          }
        },
        title: '1 + ' + this.state.shops[0].name,
        action:this.state.shops[0].name,
        action_type:1,
        complement:"",
        collect_value:'',
        change:null,
        form_of_receipt:null,
        collect_pictures:1,
        collect_signature:1,
        address_instructions: this.state.shops[0].name
      });
      data.forEach((element, index) => {
         request.points.push({
          address: element.formattedAddress,
          formatted_address: element.formattedAddress,
          geometry:{
            location:{
              lat:element.latitude,
              lng:element.longitude
            }
          },
          title: index + 1 + ' ' + element.displayId,
          action:element.displayId,
          action_type:1,
          complement:"",
          collect_value:'',
          change:null,
          form_of_receipt:null,
          collect_pictures:1,
          collect_signature:1,
          address_instructions: element.displayId
        })
        request.institution_id = this.state.shops[0].institution_id
      });
      
      console.log("points ", request);
      axios.post(`/api/v1/corp/request/create`, request)
        .then(res => {
          console.log("Res: ", res.data);
          if (res.data.success) {
            Vue.swal.fire({
              title: 'Sucesso!',
              text: "Corrida criada com sucesso!",
              icon: 'success',
              showConfirmButton: false,
              timer: 1500
            });
            res.data.points.forEach((element, index) => {
              console.log("Elementy: ", element.title);
              data.forEach((e, i) => {
                console.log("data displayId: ", e.displayId);
                if (e.displayId == element.title) {
                  console.log("Order request: ", e);
                  e['request_id']     = res.data.request_id;
                  e['tracking_route'] = res.data.request_id;
                  commit('UPDATE_ORDER', e);
                  this.dispatch('updateOrder', e);
                }
              });
            });
            console.log("Data request: ", data);
            console.log("Orders: ", this.state.orders);
          } else {
            Vue.swal.fire({
              title: 'Atenção!',
              text: res.data.errors[0],
              icon: 'warning',
              showConfirmButton: false,
              timer: 1500
            })
          }
          commit('STATUS_REQUEST');
          window.location.reload();
        })
        .catch(err => {
          console.log("Erro: ", err);
        });
    },
    updateOrder({commit}, data) {
      console.log("UpdateOrder: ", data);
      axios.post('/corp/api/order/update', data)
      .then(res => {
        console.log("Res: ", res);
        if (res.status == 200) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Atualizado com sucesso!",
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
          })
        } else {
          Vue.swal.fire({
            title: 'Atenção!',
            text: res.data,
            icon: 'warning',
            showConfirmButton: false,
            timer: 1500
          })
        }
      })
      .catch(err =>{
        Vue.swal.fire({
          title: 'Error!',
          text: err,
          icon: 'error',
          confirmButtonText: 'OK'
        })
      });
    },
    showModal({commit}, data){
      console.log("Details", data);
      commit('showDetails', data.key);
      commit('DATA_SHOP', data);
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
      commit('CLEAR_ORDERS')
      var status = this.state.selectedShop.status_reload == 1 ? true : false;
      console.log("Data 2: ", status);
      commit('statusReload', status);
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
          window.Echo = new Echo({
            broadcaster: 'socket.io',
            client: require("socket.io-client"),
            host: window.location.hostname + ":6001"
          });

          window.io = require('socket.io-client');

          console.log("DIOGO");

          this.state.orders.forEach(element => {
              console.log('Evento', element);
              if (element.request_id) {
                window.Echo.channel('request.'+element.request_id)
                  .listen('.requestUpdate',
                  (e) => {
                      console.log('Evento 2', e);
                  });
              }
              
          });
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
      data.data.status_reload = this.state.status_reload
      console.log("Entrou dispatch", data);
      console.log("Status", this.state.status_reload);
      axios.post('/corp/api/shop', data.data)
      .then(res => {
        console.log('Save ',res.data);
        console.log(res);
        if (res.status == 201 || res.status == 200) {
          commit('FETCH_SELECTED_SHOP', res.data[0]);
          commit('CREATE_SHOP', res.data[0]);
          commit('showDetails', data.key);
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Salvo com sucesso!",
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
          });
          this.dispatch('getOrders', res.data.id);
        } else if (res.data.data) {
          Vue.swal.fire({
            title: 'Atenção!',
            text: res.data.errors,
            icon: 'warning',
            confirmButtonText: 'OK'
          });
        }
      }).catch(err => {
        Vue.swal.fire({
          title: 'Error!',
          text: err,
          icon: 'error',
          confirmButtonText: 'OK'
        });
      });
    },
    editShopConfigs({commit}, data) {
      data.status_reload = this.state.status_reload
      console.log("Entrou dispatch", data);
      console.log("Status", this.state.status_reload);
      axios.put('/corp/api/shop/update', data)
      .then(res => {
        console.log('Save ',res.data);
        
        console.log(res);
        if (res.status == 200) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Salvo com sucesso!",
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
          });
          commit('UPDATE_SHOP', res.data);
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
    addMarketConfig({commit}, data) {
      data.status_reload = this.state.status_reload
      console.log("Entrou addMarketConfig", data);
      console.log("Status", this.state.status_reload);
      axios.post('/corp/api/market/store', data)
      .then(res => {
        console.log('Save ',res.data);
        console.log(res);
        if (res.status == 201 || res.status == 200) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Salvo com sucesso!",
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
          });
          commit('showDetails', data.key);
          window.location.reload();
        } else if (res.data) {
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
    editMarketConfig({commit}, data) {
      data.status_reload = this.state.status_reload
      console.log("Entrou dispatch", data);
      console.log("Status", this.state.status_reload);
      axios.put('/corp/api/market/update', data)
      .then(res => {
        console.log('Save ',res.data);
        console.log(res);
        if (res.status == 200) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Salvo com sucesso!",
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
          });
          commit('showDetails', data.key);
          window.location.reload();
        } else if (res.data) {
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
    saveRealodStatus({commit}, data){
      this.state.selectedShop['status_reload'] = data;
      console.log("Sleected: ", this.state.selectedShop['status_reload']);
      axios.post('/corp/api/shop/status', this.state.selectedShop)
      .then(res => {
        console.log("res: ",res);
        if (res.status == 200) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Salvo com sucesso!",
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
          })
        } else if (res.data) {
          Vue.swal.fire({
            title: 'Atenção!',
            text: res.data.errors,
            icon: 'warning',
            confirmButtonText: 'OK'
          })
        }
      })
      .catch(err => {
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
        console.log("Data: ", this.state.selectedShop);
        console.log("Shops: ",res);
        if (res.status == 200 && res.data.length > 0) {
          res.data.forEach(element => {
            this.dispatch('getOrders', element.id);
          });
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
        console.log("Saindo: ", this.state.status_reload);
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