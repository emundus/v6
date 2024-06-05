<template>
  <div id="emundus-filters" class="em-w-100">
	  <section id="filters-top-actions" class="em-mb-16">
		  <span id="clear-filters" class="material-icons-outlined em-pointer hidden" @click="clearFilters" :alt="translate('MOD_EMUNDUS_FILTERS_CLEAR_FILTERS')">filter_list_off</span>
			<span id="save-filters" class="material-icons-outlined em-pointer hidden" @click="onClickSaveFilter" :alt="translate('MOD_EMUNDUS_FILTERS_SAVE_FILTERS')">save</span>

		  <div id="global-search-wrapper" style="position: relative;">
			  <div id="global-search-values" ref="globalSearchValues" class="em-border-radius-8 em-border-neutral-400 em-flex-row em-flex-wrap em-white-bg" @click="onEnterGlobalSearchDiv">
				  <div v-if="globalSearch.length > 0" class="em-flex-row em-flex-wrap">
					  <div v-for="value in globalSearch" :key="value.value + '-' + value.scope" class="global-search-tag em-flex-row em-box-shadow em-border-radius-8 em-border-neutral-400 em-w-auto em-mt-4 em-mb-4 em-ml-4 em-mr-4">
						  <span style="white-space: nowrap">{{ translatedScope(value.scope) }} : {{ value.value }}</span>
						  <span class="material-icons-outlined em-pointer" @click="removeGlobalSearchValue(value.value, value.scope)">clear</span>
					  </div>
				  </div>
				  <input id="current-global-search" ref="globalSearchInput" class="em-border-radius-8" v-model="currentGlobalSearch" type="text" @keyup.enter="(e) => {this.onGlobalSearchChange(e, 'everywhere')}" :placeholder="globalSearchPlaceholder">
			  </div>
			  <ul id="select-scopes" class="em-w-100 em-w-100 em-border-radius-8 em-white-bg em-border-neutral-400 em-box-shadow" :class="{'hidden': currentGlobalSearch.length < 1}">
				  <li v-for="option in globalSearchScopes" :key="option.value" @click="(e) => {this.onGlobalSearchChange(e, option.value)}" class="em-pointer global-search-scope">
					  <button>{{ currentGlobalSearch }} {{ translate('MOD_EMUNDUS_FILTERS_SCOPE_IN') }}  {{ translate(option.label) }}</button>
				  </li>
			  </ul>
		  </div>
		  <div id="save-filters-inputs-btns">
			  <div id="save-filter-new-name" class="em-flex-row em-flex-space-between em-border-radius-8 em-white-bg em-box-shadow em-w-100 em-p-16" :class="{'hidden': !openSaveFilter}">
				  <input id="new-filter-name" ref="new-filter-name" type="text" class="em-flex-row" v-model="newFilterName" :placeholder="translate('MOD_EMUNDUS_FILTERS_SAVE_FILTER_NAME')" minlength="2" @keyup.enter="saveFilters" @focusout="onFocusOutNewFilter">
				  <span id="save-new-filter" class="material-icons-outlined em-pointer" :class="{'em-pointer em-dark-blue-500-color': newFilterName.length > 1}" @click="saveFilters">done</span>
			  </div>
			  <div v-if="registeredFilters.length > 0" id="registered-filters-wrapper" class="em-mt-8">
				  <label for="registered-filters">{{ translate('MOD_EMUNDUS_FILTERS_SAVED_FILTERS') }}</label>
				  <div class="em-flex-row em-flex-space-between">
					  <select id="registered-filters" class="em-w-100" v-model="selectedRegisteredFilter" @change="onSelectRegisteredFilter">
						  <option value="0">{{ translate('MOD_EMUNDUS_FILTERS_PLEASE_SELECT') }}</option>
						  <option v-for="registeredFilter in registeredFilters" :key="registeredFilter.id" :value="registeredFilter.id">{{ registeredFilter.name }}</option>
					  </select>
					  <span v-if="selectedRegisteredFilter > 0" class="material-icons-outlined em-red-500-color em-pointer" @click="deleteRegisteredFilter">delete</span>
				  </div>
			  </div>
		  </div>
	  </section>
	  <section id="applied-filters">
			<div v-for="appliedFilter in appliedFilters" :key="appliedFilter.uid">
				<MultiSelect v-if="appliedFilter.type === 'select'" :filter="appliedFilter" :module-id="moduleId" :countFilterValues="countFilterValues" class="em-w-100" @remove-filter="onRemoveFilter(appliedFilter)" @filter-changed="onFilterChanged"></MultiSelect>
				<DateFilter v-else-if="appliedFilter.type === 'date'" :filter="appliedFilter" :module-id="moduleId" class="em-w-100" @remove-filter="onRemoveFilter(appliedFilter)" @filter-changed="onFilterChanged"></DateFilter>
				<TimeFilter v-else-if="appliedFilter.type === 'time'" :filter="appliedFilter" :module-id="moduleId" class="em-w-100" @remove-filter="onRemoveFilter(appliedFilter)"></TimeFilter>
        <DefaultFilter v-else :filter="appliedFilter" :module-id="moduleId" :type="appliedFilter.type" class="em-w-100" @remove-filter="onRemoveFilter(appliedFilter)" @filter-changed="onFilterChanged"></DefaultFilter>			</div>
	  </section>
	  <div id="filters-selection-wrapper" class="em-w-100 em-mt-16 em-mb-16" :class="{'hidden': !openFilterOptions}">
		  <label for="filters-selection"> {{ translate('MOD_EMUNDUS_FILTERS_SELECT_FILTER_LABEL') }} </label>
			<AdvancedSelect :module-id="moduleId" :filters="availableFilters" @filter-selected="onSelectNewFilter"></AdvancedSelect>
	  </div>
	  <section id="filters-bottom-actions">
		  <button id="em-add-filter" class="em-secondary-button em-white-bg em-mt-16" @click="openFilterOptions = !openFilterOptions">{{ translate('MOD_EMUNDUS_FILTERS_ADD_FILTER') }}</button>
		  <button id="em-apply-filters" class="em-primary-button em-mt-16 hidden" @click="applyFilters">{{ translate('MOD_EMUNDUS_FILTERS_APPLY_FILTERS') }}</button>
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

