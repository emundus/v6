<template>
  <div id="emundus-filters" class="em-w-100">
	  <section id="filters-top-actions" class="em-mb-16">
		  <span id="clear-filters" class="material-icons-outlined em-pointer" @click="clearFilters" :alt="translate('MOD_EMUNDUS_FILTERS_CLEAR_FILTERS')">filter_list_off</span>
		  <button id="save-filters" class="em-secondary-button label label-darkblue em-mt-8 em-mb-8" @click="onClickSaveFilter">{{ translate('MOD_EMUNDUS_FILTERS_SAVE_FILTERS') }}</button>
		  <div class="em-flex-row em-flex-space-between" :class="{'hidden': !openSaveFilter}">
			  <input id="new-filter-name" type="text" class="em-flex-row" v-model="newFilterName" :placeholder="translate('MOD_EMUNDUS_FILTERS_SAVE_FILTER_NAME')">
			  <span class="material-icons-outlined" :class="{'em-pointer em-dark-blue-500-color': newFilterName.length > 0}" @click="saveFilters">save</span>
		  </div>
		  <div id="registered-filters-wrapper" class="em-mt-8">
			  <label for="registered-filters">{{ translate('MOD_EMUNDUS_FILTERS_SAVED_FILTERS') }}</label>
			  <div class="em-flex-row em-flex-space-between">
				  <select id="registered-filters" class="em-w-100" v-model="selectedRegisteredFilter" @change="onSelectRegisteredFilter">
						<option value="0">{{ translate('MOD_EMUNDUS_FILTERS_PLEASE_SELECT') }}</option>
					  <option v-for="registeredFilter in registeredFilters" :key="registeredFilter.id" :value="registeredFilter.id">{{ registeredFilter.name }}</option>
				  </select>
				  <span v-if="selectedRegisteredFilter > 0" class="material-icons-outlined em-red-500-color em-pointer" @click="onUnselectRegisteredFilter">delete</span>
			  </div>
		  </div>
	  </section>
	  <section id="applied-filters">
			<div v-for="appliedFilter in appliedFilters" :key="appliedFilter.uid">
				<MultiSelect v-if="appliedFilter.type === 'select'" :filter="appliedFilter" :module-id="moduleId" class="em-w-100" @remove-filter="onRemoveFilter(appliedFilter)"></MultiSelect>
				<DateFilter v-else-if="appliedFilter.type === 'date'" :filter="appliedFilter" :module-id="moduleId" class="em-w-100" @remove-filter="onRemoveFilter(appliedFilter)"></DateFilter>
				<TimeFilter v-else-if="appliedFilter.type === 'time'" :filter="appliedFilter" :module-id="moduleId" class="em-w-100" @remove-filter="onRemoveFilter(appliedFilter)"></TimeFilter>
				<DefaultFilter v-else :filter="appliedFilter" :module-id="moduleId" class="em-w-100" @remove-filter="onRemoveFilter(appliedFilter)"></DefaultFilter>
			</div>
	  </section>
	  <div id="filters-selection-wrapper" class="em-w-100 em-mt-16 em-mb-16" :class="{'hidden': !openFilterOptions}">
		  <label for="filters-selection"> {{ translate('MOD_EMUNDUS_FILTERS_SELECT_FILTER_LABEL') }} </label>
			<AdvancedSelect :module-id="moduleId" :filters="filters" @filter-selected="onSelectNewFilter"></AdvancedSelect>
	  </div>
	  <section id="filters-bottom-actions">
		  <button id="em-add-filter" class="em-secondary-button em-white-bg em-mt-16" @click="openFilterOptions = !openFilterOptions">{{ translate('MOD_EMUNDUS_FILTERS_ADD_FILTER') }}</button>
		  <button id="em-apply-filters" class="em-primary-button em-mt-16" @click="applyFilters">{{ translate('MOD_EMUNDUS_FILTERS_APPLY_FILTERS') }}</button>
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
			openSaveFilter: false,
			newFilterName: '',
			registeredFilters: [],
			selectedRegisteredFilter: 0
		}
	},
	mounted() {
		this.getRegisteredFilters();
		this.selectedRegisteredFilter = sessionStorage.getItem('emundus-current-filter') || 0;
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
			sessionStorage.removeItem('emundus-current-filter');
			filtersService.applyFilters([]);
		},
		saveFilters() {
			let saved = false;

			if (this.newFilterName.length > 0) {
				saved = filtersService.saveFilters(this.appliedFilters, this.newFilterName, this.moduleId);
				this.openSaveFilter = false;
				this.newFilterName = '';

				if (saved) {
					this.getRegisteredFilters();
				}
			}

			return saved;
		},
		updateFilter(filterId) {
			let updated = false;

			if (filterId > 0) {
				updated = filtersService.updateFilter(this.appliedFilters, this.moduleId, filterId);

				if (updated) {
					this.getRegisteredFilters();
				}
			}
			return updated;
		},
		getRegisteredFilters() {
			filtersService.getRegisteredFilters(this.moduleId).then((filters) => {
				this.registeredFilters = filters;
			});
		},
		onSelectRegisteredFilter() {
			if (this.selectedRegisteredFilter > 0) {
				const foundFilter = this.registeredFilters.find((filter) => filter.id === this.selectedRegisteredFilter);

				if (foundFilter) {
					sessionStorage.setItem('emundus-current-filter', foundFilter.id);
					this.appliedFilters = JSON.parse(foundFilter.constraints);
					this.applyFilters();
				}
			} else {
				sessionStorage.removeItem('emundus-current-filter');
			}
		},
		onUnselectRegisteredFilter() {
			this.selectedRegisteredFilter = 0;
			this.onSelectRegisteredFilter();
		},
		onClickSaveFilter() {
			if (this.selectedRegisteredFilter > 0) {
				const foundFilter = this.registeredFilters.find((filter) => filter.id === this.selectedRegisteredFilter);

				if (foundFilter) {
					this.updateFilter(foundFilter.id);
				}
			} else {
				this.openSaveFilter = true;
			}
		},
		onRemoveFilter(filter) {
			this.appliedFilters = this.appliedFilters.filter((appliedFilter) => appliedFilter.uid !== filter.uid);
		}
	}
}
</script>