<template>
	<v-row>
		<v-col cols="3" class="justify-content-between d-flex">
			<div class="col-lg-12 card card-outline-info">
				<div class="card-header">
					<div class="row justify-space-between">
						<div class="ma-5 align-center justify-start">
							<h4 class="m-b-0 text-white"> Pedidos </h4>
						</div>
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
		</v-col>
		<v-col class="map">
			<vue-maps :center="center" >
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
import Icons from '../mixins/icons';
import { VueMaps, VueMarker, VueCallout, VuePolyline } from "vue-maps";
import axios from 'axios';
export default {
	components: {
		ModalComponent,
		RefreshScreen,
		VueMaps,
		VueMarker,
		VueCallout,
		VuePolyline,
	},
	mixins: [Icons],
	data: () => ({
		center: {
			lat: -20,
			lng: -50
		},
		selectedOrders: [],
		polyline: [],
    }),
	mounted() {
		this.setMapCenter()
	},
	created(){
		this.getShop();
	},
	methods: {
		setMapCenter(address) {
			if (address) {
				this.center = {
					lat: address.latitude,
					lng: address.longitude,
				}
			}
		},
		getShop(){
			console.log("getShops");
			this.$store.dispatch('getShops');
		},
		selectOrder(order) {
			console.log("Slex order");
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
			let markers = [], first = null;

			for( let order of this.orders) {
				const icon = this.orderSelectedIndex(order) > -1 ? this.icons["free"] : this.icons["pin_purple"];
				if(!first) first = order;
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
			this.setMapCenter(first);
			return markers;
		},
		orders: function() {
			return this.$store.state.orders.data || [];
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
}
.info-order div {
	margin-bottom: .5rem;
}
.info-order div>span {
	text-transform: capitalize;
}

</style>