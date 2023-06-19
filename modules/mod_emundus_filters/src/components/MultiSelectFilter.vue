<template>
	<div class="multi-select-filter em-w-100 em-mb-8 em-border-radius-8 em-border-neutral-400 em-box-shadow em-white-bg em-p-8">
		<div class="em-flex-row em-flex-space-between">
			<p class="recap-label">{{ filter.label }}</p>
			<div>
				<span @mouseenter="resetHover = true" @mouseleave="resetHover = false" class="material-icons-outlined em-pointer reset-filter-btn" :class="{'em-blue-400-color': resetHover}" @click="resetFilter" :alt="translate('MOD_EMUNDUS_FILTERS_RESET')">refresh</span>
				<span v-if="!filter.default" class="material-icons-outlined em-red-500-color em-pointer" @click="$.emit('remove-filter')">close</span>
				<span v-if="opened === false" class="material-icons-outlined em-pointer" @click="opened = !opened">keyboard_arrow_down</span>
				<span v-else class="material-icons-outlined em-pointer" @click="opened = !opened">keyboard_arrow_up</span>
			</div>
		</div>
		<section v-if="!opened" class="recap em-mt-8">
			<div v-if="filter.value && filter.value.length > 0 && !filter.value.includes('all')" class="em-flex-column-start">
				<span class="recap-operator label label-darkblue"> {{ selectedOperatorLabel }}</span>
				<div class="recap-value em-flex-row em-flex-wrap em-mt-8">
					<div v-for="(value, index) in filter.value.slice(0, 2)" :key="value">
						<span>{{ selectedValuesLabels[index] }}</span>
						<span v-if="filter.value.length > 1 && index < filter.value.length - 1" class="em-ml-4 em-mr-4"> {{ selectedAndorOperatorLabel }} </span>
					</div>
					<div v-if="filter.value.length > 2">
						<span> + {{ (filter.value.length-2) }} {{ translate('MOD_EMUNDUS_FILTERS_MORE_VALUES') }}</span>
					</div>
				</div>
			</div>
			<p v-else> {{ translate('ALL') }}</p>
		</section>
		<section v-else class="multi-select-filter-options em-mt-8">
			<div class="operators-selection em-flex-row em-flex-wrap em-flex-gap-8">
				<div v-for="operator in operators" :key="filter.uid + '-' +operator.value" class="em-p-8 em-border-radius-8" :class="{'label-default': operator.value !== filter.operator, 'label-darkblue': operator.value === filter.operator}">
					<input class="hidden label"
					       type="radio"
					       :id="filter.uid + '-operator-' + operator.value" :value="operator.value"
					       v-model="filter.operator"
					>
					<label :for="filter.uid + '-operator-' + operator.value" style="margin: 0">{{ operator.label }}</label>
				</div>
			</div>
			<hr/>
			<div class="andor-selection em-flex-row em-flex-gap-8">
				<div v-for="andor in andorOperators" :key="filter.uid + '-' + andor.value" class="em-p-8 em-border-radius-8" :class="{'label-default': andor.value !== filter.andorOperator, 'label-darkblue': andor.value === filter.andorOperator}">
						<input class="hidden label"
						       type="radio"
						       :id="filter.uid + '-andor-' + andor.value"
						       :value="andor.value"
						       v-model="filter.andorOperator"
						>
						<label :for="filter.uid + '-andor-' + andor.value" style="margin: 0">{{ andor.label }}</label>
				</div>
			</div>
			<hr/>
			<input class="em-w-100 em-p-8 em-border-radius-8 em-border-neutral-400 em-box-shadow em-white-bg"
			       :id="filter.uid + '-filter-search'"
			       type="text"
			       :placeholder="translate('MOD_EMUNDUS_FILTERS_FILTER_SEARCH')"
			       v-model="search"
			       @keyup="onSearchChange"
			>
			<div class="values-selection em-mt-8 em-mb-8">
				<div class="em-flex-row">
					<input :name="filter.uid + '-filter-value'" :id="filter.uid + '-filter-value-all'" type="checkbox" value="all" v-model="filter.value" @click="onClickAll">
					<label :for="filter.uid + '-filter-value-all'" style="margin: 0">{{ translate('ALL') }}</label>
				</div>
				<div v-for="value in searchedValues" :key="value.value" class="em-flex-row" @click="onClickSpecificValue(value.value)">
					<input :name="filter.uid + '-filter-value'" :id="filter.uid + '-filter-value-'+ value.value" type="checkbox" :value="value.value" v-model="filter.value">
					<label :for="filter.uid + '-filter-value-'+ value.value" style="margin: 0">{{ value.label }}</label>
				</div>
			</div>
		</section>
	</div>
