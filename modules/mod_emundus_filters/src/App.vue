<template>
  <div id="emundus-filters" class="em-w-100">
	  <section id="applied-filters">
			<div v-for="appliedFilter in appliedFilters" :key="appliedFilter.uid">
				<MultiSelect v-if="appliedFilter.type === 'select'" :filter="appliedFilter" :module-id="moduleId" class="em-w-100"></MultiSelect>
			</div>
	  </section>
	  <div id="filters-selection-wrapper" class="em-w-100 em-mt-16 em-mb-16" :class="{'hidden': !openFilterOptions}">
		  <label for="filters-selection"> {{ translate('MOD_EMUNDUS_FILTERS_SELECT_FILTER_LABEL') }} </label>
			<AdvancedSelect :module-id="moduleId" :filters="filters" @filter-selected="onSelectNewFilter"></AdvancedSelect>
	  </div>
	  <section id="filters-actions">
		  <button id="add-filter" class="em-secondary-button em-mt-16" @click="openFilterOptions = !openFilterOptions">{{ translate('MOD_EMUNDUS_FILTERS_ADD_FILTER') }}</button>
	  </section>
  </div>
</template>

<script>
import MultiSelect from './components/MultiSelectFilter.vue';
import AdvancedSelect from './components/AdvancedSelect.vue';

export default {
  name: 'App',
	components: {AdvancedSelect, MultiSelect},
	props: {
		moduleId: {
			type: Number,
			required: true
		},
		defaultAppliedFilters: {
			type: Array,
			default: () => []
		},
		filters: {
			type: Array,
			default: () => []
		},
	},
	data() {
		return {
			appliedFilters: [],
			openFilterOptions: false,
		}
	},
	mounted() {
		this.appliedFilters = this.defaultAppliedFilters;
	},
	methods: {
		addFilter() {
		},
		onSelectNewFilter(filterId) {
			const newFilter = this.filters.find((filter) => filter.id === filterId);
			newFilter.uid = new Date().getTime();
			newFilter.value = [];

			this.appliedFilters.push(newFilter);
			this.openFilterOptions = false;
		},
	}
}
</script>