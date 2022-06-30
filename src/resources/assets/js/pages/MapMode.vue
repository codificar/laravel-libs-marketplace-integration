<template>
	<v-row>
		<v-col class="map">
			<vue-maps :center="center" >
				<div class="row-map ml-5">
					<div class="col-lg-3">
						<div class="card card-outline-info over-map">
							<div class="card-header">
								<div class="my-2 align-center justify-start">
									<h4 class="m-b-0 text-white"> Pedidos </h4>
								</div>
								<div class="my-2 align-center justify-end">
									<v-btn color="white lighten-2" elevation="2" small icon @click="showConfig = !showConfig"><v-icon>mdi-cog</v-icon></v-btn>
								</div>
							</div>
							<div>
								<v-list>
									<v-list-item :input-value="orderSelectedIndex(order) > -1" color="success" v-for="order in $store.state.orders.data" :key="order.id" @click="selectOrder(order)">
										{{ orderSelectedIndex(order) > -1 ? '#' + (orderSelectedIndex(order) + 1) + ' ': '' }} Pedido #{{order.display_id}}
									</v-list-item>
								</v-list>
							</div>
						</div>
					</div>
					<div class="col-lg-4 col-sm-6" v-if="showConfig">
						<div class="card card-outline-info over-map">
							<div class="card-header">
								<div class="my-2 align-center justify-start">
									<h4 class="m-b-0 text-white"> Configurações </h4>
								</div>
								<div class="my-2 align-center justify-end">
									<v-btn color="white lighten-2" elevation="2" small icon @click="showConfig = !showConfig"><v-icon>mdi-close</v-icon></v-btn>
								</div>
							</div>
							<div class="pa-3">
								<div class="mt-5 row">
									<div class="col-12">
										<refresh-screen
											v-if="$store.state.shops.length > 0"
											:isEnable="$store.state.status_reload"
										/>
									</div>
								</div>
								<filter-orders :search-query="searchQuery" :data="data" :column="true"></filter-orders>
							</div>
						</div>
					</div>
					<div class="mt-3" v-if="showConfirm">
						<div class="card card-outline-info over-map">
							<div class="pa-3 info-order align-left">
								<div>
									<h5>Distância estimada</h5>
									<span>123,12 Km</span>
								</div>
								<div>
									<h5>Tempo estimado</h5>
									<span>31 min</span>
								</div>
								<div>
									<h5>Valor</h5>
									<span>R$ 16,54</span>
								</div>
								<div>
									<v-btn color="success" @click="makeRequest('makeRequest')"><v-icon>mdi-motorbike</v-icon></v-btn>
									<v-btn color="error"@click="makeRequest('makeManualRequest')"><v-icon>mdi-google-maps</v-icon></v-btn>
								</div>
							</div>
						</div>
					</div>
				</div>
				<vue-marker
					v-for="(marker, index) in (orderMarkers ? orderMarkers : [])"
					:key="index"
					:title="'Mark' + index"
					:clickable="true"
					:icon="{ url: marker.url }"
					:coordinates="marker.coordinates"
				>
					<div class="info-order">
						<div>
							<h5>Id do pedido</h5>
							<span>{{marker.display_id || '-'}}</span>
						</div>
						<div>
							<h5>Cliente</h5>
							<span>{{marker.client_name || '-'}}</span>
						</div>
						<div>
							<h5>Endereço</h5>
							<span>{{marker.address || '-'}}</span>
						</div>
						<div>
							<h5>Plataforma</h5>
							<span>{{marker.platform || '-'}}</span>
						</div>
					</div>
				</vue-marker>
				<vue-polyline

					:coordinates="this.polyline"
					color="#02f"
				/>
			</vue-maps>
		</v-col>
	</v-row>
</template>

