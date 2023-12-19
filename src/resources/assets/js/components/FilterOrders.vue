<template>
    <div>
        <v-row>
            <v-col
                :cols="column ? '12' : '4'"
                class="d-inline-flex float-left"
                v-if="$store.state.shops.length > 0"
            >
                <store-dropdown></store-dropdown>
            </v-col>
            <v-col :cols="column ? '12' : '4'" class="d-inline-flex mx-0">
                <DatePicker
                    v-model="$store.state.filterOrders.range"
                    lang="pt-br"
                    format="YYYY-MM-DD"
                    formatted="YYYY-MM-DD"
                    :placeholder="trans('settings.period')"
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
                        :placeholder="trans('settings.placeholder_search')"
                    />
                </div>
            </v-col>
        </v-row>
    </div>
</template>

<script>
import StoreDropdown from './StoreDropdown.vue';
export default {
    components: {
        StoreDropdown,
    },
    props: ['column'],
};
</script>

<style>
.box {
    width: 100% !important;
}
</style>
