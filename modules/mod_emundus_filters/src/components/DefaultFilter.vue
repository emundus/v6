<template>
	<div class="default-filter em-w-100 em-mb-8 em-border-radius-8 em-border-neutral-400 em-box-shadow em-white-bg em-p-8">
		<div class="em-flex-row em-flex-space-between">
			<p class="recap-label">{{ filter.label }}</p>
			<div>
				<span v-if="!filter.default" class="material-icons-outlined em-red-500-color em-pointer" @click="$.emit('remove-filter')">close</span>
				<span v-if="opened === false" class="material-icons-outlined em-pointer" @click="opened = !opened">keyboard_arrow_down</span>
				<span v-else class="material-icons-outlined em-pointer" @click="opened = !opened">keyboard_arrow_up</span>
			</div>
		</div>
		<section v-if="!opened" class="recap em-flex-row em-mt-8">
			<span class="recap-operator label label-darkblue em-mr-4"> {{ selectedOperatorLabel }}</span>
			<span class="recap-value"> {{ filter.value }}</span>
		</section>
		<section v-else class="default-filter-options em-mt-8">
			<div class="operators-selection em-flex-row em-flex-wrap em-flex-gap-8">
				<div v-for="operator in operators" :key="filter.uid + '-' + operator.value" class="em-p-8 em-border-radius-8" :class="{'label-default': operator.value !== filter.operator, 'label-darkblue': operator.value === filter.operator}">
					<input class="hidden label"
					       type="radio"
					       :id="filter.uid + '-operator-' + operator.value" :value="operator.value"
					       v-model="filter.operator"
					>
					<label :for="filter.uid + '-operator-' + operator.value" style="margin: 0">{{ operator.label }}</label>
				</div>
			</div>
			<hr/>
			<input type="text" v-model="filter.value">
		</section>
	</div>
</template>

<script>
export default {
	name: "DefaultFilter",
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
				{ value: 'NOT LIKE', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_DOES_NOT_CONTAIN')}
			]
		}
	},

	computed: {
		selectedOperatorLabel() {
			const selectedOperator =  this.operators.find((operator) => { return operator.value === this.filter.operator });
			return selectedOperator ? selectedOperator.label : '';
		}
	}
}
</script>

<style scoped>

</style>