<template>
	<div>
		<v-row>
			<v-col :cols="column?'12':'4'" class="d-inline-flex float-left" v-if="$store.state.shops.length > 0">
				<div class="search-wrapper panel-heading col-sm-12">
					<select class="custom-select custom-select-lg col-sm-12 pa-2" name="shops" id="shops" v-model="data.marketId">
						<optgroup v-for="item in $store.state.shops" v-bind:key="item.id" :label="item.name">
							<option v-for="market in item.get_config" v-bind:key="market.id" :value="market.id">{{market.name}} - {{market.status == 'AVAILABLE' ? 'ABERTA' : 'FECHADA' }}</option>
						</optgroup>
					</select>
				</div>
			</v-col>
			<v-col :cols="column?'12':'4'" class="d-inline-flex mx-0" >
				<DatePicker
					v-model="data.range"
					lang="pt-br"
					format="YYYY-MM-DD"
					formatted="YYYY-MM-DD"
					placeholder="Por período"
					range
					class="box"
				/>
			</v-col>
			<v-col :cols="column?'12':'4'" class="d-inline-flex float-right" v-if="$store.state.orders">
				<div class="search-wrapper panel-heading col-sm-12">
					<input class="form-control" type="text" v-model="searchQuery" placeholder="Buscar por Pedido, Nome do Cliente ou Bairro" />
				</div>
			</v-col>
		</v-row>
	</div>
</template>

<script>
import StoreMixin from "../mixins/StoreMixin";
export default {
	mixins: [
		StoreMixin
	],
	props: ["column"],
	data() {
		return {
			searchQuery: "",
            data: {
                pagination: {
                    actual : 1,
                    itensPerPage : 100
                },
                filters: {
                    institution: '',
                    ItensPerPage: 100
                },
                order: {
                    field: '',
                    direction: ''
                },
                range: [
                    null,
                    null
                ],
                keyword: '',
                marketId : null
            }
		}
	},
	methods: {
        //
	},
	watch: {
		"data.marketId": {
			handler: function(newVal, oldVal){
				this.fetch(this.data);
			}
		},
        "data.range": {
            handler: function(newVal, oldVal){
                console.log("OldVal: ", oldVal);
                console.log("newVal: ", newVal);
                if (newVal == undefined) {
                    this.data.range = oldVal;
                } else {
                    this.data.range = newVal;
                }
                this.fetch(this.data);
            },
            deep: true
        },
	}
}
</script>

<style>
 .box {
	width: 100%!important;
 }
</style>