<template>
    <div class="search-wrapper panel-heading col-sm-12">
        <select
            class="custom-select custom-select-lg col-sm-12 pa-2"
            name="shops"
            id="shops"
            v-model="$store.state.filterOrders.shopId"
        >
            <option
                v-bind:key="null"
                :value="null"
                selected
                label="Selecione uma loja"
            >
            </option>
            <option
                v-for="item in $store.state.shops"
                v-bind:key="item.id"
                :value="item.id"
                :label="item.name"
            >
            </option>
        </select>
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
