<template>
<div id="list-body">
	<div id="actions">
		<div class="filters">

		</div>
		<div class="view-type">
			<span 
				v-for="(viewType, index) in viewTypeOptions" 
				:key="index" 
				class="material-icons"
				@click="changeViewType(viewType)"
			>
				{{ viewType.icon }}
			</span>
		</div>
	</div>
	<list-table v-if="viewType === 'table'"></list-table>
	<list-blocs v-if="viewType === 'blocs'"></list-blocs>
</div>
</template>

<script>
import ListTable from './Table/ListTable.vue';
import ListBlocs from './Bloc/ListBlocs.vue';

export default {
	components: {
		ListTable,
		ListBlocs
	},
	data() {
		return {
			viewType: 'table',
			viewTypeOptions: [
				{
					value: 'table',
					icon: 'dehaze'
				},
				{
					value: 'blocs',
					icon: 'grid_view'
				}
			]
		};
	},
	methods: {
		changeViewType(viewType) {
			this.viewType = viewType.value;
		}
	}
}
</script>

<style lang="scss" scoped>

</style>