const defaultGlobalSearchScopes = [
	{value: 'everywhere', label: 'MOD_EMUNDUS_FILTERS_SCOPE_ALL'},
	{value: 'eu.firstname', label: 'MOD_EMUNDUS_FILTERS_SCOPE_FIRSTNAME'},
	{value: 'eu.lastname', label: 'MOD_EMUNDUS_FILTERS_SCOPE_LASTNAME'},
	{value: 'u.username', label: 'MOD_EMUNDUS_FILTERS_SCOPE_USERNAME'},
	{value: 'u.email', label: 'MOD_EMUNDUS_FILTERS_SCOPE_EMAIL'},
	{value: 'jecc.applicant_id', label: 'MOD_EMUNDUS_FILTERS_SCOPE_ID'},
	{value: 'jecc.fnum', label: 'MOD_EMUNDUS_FILTERS_SCOPE_FNUM'}
];

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
		defaultQuickSearchFilters: {
			type: Array,
			default: () => []
		},
		defaultFilters: {
			type: Array,
			default: () => []
		},
    countFilterValues: {
      type: Boolean,
      default: false
    },
    menuId: {
      type: Number,
      default: 0
    }
	},
	data() {
		return {
			applySuccessEvent: null,
			startApplyFilters: null,
			appliedFilters: [],
			openFilterOptions: false,
			openSaveFilter: false,
			newFilterName: '',
			registeredFilters: [],
			selectedRegisteredFilter: 0,
			showSaveFilter: false,
			currentGlobalSearch: '',
			globalSearch: [],
			currentGlobalSearchScope: 'everywhere',
			globalSearchScopes: [],
      filters: [],
		}
	},
	mounted() {
		this.applySuccessEvent = new Event('emundus-apply-filters-success');
		this.startApplyFilters = new Event('emundus-start-apply-filters');
    this.filters = this.defaultFilters;

		this.getRegisteredFilters();
		this.selectedRegisteredFilter = sessionStorage.getItem('emundus-current-filter') || 0;
		this.appliedFilters = this.defaultAppliedFilters.map((filter) => {
      if (!filter.operator) {
        filter.operator = '=';
      }
      if (!filter.andorOperator) {
        filter.andorOperator = 'OR';
      }

			return filter;
		});
		this.globalSearch = this.defaultQuickSearchFilters;
		this.mapSearchScopesToAppliedFilters();
		this.addKeyEvents();

    window.addEventListener('refresh-emundus-module-filters', () => {
      this.applyFilters();
    });
  },
	methods: {
		addKeyEvents()
		{
			// add key events on up and down to focus on the next or previous global search scope
			const globalSearchScope = document.getElementById('global-search-wrapper');
			globalSearchScope.addEventListener('keydown', (event) => {
				const currentFocusedScope = globalSearchScope.querySelector('.global-search-scope button:focus');
				const currentFocusedInput = globalSearchScope.querySelector('#current-global-search:focus');

				if (currentFocusedScope || currentFocusedInput) {
					if (event.code === 'ArrowUp') {
						event.preventDefault();

						if (currentFocusedScope) {
							// focus on the previous scope
							const previousScope = currentFocusedScope.parentElement.previousElementSibling;
							if (previousScope) {
								const previousScopeButton = previousScope.querySelector('button');
								previousScopeButton.focus();
							} else {
								// focus on the input
								this.$refs.globalSearchInput.focus();
							}
						} else {
							// focus on the last scope
							const lastScope = globalSearchScope.querySelector('.global-search-scope:last-child button');
							lastScope.focus();
						}
					} else if (event.code === 'ArrowDown') {
						event.preventDefault();
						if (currentFocusedScope) {
							// focus on the next scope
							const nextScope = currentFocusedScope.parentElement.nextElementSibling;
							if (nextScope) {
								const nextScopeButton = nextScope.querySelector('button');
								nextScopeButton.focus();
							} else {
								this.$refs.globalSearchInput.focus();
							}
						} else {
							// focus on the first scope
							const firstScope = globalSearchScope.querySelector('.global-search-scope:first-child button');
							firstScope.focus();
						}
					}
				}
			});
		},
		onSelectNewFilter(filterId) {
			let added = false;

			const foundFilter = this.filters.find((filter) => filter.id === filterId);
			if (foundFilter) {
				// JSON stringify and parse to remove binding to the original filter
				let newFilter = JSON.parse(JSON.stringify(foundFilter));

				newFilter.uid = new Date().getTime();
				newFilter.default = false;
				newFilter.operator = newFilter.hasOwnProperty('operator') && newFilter.operator != '' ? newFilter.operator : '=';
				newFilter.andorOperator = 'OR';

				switch (newFilter.type) {
					case 'select':
						newFilter.value = ['all'];
						newFilter.operator = 'IN';
						break;
					case 'date':
						newFilter.value = ['', ''];
						break;
					default:
						newFilter.value = '';
						break;
				}

				if (newFilter.type === 'select' && newFilter.values.length < 1) {
					filtersService.getFilterValues(newFilter.id).then((values) => {
						newFilter.values = values;

						this.appliedFilters.push(newFilter);
						this.openFilterOptions = false;
						this.applyFilters();

						return true;
					});
				} else {
					this.appliedFilters.push(newFilter);
					this.openFilterOptions = false;
					added = true;
					this.applyFilters();

					return added;
				}
			} else {
				console.error('Filter not found');
				return added;
			}
		},
		applyFilters() {
			window.dispatchEvent(this.startApplyFilters);
			filtersService.applyFilters(this.appliedFilters, this.globalSearch, this.applySuccessEvent).then((applied) => {
        if (applied && this.countFilterValues) {
          filtersService.countFiltersValues(this.moduleId, this.menuId).then((response) => {
            if (response.status) {
              this.appliedFilters = response.data;
            }
          });
        }

        filtersService.getFiltersAvailable(this.moduleId).then((filters) => {
          this.filters = filters;
        }).catch((error) => {
          console.error(error);
        });
      });
		},
		clearFilters() {
			sessionStorage.removeItem('emundus-current-filter');
			this.globalSearch = [];
			// reset applied filters values
			this.appliedFilters = this.appliedFilters.map((filter) => {
				filter.operator = '=';

        if (filter.type === 'select') {
          filter.operator = 'IN';

          if (filter.defaultValue) {
            filter.value = filter.defaultValue;
          } else {
            filter.value = [];
          }
				} else if (filter.type === 'date' || filter.type === 'time') {
					filter.value = ['', ''];
				} else {
					filter.value = '';
				}

				return filter;
			});
			this.applyFilters();
		},
		onFocusOutNewFilter(e) {
			this.saveFilters(e);
			this.openSaveFilter = false;
		},
		saveFilters(e) {
			if (e) {
				e.preventDefault();
				e.stopPropagation();
			}
			let saved = false;

			if (this.newFilterName.length > 0) {
				filtersService.saveFilters(this.appliedFilters, this.newFilterName, this.moduleId).then((saved) => {
					if (saved) {
						this.getRegisteredFilters();
					}
				});
				this.openSaveFilter = false;
				this.newFilterName = '';
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
		deleteRegisteredFilter() {
			// delete  selectedRegisteredFilter from registeredFilters
			const filterIdToDelete = this.selectedRegisteredFilter;
			filtersService.deleteFilter(filterIdToDelete);

			this.registeredFilters = this.registeredFilters.filter((filter) => filter.id !== filterIdToDelete);
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
				this.openSaveFilter = !this.openSaveFilter;

				if (this.openSaveFilter) {
					// focus on the input new-filter-name
					this.$nextTick(() => {
						this.$refs['new-filter-name'].focus();
					});
				}
			}
		},
		onRemoveFilter(filter) {
			this.appliedFilters = this.appliedFilters.filter((appliedFilter) => appliedFilter.uid !== filter.uid);
			this.applyFilters();
		},
		onFilterChanged() {
			this.applyFilters();
		},
		onGlobalSearchChange(event, scope = 'everywhere') {
			event.stopPropagation();
			event.preventDefault();

			if (this.currentGlobalSearch.length > 0) {
				// if the current search is already in the list, no need to add it again
				const foundSearch = this.globalSearch.find((search) => search.value === this.currentGlobalSearch && search.scope === scope);

				if (!foundSearch) {
					this.globalSearch.push({value: this.currentGlobalSearch, scope: scope});
					this.applyFilters();
				}
			}

			this.currentGlobalSearch = '';
			// scroll to top of the div
			this.$refs.globalSearchValues.scrollTop = 0;
		},
		removeGlobalSearchValue(value, scope) {
			this.globalSearch = this.globalSearch.filter((search) => {
				return search.value !== value || search.scope !== scope;
			});

			// scroll to top of the div #global-search-values
			// remove focus from the input #global-search-input
			document.activeElement.blur();
			this.$refs.globalSearchValues.scrollTop = 0;
			this.applyFilters();
		},
		onEnterGlobalSearchDiv() {
			this.$refs.globalSearchValues.scrollTop = this.$refs.globalSearchValues.scrollHeight;
			this.$refs.globalSearchInput.focus();
		},
		translatedScope(scope) {
			const foundScope = this.globalSearchScopes.find((s) => s.value === scope);

			return foundScope ? this.translate(foundScope.label) : scope;
		},
		mapSearchScopesToAppliedFilters() {
			this.globalSearchScopes = [];
			this.globalSearchScopes = defaultGlobalSearchScopes;

			/*
			TODO: hard to give an open search on label for all filters elements
			this.appliedFilters.forEach((filter) => {
				const foundScope = this.globalSearchScopes.find((s) => s.value === filter.id);

				if (!foundScope) {
					this.globalSearchScopes.push({
						value: filter.id,
						label: filter.label
					});
				}
			});*/
		}
	},
	computed: {
		availableFilters() {
			return this.filters.filter((filter) => {
				return filter.available;
			});
		},
		globalSearchPlaceholder() {
			return this.globalSearch.length < 1 ? this.translate('MOD_EMUNDUS_FILTERS_GLOBAL_SEARCH_PLACEHOLDER') : '';
		}
	}
}
</script>

<style>
#emundus-filters {
	position: relative;
}

#emundus-filters .recap-label {
	display: -webkit-box !important;
	-webkit-line-clamp: 3;
	-webkit-box-orient: vertical;
	overflow: hidden;
	text-overflow: ellipsis;
}

#select-scopes:not(.hidden) {
	position: absolute;
	top: 42px;
	z-index:2;
	list-style-type: none;
	margin: 0;
	padding: 8px;
}

#select-scopes li {
	padding: 8px;
}

#global-search-values {
	height: 42px;
	overflow-y: auto;
}

.global-search-scope button {
	white-space: break-spaces;
	text-align: left;
}

#current-global-search {
	border: none;
	border-radius: 0;
	box-shadow: none;
	outline: 0;
}

.global-search-tag {
	padding: 4px;
}

#save-filter-new-name {
	position: absolute;
	top: 0;
}
</style>