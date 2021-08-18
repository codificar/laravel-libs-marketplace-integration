import Vue from 'vue'
import Vuex from 'vuex'
import Echo from 'laravel-echo';

Vue.use(Vuex);

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
    dataOrder: '',
    requestStatus: false,
    alphabet: ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"]
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
    ORDER_DETAILS(state, data){
      state.dataOrder = data;
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
      state.orders.splice(index, 1);
    },
    UPDATE_ORDER(state, order){
      state.orders = [
        ...state.orders.filter(element => element.id !== order.id),
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
        payment_mode:5,
        user_card_id:'',
        promo_code:'',
        user_id:'',
        token:'',
        institution_id:this.state.shops.institution_id,
        costcentre_id:1,
        provider_id:null,
        is_admin:false,
        contact_info_enable:false,
        request_info_enable:false,
        contact_info_name:null,
        contact_info_phone:null,
        request_info_number:null,
        request_info_document:null, 
        is_automation: true,
        from: 'panel'
      };
      var shop;
      data.forEach((element, index) => {
        console.log("Index: ", index);
        if (index == 0) {
          shop = this.state.shops.filter(function(item) {
            if (item.id == element.shop_id) {
              return true;
            } else {
              return false;
            }
          });
          var address = JSON.parse(shop[0].get_config[0].address);
          request.points.push({
            address: address.street,
            formatted_address: address.street,
            geometry:{
              location:{
                lat:shop[0].get_config[0].latitude,
                lng:shop[0].get_config[0].longitude
              }
            },
            title:this.state.alphabet[index].toLocaleUpperCase(),
            action:shop[0].name,
            action_type:4,
            complement:"",
            collect_value:'',
            change:null,
            form_of_receipt:null,
            collect_pictures:1,
            collect_signature:1,
            address_instructions: shop[0].name
          });
        }
        console.log('Shop: ', shop);
        request.points.push({
          address: element.formatted_address,
          formatted_address: element.formatted_address,
          geometry:{
            location:{
              lat:element.latitude,
              lng:element.longitude
            }
          },
          title: this.state.alphabet[index+1].toLocaleUpperCase(),
          action:element.display_id,
          action_type:2,
          complement:"",
          collect_value: element.prepaid ? '' : element.order_amount,
          change: element.prepaid ? '' : element.change_for,
          form_of_receipt: element.method_payment,
          collect_pictures:1,
          collect_signature:1,
          address_instructions: element.display_id
        })
        request.institution_id = shop[0].institution_id
        if (!element.prepaid) {
          request.return_to_start = true;
        }
      });
      console.log("points ", request);
      axios.post(`/api/v1/corp/request/create`, request)
        .then(res => {
          console.log("Res: ", res.data);
          if (res.data.success) {
            res.data.points.forEach((element, index) => {
              console.log("Elementy: ", element);
              data.forEach((e, i) => {
                console.log("data displayId: ", e);
                if (e.display_id == element.action) {
                  console.log("Order request: ", e);
                  e['request_id']     = res.data.request_id;
                  e['tracking_route'] = res.data.request_id;
                  commit('UPDATE_ORDER', e);
                  this.dispatch('updateOrder', e);
                }
              });
            });
            commit('STATUS_REQUEST');
            Vue.swal.fire({
              title: 'Sucesso!',
              text: "Corrida criada com sucesso!",
              icon: 'success',
              showConfirmButton: false,
              timer: 1500
            });
            console.log("Data request: ", data);
            console.log("Orders: ", this.state.orders);
          } else {
            if (res.data.error) {
              Vue.swal.fire({
                title: 'Atenção!',
                text: res.data.error,
                icon: 'warning',
                showConfirmButton: true,
              }).then((result) => {              
                window.location.reload();
              });
            } else {
              Vue.swal.fire({
                title: 'Atenção!',
                text: res.data.errors[0],
                icon: 'warning',
                showConfirmButton: true,
              }).then((result) => {              
                window.location.reload();
              })
            }
          }
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
            showConfirmButton: true,
          }).then((result) => {              
            window.location.reload();
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
    showDetail({commit}, data){
      console.log("showDetail");
      commit('showDetails', data.key);
      commit('ORDER_DETAILS', data);
    },
    readyToPickup({commit}, data){
      console.log("Entrou readyToPickup: ", data);
      axios.post('/corp/api/order/readyToPickup', {
        'id': data.shop_id,
        's_id':   data.order_id
      })
      .then(res => {
        console.log('Save ',res);
        // commit('UPDATE_ORDER', res.data)
        console.log(res);
        if (res.status == 200) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Confirmado com sucesso!",
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
          });
          window.location.reload();
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
    cancelOrder({commit}, data){
      console.log("Entrou dispatch: ", data);
      axios.post('/corp/api/order/cancel', {
        'id': data.shop_id,
        's_id':   data.order_id
      })
      .then(res => {
        console.log('Save ',res);
        commit('DELETE_ORDER', res.data)
        console.log(res);
        if (res.status == 200) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Confirmado com sucesso!",
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
          });
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
    confirmOrder({commit}, data) {
      console.log("Entrou dispatch: ", data);
      axios.post(`/corp/api/order/${data.order_id}/confirm`, {
        'id': data.shop_id,
        's_id':   data.order_id
      })
      .then(res => {
        console.log('Save ',res);
        // commit('UPDATE_ORDER', res.data)
        console.log(res);
        if (res.status == 200) {
          Vue.swal.fire({
            title: 'Sucesso!',
            text: "Confirmado com sucesso!",
            icon: 'success',
            showConfirmButton: false,
            timer: 1500
          });
          window.location.reload();
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
      console.log("Entrou getOrders", id);
      commit('CLEAR_ORDERS')
      var status = this.state.selectedShop.status_reload == 1 ? true : false;
      console.log("Data 2: ", status);
      commit('statusReload', status);
      axios.post('/corp/api/orders/'+id, id)
        .then(res => {
          console.log("Orders", res.data);
          res.data.forEach(element => {
            commit('CREATE_ORDER', element);
          });
          if (res.status == 200) {
            // Vue.swal.fire({
            //   title: 'Sucesso!',
            //   text: "Lista atualizada com sucesso!",
            //   icon: 'success',
            //   showConfirmButton: false,
            //   timer: 1500
            // });
          } else {
            // Vue.swal.fire({
            //   title: 'Atenção!',
            //   text: res.data.errors,
            //   icon: 'warning',
            //   confirmButtonText: 'OK'
            // })
          }
        }).catch(err => {
          // Vue.swal.fire({
          //   title: 'Error!',
          //   text: err,
          //   icon: 'error',
          //   confirmButtonText: 'OK'
          // })
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
          if (!res.data.code) {
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
          } else if (res.data.code == 401) {
            Vue.swal.fire({
              title: 'Atenção!',
              text: res.data.message,
              icon: 'warning',
              confirmButtonText: 'OK'
            });
          }
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
          if (!res.data.code) {
            Vue.swal.fire({
              title: 'Sucesso!',
              text: "Salvo com sucesso!",
              icon: 'success',
              showConfirmButton: false,
              timer: 1500
            });
            commit('showDetails', data.key);
            window.location.reload();
          } else {
            Vue.swal.fire({
              title: 'Atenção!',
              text: res.data.message,
              icon: 'warning',
              confirmButtonText: 'OK'
            });
          }
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
          // Vue.swal.fire({
          //   title: 'Sucesso!',
          //   text: "Salvo com sucesso!",
          //   icon: 'success',
          //   showConfirmButton: false,
          //   timer: 1500
          // })
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
        console.log("Error", err);
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
          // Vue.swal.fire({
          //   title: 'Sucesso!',
          //   text: "Salvo com sucesso!",
          //   icon: 'success',
          //   confirmButtonText: 'OK'
          // });
          
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