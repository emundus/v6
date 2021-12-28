<template>
<div id="list-body">
	<div id="actions">
		<div class="filters">

		</div>
		<div class="view-type">
			<span 
				v-for="viewTypeOption in viewTypeOptions" 
				:key="viewTypeOption.value" 
				class="material-icons"
				:class="{'active': viewTypeOption.value === viewType}"
				@click="changeViewType(viewTypeOption)"
			>
				{{ viewTypeOption.icon }}
			</span>
		</div>
	</div>
	<list-table 
		v-if="viewType === 'table'" 
		:type="type" 
		:actions="actions"
		@validateFilters="validateFilters"
		@updateLoading="updateLoading"
	></list-table>
	<list-blocs 
		v-if="viewType === 'blocs'" 
		:type="type" 
		:actions="actions"
		@validateFilters="validateFilters"
		@updateLoading="updateLoading"
	>
	</list-blocs>
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
	props: {
		type: {
			type: String,
			required: true
		},
		actions: {
			type: Object,
			required: true
		},
	},
	data() {
		return {
			viewType: 'blocs',
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
		},
		validateFilters() {
			this.$emit('validateFilters');
		},
		updateLoading(value) {
			this.$emit('updateLoading',value);
		}
	}
}
</script>

<style lang="scss" scoped>
#list-body {
	#actions {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 20px;
	
		.view-type {
			display: flex;
			align-items: center;
			justify-content: center;
			margin-right: 20px;
			
			span {
				margin-left: 10px;
    		padding: 4px 4px 3px 4px;
				color: #E3E5E8;
    		border: 1px solid #E3E5E8;
    		border-radius: 4px;
				cursor: pointer;
				transition: all 0.3s;


				&.active {
					color: #298721;
					border-color: #298721;
				}
			}
		}
	}
}
</style>