</template>

<script>
export default {
	name: 'MultiSelect.vue',
	props: {
		moduleId: {
			type: Number,
			required: true
		},
		filter: {
			type: Object,
			required: true
		},
	},
	data() {
		return {
			opened: false,
			operators: [
				{ value: 'IN', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS')},
				{ value: 'NOT IN', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_NOT')}
			],
			andorOperators: [
				{ value: 'OR', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_OR')}
			],
			search: '',
			resetHover: false
		}
	},
	mounted () {
		this.filter.operator = this.filter.operator === '=' ? 'IN' : this.filter.operator;
	},
	methods: {
		onClickSpecificValue(newValue) {
			// If all is selected, remove 'all' from selected values
			if (this.filter.value.includes('all')) {
				 this.filter.value =  this.filter.value.filter((value) => { return value !== 'all' });

				 // check that newvalue exists
				const exists = this.filter.values.find((value) => { return value.value === newValue });
				if (exists && !this.filter.value.includes(newValue)) {
					 this.filter.value.push(newValue);
				}
			}
		},
		onClickAll() {
			if (this.filter.value.includes('all')) {
				this.filter.value = this.filter.value.filter((value) => {
					return !this.searchedValues.find((searchedValue) => { return searchedValue.value === value });
				});
				this.filter.value =  this.filter.value.filter((value) => { return value !== 'all' });
			} else {
				let allValues = this.searchedValues.map((value) => { return value.value });
				allValues.push('all');
				allValues.forEach((value) => {
					if (!this.filter.value.includes(value)) {
						 this.filter.value.push(value);
					}
				});
			}
		},
		allValuesAreSelected() {
			return this.filter.values.every((value) => {
				return this.filter.value.includes(value.value);
			});
		},
		onSearchChange() {
			if(!this.allValuesAreSelected()) {
				// If all is not selected, remove 'all' from selected values
				this.filter.value =  this.filter.value.filter((value) => { return value !== 'all'});
			}
		},
		resetFilter() {
			this.filter.operator = 'IN';
			this.filter.andorOperator = 'OR';
			this.search = '';
			this.filter.value = [];
		}
	},
	computed: {
		selectedOperatorLabel() {
			const selectedOperator =  this.operators.find((operator) => { return operator.value === this.filter.operator });
			return selectedOperator ? selectedOperator.label : '';
		},
		selectedAndorOperatorLabel() {
			const selectedAndorOperator = this.andorOperators.find((andor) => { return andor.value === this.filter.andorOperator });
			return selectedAndorOperator ? selectedAndorOperator.label : '';
		},
		selectedValuesLabels() {
			let labels = [];

			 this.filter.value.forEach((value) => {
				const selectedValue = this.filter.values.find((filterValue) => { return filterValue.value === value })

				if (selectedValue) {
					labels.push(selectedValue.label);
				}
			});

			return labels;
		},
		searchedValues() {
			return this.filter.values.filter((value) => {
				return value.label.toLowerCase().includes(this.search.toLowerCase());
			});
		}
	}
}
</script>

<style scoped>
.values-selection {
	max-height: 180px;
	overflow-y: auto;
}
</style>