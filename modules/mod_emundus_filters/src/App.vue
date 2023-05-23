<template>
  <div id="emundus-filters" class="em-w-100">
	  <section id="applied-filters">
			<div v-for="appliedFilter in appliedFilters" :key="appliedFilter.uid">
				<MultiSelect v-if="appliedFilter.type === 'select'" :filter="appliedFilter" :module-id="moduleId" class="em-w-100"></MultiSelect>
			</div>
	  </section>
	  <div id="filters-selection-wrapper" class="em-w-100 em-mt-16 em-mb-16" :class="{'hidden': !openFilterOptions}">
		  <label for="filters-selection"> {{ translate('MOD_EMUNDUS_FILTERS_SELECT_FILTER_LABEL') }} </label>
		  <select id="filters-selection" name="filters-selection" class="em-w-100">
			  <option value="0">{{ translate('MOD_EMUNDUS_FILTERS_SELECT_FILTER') }} </option>
			  <option v-for="filter in filters" :key="filter.id" :v-model="filter.id" >{{ filter.label }}</option>
		  </select>
	  </div>
	  <section id="filters-actions">
		  <button id="add-filter" class="em-secondary-button em-mt-16" @click="openFilterOptions = !openFilterOptions">{{ translate('MOD_EMUNDUS_FILTERS_ADD_FILTER') }}</button>
	  </section>
  </div>
</template>

<script>
import MultiSelect from './components/MultiSelectFilter.vue';


export default {
  name: 'App',
	components: {MultiSelect},
	props: {
		moduleId: {
			type: Number,
			required: true
		},
		appliedFilters: {
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
			openFilterOptions: false,
		}
	},
	mounted() {
	},
	methods: {
		addFilter() {
			console.log('add filter');
		}
	}
}
</script>