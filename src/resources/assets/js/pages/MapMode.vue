<template>
    <v-row class="map-mode">
        <v-col cols="3">
            <div>
                <div class="card card-outline-info">
                    <div class="card-header">
                        <div class="my-2 align-center justify-start">
                            <h4 class="m-b-0 text-white">Pedidos</h4>
                        </div>
                        <div class="my-2 align-center justify-end">
                            <v-btn
                                color="white lighten-2"
                                elevation="2"
                                small
                                icon
                                @click="showFilters = !showFilters"
                                ><v-icon>mdi-layers-search</v-icon></v-btn
                            >
                        </div>
                    </div>
                    <store-dropdown></store-dropdown>
                    <div>
                        <v-virtual-scroll
                            v-if="
                                $store.state.orders.data &&
                                    $store.state.orders.data.length > 0 &&
                                    $store.state.filterOrders.shopId > 0
                            "
                            height="750"
                            item-height="50"
                            :items="$store.state.orders.data"
                        >
                            <template v-slot:default="{ item, index }">
                                <v-list-item
                                    v-if="!item.request_id"
                                    :input-value="orderSelectedIndex(item) > -1"
                                    color="success"
                                    :key="item.id"
                                    @click="selectOrder(item)"
                                >
                                    {{
                                        orderSelectedIndex(item) > -1
                                            ? '#' +
                                              (orderSelectedIndex(item) + 1) +
                                              ' '
                                            : '#' + (index + 1) + ' '
                                    }}
                                    Pedido - {{ item.display_id }}
                                </v-list-item>
                            </template>
                        </v-virtual-scroll>
                        <v-list
                            v-if="
                                !$store.state.orders.data ||
                                    $store.state.orders.data.length == 0
                            "
                        >
                            <v-list-item>
                                Sem Pedidos para Mostrar
                            </v-list-item>
                        </v-list>
                    </div>
                </div>
            </div>
        </v-col>
        <v-col cols="7">
            <vue-maps
                :provider="mapsProvider"
                :center="center"
                :displayCenterMarker="false"
            >
                <div class="ml-7">
                    <div class="filters col-lg-4 col-sm-6" v-if="showFilters">
                        <div class="card card-outline-info over-map">
                            <div class="card-header">
                                <div class="my-2 align-center justify-start">
                                    <h4 class="m-b-0 text-white">
                                        Filtros
                                    </h4>
                                </div>
                                <div class="my-2 align-center justify-end">
                                    <v-btn
                                        color="white lighten-2"
                                        elevation="2"
                                        small
                                        icon
                                        @click="showFilters = !showFilters"
                                        ><v-icon>mdi-close</v-icon></v-btn
                                    >
                                </div>
                            </div>
                            <div class="pa-3">
                                <div class="mt-5 row">
                                    <div class="col-12">
                                        <refresh-screen
                                            v-if="
                                                $store.state.shops &&
                                                    $store.state.shops.length >
                                                        0
                                            "
                                        />
                                    </div>
                                </div>
                                <filter-orders :column="true"></filter-orders>
                            </div>
                        </div>
                    </div>
                    <div class="filters col-lg-4 col-sm-6" v-if="showConfirm">
                        <div class="card card-outline-info over-map">
                            <div class="pa-3 info-order">
                                <div class="d-flex flex-row">
                                    <div class="text-center">
                                        <h5>Distância estimada</h5>
                                        <span>{{ estimated_distance }}</span>
                                    </div>
                                    <div class="ml-3 text-center">
                                        <h5>Tempo estimado</h5>
                                        <span>{{ estimated_time }}</span>
                                    </div>
                                    <div class="ml-3 text-center">
                                        <h5>Valor Estimado</h5>
                                        <span>{{ estimated_price }}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <v-btn
                                        color="success"
                                        class="mt-3"
                                        small
                                        @click="
                                            makeRequest('makeRequest');
                                            polyline = [];
                                        "
                                        ><v-icon>mdi-motorbike</v-icon>
                                        Solicitar Entregador</v-btn
                                    >
                                    <v-btn
                                        color="error"
                                        class="mt-2 align-text-left"
                                        small
                                        @click="
                                            makeRequest('makeManualRequest')
                                        "
                                        ><v-icon>mdi-google-maps</v-icon> Montar
                                        corrida</v-btn
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <vue-marker
                    v-if="shopMarker && shopMarker.url"
                    :title="'Shop'"
                    :clickable="true"
                    :icon="{
                        url: shopMarker ? shopMarker.url : null,
                        size: shopMarker ? shopMarker.size : null,
                        anchor: shopMarker ? [0, 0] : null,
                    }"
                    :coordinates="shopMarker.coordinates"
                >
                    <div>{{ shopMarker.shop_name }}</div>
                </vue-marker>
                <vue-marker
                    v-for="(marker, index) in orderMarkers ? orderMarkers : []"
                    :key="index"
                    :title="'Mark' + index"
                    :clickable="true"
                    :icon="{
                        url: marker ? marker.url : null,
                        size: marker ? marker.size : null,
                    }"
                    :coordinates="marker.coordinates"
                >
                    <div class="info-order">
                        <div>
                            <h5>Id do pedido</h5>
                            <span>{{ marker.display_id || '-' }}</span>
                        </div>
                        <div>
                            <h5>Cliente</h5>
                            <span>{{ marker.client_name || '-' }}</span>
                        </div>
                        <div>
                            <h5>Endereço</h5>
                            <span>{{ marker.address || '-' }}</span>
                        </div>
                        <div>
                            <h5>Plataforma</h5>
                            <span>{{ marker.platform || '-' }}</span>
                        </div>
                    </div>
                </vue-marker>
                <vue-polyline :coordinates="this.polyline" color="#02f" />
            </vue-maps>
        </v-col>
    </v-row>
