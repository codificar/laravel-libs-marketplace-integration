<template>
	<v-row class="map">
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
								<v-list v-if="$store.state.orders && $store.state.orders.data && $store.state.orders.data.length">
									<v-list-item :input-value="orderSelectedIndex(order) > -1" color="success" v-for="order in $store.state.orders.data" :key="order.id" @click="selectOrder(order)">
										{{ orderSelectedIndex(order) > -1 ? '#' + (orderSelectedIndex(order) + 1) + ' ': '' }} Pedido #{{order.display_id}}
									</v-list-item>
								</v-list>
								<v-list v-else>
									<v-list-item>
									Sem Pedidos para Mostrar
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
											v-if="$store.state.shops && $store.state.shops.length > 0"
										/>
									</div>
								</div>
								<filter-orders :column="true"></filter-orders>
							</div>
						</div>
					</div>
					<div class="mt-3" v-if="showConfirm">
						<div class="card card-outline-info over-map">
							<div class="pa-3 info-order align-left">
								<div>
									<h5>Distância estimada</h5>
									<span>{{estimed_distance}}</span>
								</div>
								<div>
									<h5>Tempo estimado</h5>
									<span>{{estimed_time}}</span>
								</div>
								<!--<div>
									<h5>Valor</h5>
									<span>R$ 16,54</span>
								</div>-->
								<div class="d-flex flex-column">
									<v-btn color="success" class="mt-3" small @click="makeRequest('makeRequest')"><v-icon>mdi-motorbike</v-icon> Solicitar Entregador</v-btn>
									<v-btn color="error" class="mt-2 align-text-left" small @click="makeRequest('makeManualRequest')"><v-icon>mdi-google-maps</v-icon> Montar corrida</v-btn>
								</div>
							</div>
						</div>
					</div>
				</div>
				<vue-marker
					:title="'Shop'"
					:clickable="false"
					:icon="{ iconUrl: shopMarker ? shopMarker.url: null }"
					:coordinates="shopMarker.coordinates"
				>
					<div>{{shopMarker.shop_name}}</div>
				</vue-marker>
				<vue-marker
					v-for="(marker, index) in (orderMarkers ? orderMarkers : [])"
					:key="index"
					:title="'Mark' + index"
					:clickable="true"
					:icon="{ iconUrl: marker ? marker.url : null }"
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
import StoreMixin from "../mixins/StoreMixin";
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
	mixins: [Icons, StoreMixin],
	data: () => ({
		loading: false,
		showConfig: false,
		center: {
			lat: -20,
			lng: -50
		},
		shopMarker: {},
		selectedOrders: [],
		polyline: [],
		estimed_distance: "",
		estimed_time: "",
    }),
	methods: {
		setMapCenter(address) {
			console.log(address);
			if (address) {
				this.center = {
					lat: address.latitude,
					lng: address.longitude,
				}
			}
		},
		//getShop(){
		//	this.$store.dispatch('getShops');
		//	this.setMapCenter(this.$store.state.shops[0]);
		//	this.StoreMixin.getOrders();
		//},
		selectOrder(order) {
			this.showConfig = false;
			this.setMapCenter(this.$store.state.shops[0]);
			let orderIndex = this.orderSelectedIndex(order);
			console.log(orderIndex);
			if(orderIndex > -1) {
				this.selectedOrders.splice(orderIndex, 1);
			} else {
				this.selectedOrders.push(order);
			}

			if(this.selectedOrders.length >= 1) {
				this.drawRoute();
			} else {
				this.polyline = [];
			}
			this.orderMarkers;
		},
		orderSelectedIndex(order) {
			return this.selectedOrders.findIndex(e => e.id == order.id);
		},
		drawRoute() {
			let shop = this.$store.state.shops[0];
			let shopCoord = `[${shop.latitude},${shop.longitude}]`;
			let polylineParams = {
				params: {
					waypoints: '['+this.selectedOrders.reduce((result,current) => `${result}${result?',':''}[${current.latitude},${current.longitude}]`, shopCoord)+']',
					optimize_route: 0
				}
			};
			let polylineRoute = "/api/v1/libs/geolocation/corp/get_polyline_waypoints";
			new Promise((resolve, reject) => {
				axios
					.get(polylineRoute, polylineParams)
					.then((response) => {
						if (response.data.success) {
							this.estimed_distance = response.data.distance_text;
							this.estimed_time = response.data.duration_text;
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
			console.log('shops > ordermarkers',this.$store.state.shops);
			let shop = this.$store.state.shops[0];

			let markers = [];

			if(shop) {
				this.shopMarker = {
					coordinates: {lat: shop.latitude, lng: shop.longitude},
					address: shop.full_address,
					shop_name: shop.name,
					url: this.icons["start"].url,
				};
				this.setMapCenter(shop);
			}

			for( let order of this.orders) {
				const icon = this.orderSelectedIndex(order) > -1 ? this.icons["free"] : this.icons["pin_purple"];
				const point = { lat: order.latitude, lng: order.longitude };

				const marker = {
					coordinates: point,
					display_id: order.display_id,
					address: order.formatted_address,
					client_name: order.client_name,
					platform: order.marketplace,
					url: icon.url,
				};
				markers.push(marker);
			}
			return markers;
		},
		orders: function() {
			return this.$store.state.orders.data || [];
		},
		showConfirm: function() {
			return this.selectedOrders.length >= 1;
		},
	},

	watch: {
		orders() {
			console.log("asdadasd");
			this.selectedOrders = [];
		}
	}

}
</script>

<style>
/*.row.map {
  z-index: 0;
  height: 90vh;
  margin-top: -70px;
  padding: 0;
  margin-left: 0;
  margin-right: 0;
}
.col.map {
	padding: 0;
}*/
.row.map {
	position: absolute;
	left: -11px;
	right: -11px;
	top: -70px;
	bottom: -80vh;
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
	z-index: 999;
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