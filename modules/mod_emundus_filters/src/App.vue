<template>
  <div id="emundus-filters" class="em-w-100">
	  <section id="filters-top-actions" class="em-mb-16">
		  <button id="clear-filters" class="em-secondary-button" @click="clearFilters">{{ translate('MOD_EMUNDUS_FILTERS_CLEAR_FILTERS') }}</button>
		  <button id="save-filters" class="em-secondary-button label label-darkblue em-mt-8 em-mb-8" @click="saveFilters">{{ translate('MOD_EMUNDUS_FILTERS_SAVE_FILTERS') }}</button>
		  <input id="new-filter-name" type="text" class="em-flex-row" v-model="newFilterName" :placeholder="translate('MOD_EMUNDUS_FILTERS_SAVE_FILTER_NAME')">
	  </section>
	  <section id="applied-filters">
			<div v-for="appliedFilter in appliedFilters" :key="appliedFilter.uid">
				<MultiSelect v-if="appliedFilter.type === 'select'" :filter="appliedFilter" :module-id="moduleId" class="em-w-100"></MultiSelect>
				<DateFilter v-else-if="appliedFilter.type === 'date'" :filter="appliedFilter" :module-id="moduleId" class="em-w-100"></DateFilter>
				<TimeFilter v-else-if="appliedFilter.type === 'time'" :filter="appliedFilter" :module-id="moduleId" class="em-w-100"></TimeFilter>
				<DefaultFilter v-else :filter="appliedFilter" :module-id="moduleId" class="em-w-100"></DefaultFilter>
			</div>
	  </section>
	  <div id="filters-selection-wrapper" class="em-w-100 em-mt-16 em-mb-16" :class="{'hidden': !openFilterOptions}">
		  <label for="filters-selection"> {{ translate('MOD_EMUNDUS_FILTERS_SELECT_FILTER_LABEL') }} </label>
			<AdvancedSelect :module-id="moduleId" :filters="filters" @filter-selected="onSelectNewFilter"></AdvancedSelect>
	  </div>
	  <section id="filters-bottom-actions">
		  <button id="add-filter" class="em-secondary-button em-mt-16" @click="openFilterOptions = !openFilterOptions">{{ translate('MOD_EMUNDUS_FILTERS_ADD_FILTER') }}</button>
		  <button id="apply-filters" class="em-primary-button em-mt-16" @click="applyFilters">{{ translate('MOD_EMUNDUS_FILTERS_APPLY_FILTERS') }}</button>
	  </section>
  </div>
</template>

<script>
import MultiSelect from './components/MultiSelectFilter.vue';
import AdvancedSelect from './components/AdvancedSelect.vue';
import DateFilter from './components/DateFilter.vue';
import TimeFilter from './components/TimeFilter.vue';
import DefaultFilter from './components/DefaultFilter.vue';
import filtersService from './services/filters.js';

export default {
  name: 'App',
	components: {DateFilter, AdvancedSelect, MultiSelect, TimeFilter, DefaultFilter},
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
			newFilterName: ''
		}
	},
	mounted() {
		this.appliedFilters = this.defaultAppliedFilters.map((filter) => {
			if (!filter.hasOwnProperty('operator')) {
				filter.operator = '=';
			}
			if (!filter.hasOwnProperty('andorOperator')) {
				filter.andorOperator = 'OR';
			}

			return filter;
		});
	},
	methods: {
		onSelectNewFilter(filterId) {
			let added = false;

			const foundFilter = this.filters.find((filter) => filter.id === filterId);
			if (foundFilter) {
				// JSON stringify and parse to remove binding to the original filter
				let newFilter = JSON.parse(JSON.stringify(foundFilter));

				newFilter.uid = new Date().getTime();
				newFilter.value = newFilter.type === 'select' ? [] : '';
				newFilter.default = false;
				newFilter.operator = newFilter.type === 'select' ? 'IN' : '=';
				newFilter.andorOperator = 'OR';

				this.appliedFilters.push(newFilter);
				this.openFilterOptions = false;
				added = true;
			}

			return added;
		},
		applyFilters() {
			filtersService.applyFilters(this.appliedFilters);
		},
		clearFilters() {
			filtersService.applyFilters([]);
		},
		saveFilters() {
			const saved = filtersService.saveFilters(this.appliedFilters, this.newFilterName, this.moduleId);
			this.newFilterName = '';

			if (saved) {
				this.getRegisteredFilters();
			}
		},
		getRegisteredFilters() {
			filtersService.getRegisteredFilters().then((response) => {
				this.filters = response.data;
			});
		},
	}
}
</script>