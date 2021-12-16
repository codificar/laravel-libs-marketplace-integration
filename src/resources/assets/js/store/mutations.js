const mutations = {
  STATUS_RELOAD (state, status) {
    state.status_reload = status;
  },
  CREDENTIALS (state, credentials) {
    console.log("Entrou", credentials);
    state.ifood_client_id = credentials.ifood_client_id;
    state.ifood_client_secret = credentials.ifood_client_secret;
  },
  SHOW_DETAILS (state, key) {
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
};

export default mutations

// import { mapMutations } from 'vuex'
// export default {
//   methods: {
//     ...mapMutations({
//       STATUS_RELOAD: 'STATUS_RELOAD', // map `this.cal()` to `this.$store.commit('calculate')`
//       CREDENTIALS: 'CREDENTIALS',
//       SHOW_DETAILS: 'SHOW_DETAILS',
//       STATUS_REQUEST: 'STATUS_REQUEST',
//       DATA_SHOP: 'DATA_SHOP',
//       ORDER_DETAILS: 'ORDER_DETAILS',
//       FETCH_SELECTED_SHOP: 'FETCH_SELECTED_SHOP',
//       CREATE_SHOP: 'CREATE_SHOP',
//       UPDATE_SHOP: 'UPDATE_SHOP',
//       FETCH_SHOPS: 'FETCH_SHOPS',
//       DELETE_SHOP: 'DELETE_SHOP',
//       CREATE_ORDER: 'CREATE_ORDER',
//       FETCH_ORDERS: 'FETCH_ORDERS',
//       CLEAR_ORDERS: 'CLEAR_ORDERS',
//       REQUEST_ORDERS: 'REQUEST_ORDERS',
//       DELETE_ORDER: 'DELETE_ORDER',
//       UPDATE_ORDER: 'UPDATE_ORDER'
//     })
//   }
// }