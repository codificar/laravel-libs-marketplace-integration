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
						@click="fab = !fab"
						:to="fab ? '/corp/marketplace/settings' : '/corp/marketplace/integration'"
					>
						<v-icon>
							mdi-cart-variant
						</v-icon>
					</v-btn>
					<v-btn
						v-model="mapMode"
						v-if="$route.name != 'credentials'"
						color="primary darken-2"
						dark
						fab
						link
						@click="mapMode = !mapMode"
						:to="mapMode ? '/corp/marketplace/integration': '/corp/marketplace/integration?mapMode=1'"
					>
						<v-icon v-if="mapMode">
							mdi-view-list
						</v-icon>
						<v-icon v-else>
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
}
</script>

<style>
.margin-map {
	margin-top: -60px;
}
</style>