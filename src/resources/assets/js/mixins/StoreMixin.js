export default {
	methods: {
		getOrders() {
			if (this.$store.state.orders) {
				this.loading = !this.loading;
			}
			this.fetch();
		},
		getShop(){
			this.$store.dispatch('getShops');
			this.getOrders();
		},

		fetch(data = {}, page = 1) {
			//this.data.pagination.page = page;
			//this.$store.dispatch('getOrders');
			this.$store.dispatch('getOrders', data, page);
			this.$nextTick();
		},
		makeRequest(type = 'makeRequest'){
			console.log('makeRequest > selected', this.selectedOrders);
			this.$store.dispatch(type, this.selectedOrders);
		},
		resultQuery(keyword){
            console.log("resultquery > this.$store.state.orders:", this.$store.state.orders.data);

            if(keyword){
                console.log("filter");
                return this.$store.state.orders.data.filter((item)=>{
                    return keyword.toLowerCase().split(' ').every(v => item.display_id.toLowerCase().includes(v))
                    || keyword.toLowerCase().split(' ').every(v => item.neighborhood.toLowerCase().includes(v))
                    || keyword.toLowerCase().split(' ').every(v => item.client_name.toLowerCase().includes(v))
                });
            }else{
                console.log("filter else");
                return this.$store.state.orders.data;
            }
        },
	},
	created() {
		console.log("MIXIIN");
		this.getShop();
	}
}