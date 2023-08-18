<template>
	<div class="advanced-search em-w-100">
		<input type="text" v-model="search" class="em-w-100">
		<select class="em-w-100 em-mt-8" @change="onChange" v-model="selected">
			<option value="-1" selected> {{ translate('MOD_EMUNDUS_FILTERS_PLEASE_SELECT') }}</option>
			<optgroup :label="group.label" v-for="group in groupedFilters" :key="group.id">
				<option v-for="option in group.options" :key="option.id" :value="option.id"> {{ option.label }}</option>
			</optgroup>
		</select>
	</div>
</template>

<script>
export default {
	name: 'AdvancedSelect.vue',
	props: {
		moduleId: {
			type: Number,
			required: true
		},
		filters: {
			type: Array,
			required: true
		},
	},
	data() {
		return {
			groupedOptions: [],
			search: '',
			selected: -1
		};
	},
	methods: {
		onChange() {
			if (this.selected !== -1) {
				this.$emit('filter-selected', this.selected);
				this.selected = -1;
			}
		}
	},
	computed: {
		groupedFilters() {
			const groups = [];
			const alreadyAdded = [];

			this.displayedFilters.forEach((filter) => {
				if (!alreadyAdded.includes(filter.group_id)) {
					groups.push({
						id: filter.group_id,
						label: filter.group_label,
						options: []
					});
					alreadyAdded.push(filter.group_id);
				}

				const currentFilterGroup = groups.find((group) => group.id === filter.group_id);
				currentFilterGroup.options.push(filter);
			});

			return groups;
		},
		displayedFilters() {
			return this.filters.filter((filter) => {
				return filter.label.toLowerCase().includes(this.search.toLowerCase()) || filter.group_label.toLowerCase().includes(this.search.toLowerCase());
			});
		}
	}
}
</script>

<style scoped>

</style>