<template>
	<div class="multi-select-filter em-w-100 em-mb-8 em-border-radius-8 em-border-neutral-400 em-box-shadow em-white-bg em-p-8">
		<div class="em-flex-row em-flex-space-between">
			<p class="recap-label">{{ filter.label }}</p>
			<span v-if="opened === false" class="material-icons-outlined em-pointer" @click="opened = !opened">keyboard_arrow_down</span>
			<span v-else class="material-icons-outlined em-pointer" @click="opened = !opened">keyboard_arrow_up</span>
		</div>
		<section v-if="!opened" class="recap">
			<div v-if="selectedValues.length > 0 && !selectedValues.includes('all')">
				<p class="recap-operator"> {{ selectedOperatorLabel }}</p>
				<div class="recap-value em-flex-row em-flex-wrap">
					<div v-for="(value, index) in selectedValues" :key="value">
						<span>{{ selectedValuesLabels[index] }}</span>
						<span v-if="selectedValues.length > 1 && index < selectedValues.length - 1" class="em-ml-4 em-mr-4"> {{ selectedAndorOperatorLabel }} </span>
					</div>
				</div>
			</div>
			<p v-else> {{ translate('ALL') }}</p>
		</section>
		<section v-else class="multi-select-filter-options em-mt-8">
			<div class="operators-selection em-flex-row">
				<div v-for="operator in operators" :key="filter.uid + '-' +operator.value" class="em-mr-8 em-p-8 em-border-radius-8" :class="{'label-default': operator.value !== selectedOperator, 'label-darkblue': operator.value === selectedOperator}">
					<input class="hidden label"
					       type="radio"
					       :id="filter.uid + '-operator-' + operator.value" :value="operator.value"
					       v-model="selectedOperator"
					>
					<label :for="filter.uid + '-operator-' + operator.value" style="margin: 0">{{ operator.label }}</label>
				</div>
			</div>
			<hr/>
			<div class="andor-selection em-flex-row">
				<div v-for="andor in andorOperators" :key="filter.uid + '-' + andor.value" class="em-mr-8 em-p-8 em-border-radius-8" :class="{'label-default': andor.value !== selectedAndorOperator, 'label-darkblue': andor.value === selectedAndorOperator}">
						<input class="hidden label"
						       type="radio"
						       :id="filter.uid + '-andor-' + andor.value"
						       :value="andor.value"
						       v-model="selectedAndorOperator"
						>
						<label :for="filter.uid + '-andor-' + andor.value" style="margin: 0">{{ andor.label }}</label>
				</div>
			</div>
			<hr/>
			<input class="em-w-100 em-p-8 em-border-radius-8 em-border-neutral-400 em-box-shadow em-white-bg"
			       type="text"
			       :placeholder="translate('MOD_EMUNDUS_FILTERS_FILTER_SEARCH')"
			       v-model="search"
			>
			<div class="values-selection em-mt-8">
				<div v-for="value in searchedValues" :key="value.value" class="em-flex-row">
					<input :id="filter.uid + '-filter-value-'+ value.value" type="checkbox" :value="value.value" v-model="selectedValues">
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
				{ value: '=', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS')},
				{ value: '!=', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_NOT')},
				{ value: 'IN', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_ONE_OF')},
				{ value: 'NOT IN', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_NOT_ONE_OF')}
			],
			selectedOperator: '=',
			andorOperators: [
				{ value: 'OR', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_OR')}
			],
			selectedAndorOperator: 'OR',
			selectedValues: [],
			search: ''
		}
	},
	mounted () {
		this.selectedValues = this.filter.value;
	},
	computed: {
		selectedOperatorLabel() {
			const selectedOperator =  this.operators.find((operator) => { return operator.value === this.selectedOperator });
			return selectedOperator ? selectedOperator.label : '';
		},
		selectedAndorOperatorLabel() {
			const selectedAndorOperator = this.andorOperators.find((andor) => { return andor.value === this.selectedAndorOperator });
			return selectedAndorOperator ? selectedAndorOperator.label : '';
		},
		selectedValuesLabels() {
			let labels = [];

			this.selectedValues.forEach((value) => {
				labels.push(this.filter.values.find((filterValue) => { return filterValue.value === value }).label);
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

</style>