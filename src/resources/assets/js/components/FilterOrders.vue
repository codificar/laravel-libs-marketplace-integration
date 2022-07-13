<template>
    <div>
        <v-row>
            <v-col
                :cols="column ? '12' : '4'"
                class="d-inline-flex float-left"
                v-if="$store.state.shops.length > 0"
            >
                <div class="search-wrapper panel-heading col-sm-12">
                    <select
                        class="custom-select custom-select-lg col-sm-12 pa-2"
                        name="shops"
                        id="shops"
                        v-model="$store.state.filterOrders.marketId"
                    >
                        <optgroup
                            v-for="item in $store.state.shops"
                            v-bind:key="item.id"
                            :label="item.name"
                        >
                            <option
                                v-for="market in item.get_config"
                                v-bind:key="market.id"
                                :value="market.id"
                                >{{ market.name }}
                                {{
                                    market.status_label != undefined
                                        ? '- ' + market.status_label
                                        : ''
                                }}</option
                            >
                        </optgroup>
                    </select>
                </div>
            </v-col>
            <v-col :cols="column ? '12' : '4'" class="d-inline-flex mx-0">
                <DatePicker
                    v-model="$store.state.filterOrders.range"
                    lang="pt-br"
                    format="YYYY-MM-DD"
                    formatted="YYYY-MM-DD"
                    placeholder="Por período"
                    range
                    class="box"
                />
            </v-col>
            <v-col
                :cols="column ? '12' : '4'"
                class="d-inline-flex float-right"
                v-if="$store.state.orders"
            >
                <div class="search-wrapper panel-heading col-sm-12">
                    <input
                        class="form-control"
                        type="text"
                        v-model="$store.state.filterOrders.searchQuery"
                        placeholder="Buscar por Pedido, Nome do Cliente ou Bairro"
                    />
                </div>
            </v-col>
        </v-row>
    </div>
</template>

<script>
export default {
    props: ['column'],
};
</script>

<style>
.box {
    width: 100% !important;
}
</style>
