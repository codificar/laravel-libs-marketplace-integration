<template>
	<v-app class="margin-map">
		<v-main>
			<v-container fluid>
				<router-view></router-view>
				<v-speed-dial
					bottom
					right
					fixed
				>
					<template v-slot:activator>
						<v-btn
							v-if="$route.name != 'credentials'"
							color="success darken-2"
							dark
							fab
						>
							<v-icon>
								mdi-menu
							</v-icon>
						</v-btn>
					</template>
					<v-btn
						v-model="fab"
						v-if="$route.name != 'credentials'"
						color="success darken-2"
						dark
						fab
						link
						:to="'/corp/marketplace/settings'"
					>
						<v-icon>
							mdi-cart-variant
						</v-icon>
					</v-btn>
					<v-btn
						v-if="$route.name != 'credentials' && $route.name != 'list' "
						color="primary darken-2"
						dark
						fab
						link				
						:to="'/corp/marketplace/integration/list'"
					>
						<v-icon>
							mdi-view-list
						</v-icon>
						
					</v-btn>
					<v-btn
						v-if="$route.name != 'credentials' && $route.name != 'map' "
						color="primary darken-2"
						dark
						fab
						link				
						:to="'/corp/marketplace/integration/map'"
					>
						<v-icon>
							mdi-map
						</v-icon>
					</v-btn>
				</v-speed-dial>
			</v-container>
		</v-main>
	</v-app>
</template>

<script>
import ModalComponent from '../components/Modal';
export default {
	components: {
		ModalComponent
	},
	data() {
		return {
			drawer: true,
			fab: true,
			mapMode: this.$route.query.mapMode,
		}
	},
	mounted() {
		console.log('route', this.$route.name);
	}
}
</script>

<style>
.margin-map {
	margin-top: -60px;
}
</style>