</template>

<script>
import ModalComponent from '../components/Modal.vue';
import RefreshScreen from '../components/RefreshScreen.vue';
import FilterOrders from '../components/FilterOrders.vue';
import StoreDropdown from '../components/StoreDropdown.vue';
import Icons from '../mixins/icons';
import StoreMixin from '../mixins/StoreMixin';
import { VueMaps, VueMarker, VueCallout, VuePolyline } from 'vue-maps';
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
        StoreDropdown,
    },
    mixins: [Icons, StoreMixin],
    data: () => ({
        loading: false,
        showFilters: false,
        center: {
            lat: -20,
            lng: -50,
        },
        shopMarker: {},
        selectedOrders: [],
        polyline: [],
        estimated_distance: '',
        estimated_time: '',
        estimated_price: '',
        mapsProvider: 'osm',
    }),
    mounted() {
        console.log(
            'Component mounted. mapsProvider',
            window.marketplaceSettings.mapsProvider
        );
        this.mapsProvider = window.marketplaceSettings.mapsProvider;
        this.getShop();
    },
    methods: {
        setMapCenter(address) {
            console.log(address);
            if (address) {
                this.center = {
                    lat: address.latitude,
                    lng: address.longitude,
                };
            }
        },
        selectOrder(order) {
            this.showFilters = false;
            this.setMapCenter(this.$store.state.shops[0]);
            let orderIndex = this.orderSelectedIndex(order);
            console.log(orderIndex);
            if (orderIndex > -1) {
                this.selectedOrders.splice(orderIndex, 1);
            } else {
                this.selectedOrders.push(order);
            }

            if (this.selectedOrders.length >= 1) {
                this.drawRoute();
            } else {
                this.polyline = [];
            }
            this.orderMarkers;
        },
        orderSelectedIndex(order) {
            return this.selectedOrders.findIndex((e) => e.id == order.id);
        },
        drawRoute() {
            let shop = this.selectedOrders[0].shop;
            let shopCoord = `[${shop.latitude},${shop.longitude}]`;
            let polylineParams = {
                params: {
                    waypoints:
                        '[' +
                        this.selectedOrders.reduce(
                            (result, current) =>
                                `${result}${result ? ',' : ''}[${
                                    current.latitude
                                },${current.longitude}]`,
                            shopCoord
                        ) +
                        ']',
                    optimize_route: 0,
                },
            };
            let polylineRoute =
                '/api/v1/libs/geolocation/corp/get_polyline_waypoints';
            let estimateRoute = '/corp/estimate/estimate_request';
            new Promise((resolve, reject) => {
                axios
                    .get(polylineRoute, polylineParams)
                    .then((response) => {
                        if (response.data.success) {
                            //this.estimated_distance =
                            //response.data.distance_text;
                            //this.estimated_time = response.data.duration_text;
                            this.polyline = response.data.points;
                        } else {
                            this.$swal({
                                title: this.trans('requests.route_fail'),
                                html:
                                    '<label class="text-left alert alert-danger alert-dismissable">' +
                                    response.data.error +
                                    '</label>',
                                type: 'error',
                            });
                        }
                    })
                    .catch((error) => {
                        console.log(error);
                        reject(error);
                        return false;
                    });
            });
            let pointsEstimate = [];
            pointsEstimate.push({
                geometry: {
                    location: {
                        lat: shop.latitude,
                        lng: shop.longitude,
                    },
                },
            });
            let estimateParams = {
                points: pointsEstimate.concat(
                    this.selectedOrders.map((element) => {
                        return {
                            geometry: {
                                location: {
                                    lat: element.latitude,
                                    lng: element.longitude,
                                },
                            },
                        };
                    })
                ),
                provider_type: window.marketplaceSettings.providerType,
                return_to_start: true,
            };
            new Promise((resolve, reject) => {
                axios
                    .post(estimateRoute, estimateParams)
                    .then((response) => {
                        if (response.data.estimate_info.success) {
                            this.estimated_price =
                                response.data.estimate_info.estimated_price_formatted;
                            this.estimated_distance =
                                response.data.estimate_info.distance_text;
                            this.estimated_time =
                                response.data.estimate_info.duration_text;
                        } else {
                            this.$swal({
                                title: this.trans('requests.route_fail'),
                                html:
                                    '<label class="text-left alert alert-danger alert-dismissable">' +
                                    response.data.error +
                                    '</label>',
                                type: 'error',
                            });
                        }
                    })
                    .catch((error) => {
                        console.log(error);
                        reject(error);
                        return false;
                    });
            });
        },
    },
    computed: {
        orderMarkers: function() {
            let markers = [];
            let selectedIndex = -1;
            let index = 1;
            let replaceIndex = 1;

            for (let order of this.orders) {
                if (order.request_id) continue;

                selectedIndex = this.orderSelectedIndex(order);
                console.log('selectedIndex > ', selectedIndex);
                // primeiro ponto selecionado
                if (index == 1) {
                    let shop_id = order.shop_id;

                    let shop = this.$store.state.shops.filter(function(item) {
                        if (item.id == shop_id) {
                            return true;
                        } else {
                            return false;
                        }
                    });
                    shop = shop[0];

                    this.shopMarker = {
                        coordinates: {
                            lat: shop.latitude,
                            lng: shop.longitude,
                        },
                        address: shop.full_address,
                        shop_name: shop.name,
                        url: this.icons['shop'].url,
                        size: this.icons['shop'].size,
                    };

                    console.log('shop > ', this.icons['shop'].url);
                    this.setMapCenter(shop);
                }

                const icon = this.icons['point_gray'];
                let iconUrl = icon.url.replace('%d', index);

                if (selectedIndex > -1) {
                    icon = this.icons['point_green'];
                    replaceIndex = selectedIndex + 1;
                    iconUrl = icon.url.replace('%d', replaceIndex);
                }

                const point = { lat: order.latitude, lng: order.longitude };

                console.log('point >', point);

                const marker = {
                    coordinates: point,
                    display_id: order.display_id,
                    address: order.formatted_address,
                    client_name: order.client_name,
                    platform: order.marketplace,
                    url: iconUrl,
                    size: icon.size,
                };

                //console.log('marker >', marker);
                markers.push(marker);

                index++;
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
            console.log('asdadasd');
            this.selectedOrders = [];
        },
    },
};
</script>

<style>
#app,
.vue-app {
    margin: 0px;
    padding: 0px;
}
.container {
    max-width: 100%;
    padding: 0px;
}
.map-mode {
    width: 110%;
    min-height: 800px;
}
.col-7 {
    max-width: 100%;
    flex: 0 0 76%;
    margin-left: -25px;
}
.filters {
    margin: -20px;
    margin-left: -48px !important;
    z-index: 1099;
}
.card-header:first-child {
    border-radius: 0 0 0 0;
}
.info-order {
    padding: 0.5rem 0.8rem;
    text-align: left;
}
.info-order div {
    margin-bottom: 0.5rem;
}
.info-order div > span {
    text-transform: capitalize;
}
.over-map {
    z-index: 999;
    margin: 0.5rem;
}
.vertical {
    height: 85vh;
}
</style>
