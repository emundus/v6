<template>
	<div class="multi-select-filter em-w-100 em-mb-8 em-border-radius-8 em-border-neutral-400 em-box-shadow em-white-bg em-p-8">
		<div class="em-flex-row em-flex-space-between">
			<p class="recap-label">{{ filter.label }}</p>
			<span v-if="opened === false" class="material-icons-outlined em-pointer" @click="opened = !opened">keyboard_arrow_down</span>
			<span v-else class="material-icons-outlined em-pointer" @click="opened = !opened">keyboard_arrow_up</span>
		</div>
		<section v-if="!opened" class="recap">
			<div v-if="selectedValues.length > 0 && !selectedValues.includes('all')">
				<p class="recap-operator"> {{ selectedOperator }}</p>
				<p class="recap-value">
					<span v-for="(value, index) in selectedValues" :key="index">{{ value }}{{ index < selectedValues.length - 1 ? ', ' : '' }}</span>
				</p>
			</div>
			<p v-else> {{ translate('ALL') }}</p>
		</section>
		<section v-else class="multi-select-filter-options">
			<div class="operators-selection">
				<div v-for="operator in operators" :key="operator.value" class="em-flex-row">
					<input type="radio" :id="'operator-' + operator.value" :value="operator.value" v-model="selectedOperator">
					<label :for="'operator-' + operator.value" style="margin: 0">{{ operator.label }}</label>
				</div>
			</div>
			<hr/>
			<div class="andor-selection">
				<div v-for="andor in andorOperators" :key="andor.value" class="em-flex-row">
					<input type="radio" :id="'andor-' + andor.value" :value="andor.value" v-model="selectedAndorOperator">
					<label :for="'andor-' + andor.value" style="margin: 0">{{ andor.label }}</label>
				</div>
			</div>
			<hr/>
			<div class="values-selection">
				<div v-for="value in filter.values" :key="value.value" class="em-flex-row">
					<input :id="'filter-value-'+ value.value" type="checkbox" :value="value.value" v-model="selectedValues">
					<label :for="'filter-value-'+ value.value" style="margin: 0">{{ value.label }}</label>
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
				{ value: 'LIKE', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_CONTAINS')},
				{ value: 'NOT LIKE', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_DOES_NOT_CONTAIN')},
				{ value: 'IN', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_ONE_OF')},
				{ value: 'NOT IN', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_NOT_ONE_OF')}
			],
			selectedOperator: '=',
			andorOperators: [
				{ value: 'AND', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_AND')},
				{ value: 'OR', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_OR')}
			],
			selectedAndorOperator: 'AND',
			selectedValues: []
		}
	},
}
</script>

<style scoped>

</style>