<script>
import ModalComponent from "../components/Modal.vue";
import RefreshScreen from "../components/RefreshScreen.vue";
import FilterOrders from "../components/FilterOrders.vue";
import Icons from '../mixins/icons';
import { VueMaps, VueMarker, VueCallout, VuePolyline } from "vue-maps";
import axios from 'axios';
export default {
	components: {
		ModalComponent,
		RefreshScreen,
		FilterOrders,
		VueMaps,
		VueMarker,
		VueCallout,
		VuePolyline,
	},
	mixins: [Icons],
	data: () => ({
		searchQuery: "",
		data: {},
		showConfig: false,
		center: {
			lat: -20,
			lng: -50
		},
		selectedOrders: [],
		polyline: [],
    }),
	created(){
		this.getShop();
	},
	methods: {
		setMapCenter(address) {
			console.log("ERNDEASAD");
			console.log(address);
			if (address) {
				this.center = {
					lat: address.latitude,
					lng: address.longitude,
				}
			}
		},
		getShop(){
			this.$store.dispatch('getShops');
			this.setMapCenter(this.$store.state.shops[0]);
		},
		selectOrder(order) {
			this.setMapCenter(this.$store.state.shops[0]);
			let orderIndex = this.orderSelectedIndex(order);
			console.log(orderIndex);
			if(orderIndex > -1) {
				this.selectedOrders.splice(orderIndex, 1);
			} else {
				this.selectedOrders.push(order);
			}

			if(this.selectedOrders.length > 1) {
				this.drawRoute();
			} else {
				this.polyline = [];
			}
			this.orderMarkers;
		},
		makeRequest(type = 'makeRequest'){
			this.$store.dispatch(type, this.selectedOrders);
		},
		getOrders() {
			if (this.$store.state.orders) {
				this.loading = !this.loading;
			}
			if (this.$store.state.orders.length == 0) {
				console.log("Vazio");
			}
		},
		orderSelectedIndex(order) {
			return this.selectedOrders.findIndex(e => e.id == order.id);
		},
		drawRoute() {
			let polylineParams = {
				params: {
					waypoints: '['+this.selectedOrders.reduce((result,current) => `${result}${result?',':''}[${current.latitude},${current.longitude}]`, '')+']',
					optimize_route: 0
				}
			};
			let polylineRoute = "/api/v1/libs/geolocation/corp/get_polyline_waypoints";
			new Promise((resolve, reject) => {
				axios
					.get(polylineRoute, polylineParams)
					.then((response) => {

						if (response.data.success) {
							// Se tiver optimize_route, sera necessario reorganizar os pontos de paradas (waypoints)
							//let waypoint_order = response.data.waypoint_order;

							//if (
							//optimize_route &&
							//waypoint_order &&
							//waypoint_order.length > 0
							//) {
							//for (let k = 0; k < waypoint_order.length; k++) {
							//	this.locations[k + 1] = aux[waypoint_order[k] + 1];
							//}
							////atualiza os titulos (A, B, C ...) dos pontos
							//vm.updateLocationTitles();
							//vm.$forceUpdate();
							//}
							this.polyline = response.data.points;

						} else {
							this.$swal({
							title: this.trans("requests.route_fail"),
							html:
								'<label class="text-left alert alert-danger alert-dismissable">' +
								response.data.error +
								"</label>",
							type: "error",
							});
						}
					})
					.catch((error) => {
						console.log(error);
						reject(error);
						return false;
					});
			});
		}
	},
	computed: {
		orderMarkers: function() {
			let shop = this.$store.state.shops[0];
			let markers = [
				{
					coordinates: {lat: shop.latitude, lng: shop.longitude},
					display_id: shop.id,
					address: shop.full_address,
					client_name: 'Loja',
					//platform: order.marketplace,
					//type: provider.driverType,
					url: this.icons["driver"],
					//shadow: icon.shadow,
					//providerId: provider.id,
					//thumb: provider.thumb,
					//first_name: provider.first_name,
					//last_name: provider.last_name,
					//phone: provider.phone,
				}
			];
			this.setMapCenter(shop);

			for( let order of this.orders) {
				const icon = this.orderSelectedIndex(order) > -1 ? this.icons["free"] : this.icons["pin_purple"];
				const point = { lat: order.latitude, lng: order.longitude };

				const marker = {
					coordinates: point,
					display_id: order.display_id,
					address: order.formatted_address,
					client_name: order.client_name,
					platform: order.marketplace,
					//type: provider.driverType,
					url: icon.url,
					//shadow: icon.shadow,
					//providerId: provider.id,
					//thumb: provider.thumb,
					//first_name: provider.first_name,
					//last_name: provider.last_name,
					//phone: provider.phone,
				};
				markers.push(marker);
			}
			return markers;
		},
		orders: function() {
			return this.$store.state.orders.data || [];
		},
		showConfirm: function() {
			return this.selectedOrders.length >= 2;
		}
	}

}
</script>

<style>
.map {
  z-index: 0;
  height: 90vh;
}
.info-order {
	padding: .5rem .8rem;
	text-align: left;
}
.info-order div {
	margin-bottom: .5rem;
}
.info-order div>span {
	text-transform: capitalize;
}
.over-map {
	z-index: 20000;
	margin: 0.5rem;
}
.vertical {
	height: 85vh;
}
.row-map {
	display: flex;
	flex-direction: row;
}
</style>