export default {
    data() {
        return {
            searchQuery: '',
            data: this.$store.filterOrders,
        };
    },
    methods: {
        getOrders() {
            if (this.$store.state.orders) {
                this.loading = !this.loading;
            }
            this.fetch();
        },
        getShop() {
            this.$store.dispatch('getShops');
        },
        fetch(page = 1) {
            console.log('fetchdata > data ', this.$store.state.filterOrders);
            this.$store.dispatch('getOrders', page);
        },
        makeRequest(type = 'makeRequest') {
            console.log('makeRequest > selected', this.selectedOrders);
            this.$store.dispatch(type, this.selectedOrders);
        },
        resultQuery() {
            let keyword = this.$store.state.filterOrders.searchQuery;
            if (keyword) {
                console.log('filter');
                return this.$store.state.orders.data.filter((item) => {
                    return (
                        keyword
                            .toLowerCase()
                            .split(' ')
                            .every((v) =>
                                item.display_id.toLowerCase().includes(v)
                            ) ||
                        keyword
                            .toLowerCase()
                            .split(' ')
                            .every((v) =>
                                item.neighborhood.toLowerCase().includes(v)
                            ) ||
                        keyword
                            .toLowerCase()
                            .split(' ')
                            .every((v) =>
                                item.client_name.toLowerCase().includes(v)
                            )
                    );
                });
            } else {
                console.log('filter else');
                return this.$store.state.orders.data;
            }
        },
    },

    watch: {
        '$store.state.filterOrders': {
            handler: function(newVal, oldVal) {
                if (newVal != oldVal) this.fetch();
            },
            deep: true,
        },
        '$store.state.filterOrders.range': {
            handler: function(newVal, oldVal) {
                if (newVal != oldVal) this.fetch();
            },
            deep: true,
        },
    },
};
