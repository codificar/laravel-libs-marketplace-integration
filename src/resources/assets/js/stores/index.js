import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

const store = new Vuex.Store({
	state: {
		drawer: true,
		themeDark: true,
		shops: [],
		orders: {},
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
		alphabet: ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"],
		ifood_client_id: '',
		ifood_client_secret: '',
		data: {
			pagination: {
				actual: 1,
				itensPerPage: 10
			},
			filters: {
				institution: '',
				ItensPerPage: 10,
				startTime: null,
				endTime: null
			},
			order: {
				field: '',
				direction: ''
			},
			range: [
				null,
				null
			]
		}
	},
	mutations: {
		toggleDrawer(state) {
			state.drawer = !state.drawer;
		},
		statusReload(state, status) {
			state.status_reload = status;
		},
		credentials(state, credentials) {
			console.log("Entrou", credentials);
			state.ifood_client_id = credentials.ifood_client_id;
			state.ifood_client_secret = credentials.ifood_client_secret;
		},
		showDetails(state, key) {
			state.sheet = !state.sheet;
			state.modalContent = key
		},
		STATUS_REQUEST(state) {
			state.requestStatus = !state.requestStatus;
		},
		DATA_SHOP(state, data) {
			state.dataShop = data;
		},
		ORDER_DETAILS(state, data) {
			state.dataOrder = data;
		},
		chageTheme(state) {
			state.themeDark = !state.themeDark;
		},
		FETCH_SELECTED_SHOP(state, shop) {
			return state.selectedShop = shop;
		},
		CREATE_SHOP(state, shop) {
			state.shops.push(shop)
		},
		UPDATE_SHOP(state, shop) {
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
		CREATE_ORDER(state, orders) {
			state.orders = orders;
		},
		CLEAR_ORDERS(state) {
			return state.orders = {};
		},
		REQUEST_ORDERS(state, orders) {
			return state.selectedOrders = orders;
		},
		DELETE_ORDER(state, order) {
			let index = state.orders.data.findIndex(item => item.id === order.id);
			state.orders.data.splice(index, 1);
		},
		UPDATE_ORDER(state, order) {
			state.orders.data = [
				...state.orders.data.filter(element => element.id !== order.id),
				order
			]
		}
	},
	actions: {
		makeRequest({ commit }, data) {
			commit('STATUS_REQUEST');
			console.log("MakeRequest", data);
			var request = {
				points: [],
				return_to_start: false,
				type: window.marketplaceSettings.providerType,
				category_id: null,
				payment_mode: window.marketplaceSettings.paymentMode,
				user_card_id: '',
				promo_code: '',
				user_id: '',
				token: '',
				institution_id: this.state.shops.institution_id,
				costcentre_id: null,
				provider_id: null,
				is_admin: false,
				contact_info_enable: false,
				request_info_enable: false,
				contact_info_name: null,
				contact_info_phone: null,
				request_info_number: null,
				request_info_document: null,
				is_automation: true,
				from: 'panel'
			};
			var shop;
			data.forEach((element, index) => {
				console.log("Index: ", index);
				//Aparrentely test to capture the point A
				if (index == 0) {
					shop = this.state.shops.filter(function (item) {
						if (item.id == element.shop_id) {
							return true;
						} else {
							return false;
						}
					});
					var address = JSON.parse(shop[0].get_config[0].address);
					//add collect point, point a
					request.points.push({
						address: address.street,
						formatted_address: address.street,
						geometry: {
							location: {
								lat: shop[0].get_config[0].latitude,
								lng: shop[0].get_config[0].longitude
							}
						},
						title: this.state.alphabet[index].toLocaleUpperCase(),
						action: shop[0].name,
						action_type: 4,
						complement: "",
						collect_value: '',
						change: null,
						form_of_receipt: null,
						collect_pictures: 0,
						collect_signature: 0,
						address_instructions: shop[0].name
					});
				}
				console.log('Shop: ', shop);
				//add delivery points, point B,C, D and so on
				request.points.push({
					address: element.formatted_address,
					formatted_address: element.formatted_address,
					geometry: {
						location: {
							lat: element.latitude,
							lng: element.longitude
						}
					},
					title: this.state.alphabet[index + 1].toLocaleUpperCase(),
					action: `Entregar pedido número ${element.display_id} para ${element.client_name}`,
					action_type: 2,
					complement: `Cliente ${element.client_name}: ${element.complement}`,
					collect_value: element.prepaid ? null : element.order_amount,
					change: element.prepaid ? '' : element.change_for,
					form_of_receipt: element.method_payment,
					collect_pictures: 0,
					collect_signature: 0,
					address_instructions: `Entregar pedido número ${element.display_id} para ${element.client_name}`
				})
				request.institution_id = shop[0].institution_id
				if (!element.prepaid) {
					request.return_to_start = true;
				}
			});

			console.log("points ", request);
			//call creat corp request
			axios.post(`/api/v1/corp/request/create`, request)
				.then(res => {
					console.log("Res: ", res.data);
					if (res.data.success) {
						res.data.points.forEach((element, index) => {
							console.log("Elementy: ", element);
							data.forEach((e, i) => {
								console.log("data displayId: ", e);
								if (element.action.includes(e.display_id)) {
									console.log("Order request: ", e);
									e['point_id'] = element.id
									e['request_id'] = res.data.request_id;
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
		makeManualRequest({ commit }, data) {
			// commit('STATUS_REQUEST');
			let response = createPoints(data, this.state.shops, 'makeManualRequest');
			console.log("POints created:=> ", response);
			post(`/corp/request/add`, response);

		},
		updateOrder({ commit }, data) {
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
				.catch(err => {
					Vue.swal.fire({
						title: 'Error!',
						text: err,
						icon: 'error',
						confirmButtonText: 'OK'
					})
				});
		},
		showModal({ commit }, data) {
			console.log("Details", data);
			commit('showDetails', data.key);
			commit('DATA_SHOP', data);
		},
		showDetail({ commit }, data) {
			console.log("showDetail");
			commit('showDetails', data.key);
			commit('ORDER_DETAILS', data);
		},
		readyToPickup({ commit }, data) {
			console.log("Entrou readyToPickup: ", data);
			axios.post('/corp/api/order/readyToPickup', {
				'id': data.shop_id,
				's_id': data.order_id
			})
				.then(res => {
					console.log('Save ', res);
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
		cancelOrder({ commit }, data) {
			console.log("Entrou dispatch: ", data);
			axios.post('/corp/api/order/cancel', {
				'id': data.shop_id,
				's_id': data.order_id
			})
				.then(res => {
					console.log('Save ', res);
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
		confirmOrder({ commit }, data) {
			console.log("Entrou dispatch: ", data);
			axios.post(`/corp/api/order/${data.order_id}/confirm`, {
				'id': data.shop_id,
				's_id': data.order_id
			})
				.then(res => {
					console.log('Save ', res);
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
		getOrders({ commit }, data, page = 1) {
			console.log('store > get orders > data', data)
			commit('CLEAR_ORDERS')
			axios.post('/libs/marketplace-integration/orders/?page=' + page, data)
				.then(res => {
					
					console.log('CREATE_ORDER', res.data);
					commit('CREATE_ORDER', res.data);
					
				
				}).catch(err => {

					if(err.response && err.response.data && err.response.data.errors) err =  err.response.data.errors;

					Vue.swal.fire({
					  title: 'Error!',
					  text: err,
					  icon: 'error',
					  confirmButtonText: 'OK'
					})
				}
				);
		},
		storeShop({ commit }, data) {
			
			console.log("Entrou dispatch", data);
			
			axios.post('/libs/marketplace-integration/shop/store', data)
				.then(res => {
					console.log('Save ', res.data);
					
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
					
				}).catch(err => {
					if(err.response && err.response.data && err.response.data.errors) err =  err.response.data.errors;
					Vue.swal.fire({
						title: 'Error!',
						text: err,
						icon: 'error',
						confirmButtonText: 'OK'
					});
				});
		},
		storeMarketConfig({ dispatch }, data) {
			console.log("Entrou addMarketConfig");
			axios.post('/libs/marketplace-integration/market_config/store', data)
				.then(res => {
					
					dispatch('getShops');

					this.state.sheet = false ;
					
					Vue.swal.fire({
						title: 'Sucesso!',
						text: "Salvo com sucesso!",
						icon: 'success',
						showConfirmButton: false,
						timer: 1500
					});
					
					
					
				}).catch(err => {
					if(err.response && err.response.data && err.response.data.errors) err =  err.response.data.errors;
					Vue.swal.fire({
						title: 'Error!',
						text: err,
						icon: 'error',
						confirmButtonText: 'OK'
					})
				})
		},
		deleteMarketConfig({ commit }, data) {
			// data.status_reload = this.state.status_reload
			console.log("Entrou deleteMarketConfig", data);
			// console.log("Status", this.state.status_reload);
			axios.delete('/corp/api/market/delete', data)
				.then(res => {
					console.log('deleteMarketConfig reponse data ', res.data);
					console.log(res);
					if (res.status == 200 && res.data.success) {
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
		
		getShops({ commit }) {
			console.log("Entrou dispatch getShops");
			axios.get('/corp/api/shop')
				.then(res => {
					commit('FETCH_SHOPS', res.data);
					commit('FETCH_SELECTED_SHOP', res.data[0])
					console.log("Data: ", this.state.selectedShop);
					console.log("Shops: ", res);
					if (res.data == 0) {
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
		},
		saveCredentials({ commit }, data) {
			console.log("Credentials: ", data);
			axios.post('/admin/settings/credentials/save', data)
				.then(res => {
					if (res.status == 200) {
						Vue.swal.fire({
							title: 'Sucesso!',
							text: res.data.message,
							icon: 'success',
							showConfirmButton: false,
							timer: 1500
						});
					} else {
						Vue.swal.fire({
							title: 'Atenção!',
							html: res.data.message,
							icon: 'warning',
							showConfirmButton: false,
							timer: 1500
						});
					}
				}).catch(err => {
					Vue.swal.fire({
						title: 'Error!',
						text: err,
						icon: 'error',
						confirmButtonText: 'OK'
					})
				});
		},
		getCredentials({ commit }) {
			console.log("Entrou getCredentials");
			axios.post('/admin/settings/get/credentials/')
				.then(res => {
					if (res.status == 200) {
						console.log("ifood_client_id", res.data.ifood_client_id.value);
						commit('credentials', { 'ifood_client_id': res.data.ifood_client_id.value, 'ifood_client_secret': res.data.ifood_client_secret.value });
						// Vue.swal.fire({
						//   title: 'Sucesso!',
						//   text: res.data.message,
						//   icon: 'success',
						//   confirmButtonText: 'OK'
						// });
					} else {
						Vue.swal.fire({
							title: 'Atenção!',
							html: res.data.message,
							icon: 'warning',
							showConfirmButton: false,
							timer: 1500
						});
					}
				}).catch(err => {
					Vue.swal.fire({
						title: 'Error!',
						text: err,
						icon: 'error',
						showConfirmButton: false,
						timer: 1500
					})
				});
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

/**
 * Function to create points, might be used on mount manual race in new request or create a request to call the provider
 * After, is important improve the js code in this file like tnhction and oter portions
 *
 * @param {*} data
 * @param {*} shops
 * @returns
 */
function createPoints(data, shops, type = '') {
	let alphabet = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"];
	// commit('STATUS_REQUEST');
	console.log('DATA =:> ', data);

	let points = [];
	let returnToStart = false;

	let shop = shops.filter(function (item) {
		if (item.id == data[0].shop_id) {
			return true;
		} else {
			return false;
		}
	});

	let address = JSON.parse(shop[0].get_config[0].address);

	let point = {
		address: address.street,
		formatted_address: address.street,
		title: alphabet[0].toLocaleUpperCase(),
		action: shop[0].name,
		action_type: 4,
		complement: "",
		collect_value: null,
		change: null,
		form_of_receipt: null,
		collect_pictures: 0,
		collect_signature: 0,
		address_instructions: shop[0].name
	};

	let initalPoint = { ...point };

	let location = {
		lat: shop[0].get_config[0].latitude,
		lng: shop[0].get_config[0].longitude,
	};

	if (type == 'makeManualRequest') {
		point['latitude'] = location.lat;
		point['longitude'] = location.lng;
	} else {
		point['geometry'] = {
			location: {
				lat: location.lat,
				lng: location.lng
			}
		}
	}

	points.push(point);

	//only delivery orders
	data.forEach((element, index) => {
		console.log("Point " + index + ": ", element);
		location = {
			lat: element.latitude,
			lng: element.longitude,
		};

		point = {
			order_id: element.order_id,
			address: element.formatted_address,
			formatted_address: element.formatted_address,
			title: alphabet[index + 1].toLocaleUpperCase(),
			action: `Entregar pedido número ${element.display_id} para cliente ${element.client_name}: ${element.complement}`,
			action_type: 2,
			complement: element.complement,
			collect_value: element.prepaid == 0 ? null : element.order_amount,
			change: element.prepaid ? '' : element.change_for,
			form_of_receipt: element.prepaid == 0 ? null : element.method_payment,
			collect_pictures: 0,
			collect_signature: 0,
			address_instructions: `Entregar pedido número ${element.display_id} para cliente ${element.client_name}: ${element.complement}`
		};
		//define if thje location attr is to mount request or call provider
		if (type == 'makeManualRequest') {
			point['latitude'] = location.lat;
			point['longitude'] = location.lng;
		} else {
			point['geometry'] = {
				location: {
					lat: location.lat,
					lng: location.lng
				}
			}
		}

		console.log("createPoint => ", point);
		points.push(point);
		console.log('Shop: ', shop);
		//add delivery points, point B,C, D and so on
		// points.push()
		if (!element.prepaid && !returnToStart) {
			returnToStart = true;
		}
	});
	if(returnToStart) {
		points.push(initalPoint);
	}
	console.log('POints generated =:> ', points);

	let dataRequest = { 'points': points, 'returnToStart': returnToStart }
	return dataRequest;

}

/**
 * sends a request to the specified url from a form. this will change the window location.
 * @param {string} path the path to send the post request to
 * @param {object} params the parameters to add to the url
 * @param {string} [method=post] the method to use on the form
 */
function post(path, params, method = 'post') {
	// The rest of this code assumes you are not using a library.
	// It can be made less verbose if you use one.
	const form = document.createElement('form');
	form.method = method;
	form.action = path;
	form.target = "_blank"
	console.log("points", params.points);
	const hiddenField = document.createElement('input');
	hiddenField.type = 'hidden';
	hiddenField.name = 'points';
	hiddenField.value = JSON.stringify(params.points);

	console.log("returnToStart", params.returnToStart);
	const hiddenField1 = document.createElement('input');
	hiddenField1.type = 'hidden';
	hiddenField1.name = 'returnToStart';
	hiddenField1.value = JSON.stringify(params.returnToStart);

	form.appendChild(hiddenField);
	form.appendChild(hiddenField1);
	document.body.appendChild(form);
	form.submit();
}

export default store;