<template>
    <div class="col-lg-12 card card-outline-info">
        <div class="card-header">
            <div class="row justify-space-between">
                <div class="ma-5 align-center justify-start">
                    <h4 class="m-b-0 text-white">
                        {{ trans('settings.requests') }}
                    </h4>
                </div>
                <div class="ma-5 col-lg-4 col-md-4 align-center justify-center">
                    <refresh-screen
                        v-if="$store.state.shops.length > 0"
                        @on-finish-count="getOrders"
                    />
                </div>
                <div class="ma-5 align-center">
                    <div class="row col-md-12 justify-end">
                        <v-btn
                            class="ma-lg-2 justify-end"
                            v-if="selectedIds.length > 0"
                            :loading="$store.state.requestStatus"
                            :disabled="$store.state.requestStatus"
                            color="success"
                            @click="makeRequest('makeManualRequest')"
                            small
                        >
                            <i class="mdi mdi-google-maps"></i>
                            {{ trans('settings.mount_race') }}
                        </v-btn>
                        <v-btn
                            class="ma-lg-2 justify-end"
                            v-if="selectedIds.length > 0"
                            :loading="$store.state.requestStatus"
                            :disabled="$store.state.requestStatus"
                            color="success"
                            @click="makeRequest()"
                            small
                        >
                            <i class="mdi mdi-motorbike"></i>
                            {{ trans('settings.request_provider') }}
                        </v-btn>
                    </div>
                </div>
            </div>
        </div>
        <filter-orders></filter-orders>

        <div class="col-lg-12 w-100 h-50">
            <v-card-text
                v-if="
                    $store.state.orders &&
                    $store.state.orders.data &&
                    $store.state.orders.data.length == 0
                "
            >
                <div class="card-body">
                    {{ trans('settings.no_have_requests') }}
                </div>
            </v-card-text>
            <div
                v-if="
                    $store.state.orders &&
                    $store.state.orders.data &&
                    $store.state.orders.data.length > 0
                "
                class="card-body"
            >
                <table class="table">
                    <th>{{ trans('settings.store') }}</th>
                    <th>{{ trans('settings.request') }}</th>
                    <th>{{ trans('settings.status') }}</th>
                    <th>{{ trans('settings.payment') }}</th>
                    <th>{{ trans('settings.details') }}</th>
                    <th>{{ trans('settings.selection') }}</th>
                    <tbody>
                        <tr
                            v-for="order in getResultQuery()"
                            :key="order.order_id"
                        >
                            <!-- <div class="d-flex justify-space-between caption"> -->
                            <td class="font-weight-black mr-3">
                                <div class="font-weight-medium">
                                    <v-avatar size="64" class="mr-5">
                                        <v-img
                                            :src="
                                                '/vendor/codificar/marketplace-integration/img/' +
                                                order.factory +
                                                '.jpg'
                                            "
                                            alt="iFood"
                                        />
                                    </v-avatar>
                                </div>
                                <div class="font-weight-medium ml-5">
                                    {{ order.market_name }}
                                </div>
                            </td>
                            <td class="font-weight-black">
                                <div class="font-weight-medium">
                                    {{ trans('settings.request') }}:
                                    {{ order.display_id }}
                                </div>
                                <div class="font-weight-medium">
                                    {{ trans('settings.client') }}:
                                    {{ order.client_name }}
                                </div>
                                <div class="font-weight-medium">
                                    {{ trans('settings.neighborhood') }}:
                                    {{ order.neighborhood }}
                                </div>
                                <div class="font-weight-medium">
                                    {{ trans('settings.address') }}:
                                    {{ order.formatted_address }}
                                </div>
                                <div
                                    class="font-weight-medium"
                                    v-if="order.complement != ''"
                                >
                                    {{ trans('settings.complement') }}:
                                    {{ order.complement }}
                                </div>
                            </td>
                            <td class="font-weight-black">
                                <div class="font-weight-medium">
                                    Status:
                                    {{
                                        order.code == 'RDA' ||
                                        order.code == 'CFM'
                                            ? 'PARA ENTREGA'
                                            : order.full_code
                                    }}
                                </div>
                                <div class="font-weight-medium">
                                    {{ trans('settings.distance') }}:
                                    {{
                                        parseFloat(order.distance).toFixed() /
                                        1000
                                    }}
                                    KM
                                </div>
                            </td>
                            <td class="font-weight-black">
                                <div class="font-weight-medium">
                                    {{ trans('settings.value') }}:
                                    {{
                                        order.order_amount
                                            ? formatCurrency(order.order_amount)
                                            : '-'
                                    }}
                                </div>
                                <div
                                    class="font-weight-medium"
                                    v-if="
                                        !order.prepaid &&
                                        order.method_payment === 'CASH'
                                    "
                                >
                                    {{ trans('settings.payment_money') }}
                                </div>
                                <div
                                    class="font-weight-medium"
                                    v-if="
                                        !order.prepaid &&
                                        order.method_payment === 'CASH'
                                    "
                                >
                                    Troco para:
                                    {{ formatCurrency(order.change_for) }}
                                </div>
                                <div
                                    class="font-weight-medium"
                                    v-if="
                                        !order.prepaid &&
                                        order.method_payment === 'CREDIT'
                                    "
                                >
                                    {{ trans('settings.payment_machine') }}
                                </div>
                                <div
                                    class="font-weight-medium"
                                    v-if="
                                        !order.prepaid &&
                                        order.method_payment == 'CREDIT'
                                    "
                                >
                                    Bandeira: {{ order.card_brand }}
                                </div>
                                <div
                                    class="font-weight-medium"
                                    v-if="order.prepaid"
                                >
                                    {{ trans('settings.payment_online') }}
                                </div>
                            </td>
                            <td class="font-weight-black">
                                <div class="font-weight-medium">
                                    <v-btn
                                        class="ma-1"
                                        small
                                        depressed
                                        color="primary"
                                        @click="showDetails(order)"
                                        style="width: 150px"
                                    >
                                        <v-icon color="white" left
                                            >mdi-clipboard-text</v-icon
                                        >
                                        <span
                                            class="font-weight-white white--text"
                                        >
                                            {{ trans('settings.details') }}
                                        </span>
                                    </v-btn>
                                </div>
                            </td>
                            <td>
                                <div class="grey--text text-darken-1 ma-2 mt-6">
                                    <div class="font-weight-white">
                                        <v-checkbox
                                            v-model="selectedIds"
                                            v-if="
                                                order.request_id == null &&
                                                (order.code == 'CFM' ||
                                                    order.code == 'RDA')
                                            "
                                            :label="
                                                trans('settings.add_delivery')
                                            "
                                            class="ma-2 mt-1"
                                            :value="order.order_id"
                                            :id="order.order_id"
                                        ></v-checkbox>
                                    </div>
                                    <v-btn
                                        class="ma-1"
                                        small
                                        depressed
                                        v-if="order.request_id"
                                        color="primary"
                                        style="width: 150px"
                                        v-bind="attrs"
                                        :href="
                                            '/corp/request/tracking/' +
                                            order.tracking_route
                                        "
                                    >
                                        <v-icon color="white" left
                                            >mdi-map</v-icon
                                        >
                                        {{ trans('settings.to_accompany') }}
                                    </v-btn>
                                </div>
                            </td>
                            <!-- </div> -->
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <pagination
            :data="$store.state.orders"
            @pagination-change-page="fetch"
            align="right"
        >
        </pagination>
        <modal-component v-if="$store.state.sheet" />
    </div>
