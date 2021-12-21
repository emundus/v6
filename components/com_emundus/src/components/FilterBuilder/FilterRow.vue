<template>
	<div class="filter-row" :class="{ 'filter-delete': hoverDelete }">
		<select name="filter-name" v-model="selectedFilter">
			<option v-for="name in names" :key="name.id" :value="name.id">
				{{ translate(name.label) }}
			</option>
		</select>

		<select name="filter-action" v-model="selectedAction">
			<option v-for="(action, key) in actions" :key="key" :value="key">
				{{ action }}
			</option>
		</select>

		<select v-if="type == 'select'" name="filter-value" v-model="selectedValue">
			<option v-for="(value, key) in values" :key="key" :value="key">
				{{ value }}
			</option>
		</select>
		<input
			v-else-if="type == 'text'"
			type="text"
			name="filter-value"
			v-model="selectedValue"
		/>
		<input
			v-else-if="type == 'number'"
			type="number"
			name="filter-value"
			v-model="selectedValue"
		/>
		<span
			class="material-icons delete"
			@click="removeFilter"
			@mouseover="hoverDelete = true"
			@mouseleave="hoverDelete = false"
		>
			remove
		</span>
	</div>
</template>

<script>
export default {
	props: {
		id: {
			type: Number,
			required: true,
		},
		group: {
			type: Number,
			default: 0,
		},
	},
	data() {
		return {
			selectedFilter: null,
			selectedAction: null,
			selectedValue: null,
			hoverDelete: false,
			names: [],
		};
	},
	mounted() {
		this.names = this.$store.state.filterBuilder.filters.map(
			(filter, index) => {
				if (index == 0) {
					this.selectedFilter = filter.id;
					this.selectedAction = Object.keys(filter.actions)[0];
					this.selectedValue = Object.keys(filter.values)[0];
				}
				return {
					id: filter.id,
					label: filter.label,
				};
			}
		);

		if (
			this.$store.state.filterBuilder.queryFilters.groups[this.group] &&
			this.$store.state.filterBuilder.queryFilters.groups[this.group].filters &&
			this.$store.state.filterBuilder.queryFilters.groups[this.group].filters[
				this.id
			]
		) {
			this.selectedFilter =
				this.$store.state.filterBuilder.queryFilters.groups[this.group].filters[
					this.id
				].id;
			this.selectedAction =
				this.$store.state.filterBuilder.queryFilters.groups[this.group].filters[
					this.id
				].action;
			this.selectedValue =
				this.$store.state.filterBuilder.queryFilters.groups[this.group].filters[
					this.id
				].value;
		} else {
			this.updateFilter();
		}
	},
	methods: {
		updateFilter() {
			// update the actions and the values based on name selected
			this.$store.dispatch("filterBuilder/updateQueryFilters", {
				group: this.group,
				id: this.id,
				filter: {
					id: this.selectedFilter,
					action: this.selectedAction,
					value: this.selectedValue,
				},
			});
		},
		removeFilter() {
			this.$store.dispatch("filterBuilder/removeQueryFilter", {
				group: this.group,
				id: this.id,
			});

			this.$emit("removeFilter", this.id);
		},
	},
	computed: {
		actions() {
			const currentFilter = this.$store.state.filterBuilder.filters.find(
				(filter) => filter.id == this.selectedFilter
			);
			return currentFilter ? currentFilter.actions : [];
		},
		values() {
			const currentFilter = this.$store.state.filterBuilder.filters.find(
				(filter) => filter.id == this.selectedFilter
			);
			return currentFilter ? currentFilter.values : [];
		},
		type() {
			const currentFilter = this.$store.state.filterBuilder.filters.find(
				(filter) => filter.id == this.selectedFilter
			);
			return currentFilter ? currentFilter.type : null;
		},
	},
	watch: {
		selectedFilter() {
			this.updateFilter();
		},
		selectedAction() {
			this.updateFilter();
		},
		selectedValue() {
			this.updateFilter();
		},
	},
};
</script>

<style lang="scss" scoped>
.filter-row {
	display: flex;
	flex-direction: row;
	align-items: center;
	justify-content: space-between;
	margin: 8px 0;

	&.filter-delete {
		select,
		input {
			border: 1px solid var(--error-color);
		}
	}

	select,
	input {
		transition: all 0.3s;
		height: 32px;
		margin: 0 16px 0 0;
	}

	.delete {
		cursor: pointer;
		color: var(--error-color);
		margin-right: 4px;
		padding: 0 !important;
	}
}
</style>