</template>

<script>
import ModalComponent from '../components/Modal.vue';
import RefreshScreen from '../components/RefreshScreen.vue';
import FilterOrders from '../components/FilterOrders.vue';
import StoreMixin from '../mixins/StoreMixin';
export default {
    components: {
        ModalComponent,
        RefreshScreen,
        FilterOrders,
    },
    mixins: [StoreMixin],

    data: () => ({
        loading: true,
        loader: null,
        attrs: {
            class: 'mb-2',
            boilerplate: true,
            elevation: 2,
        },
        sheet: false,
        selectedIds: [],
        selectedOrders: [],
        enabled: true,
        sliderValue: 0,
        ReloadScreen: {
            type: [Boolean, String],
            default: true,
        },
        objectData: {},
    }),
    mounted() {
        console.log('Component mounted.');
        this.getShop();
        this.getOrders();
        this.resultQuery();
    },
    methods: {
        returnString() {
            return JSON.stringify(this.$store.state.orders);
        },
        disabledBeforeToday(date) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            return date < today;
        },
        getResultQuery() {
            return this.resultQuery(null);
        },

        formatNumber(number) {
            number = number.toFixed(2) + '';
            x = number.split('.');
            x1 = x[0];
            x2 = x.length > 1 ? '.' + x[1] : '';
            var rgx = /(\d+)(\d{3})/;
            while (rgx.test(x1)) {
                x1 = x1.replace(rgx, '$1' + ',' + '$2');
            }
            return x1 + x2;
        },

        formatCurrency(value) {
            return value.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL',
            });
        },
        showDetails(order) {
            console.log('Sheet', this.$store.state.sheet);
            console.log('order', order);
            this.$store.dispatch('showDetail', {
                key: 'orderDetails',
                data: order,
            });
        },
        trackingRoute(order) {
            this.$router.push('/corp/request/tracking/' + order.tracking_route);
        },
    },
    watch: {
        selectedIds: {
            handler: function (newVal, oldVal) {
                this.selectedOrders = [];
                var context = this;

                newVal.forEach(function (orderId) {
                    console.log(orderId);
                    context.$store.state.orders.data.forEach(function (order) {
                        if (order.order_id == orderId)
                            context.selectedOrders.push(order);
                    });
                });
            },
            deep: true,
        },
        loader() {
            const l = this.loader;
            this[l] = !this[l];

            setTimeout(() => (this[l] = false), 3000);

            this.loader = null;
        },
    },
};
</script>
