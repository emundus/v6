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
			<p class="recap-value">
				<span v-if="filter.value[0]">{{ filter.value[0] }}</span>
				<span v-if="['between', '!between'].includes(filter.operator) && filter.value[1]"> {{ translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_AND') }} {{ filter.value[1] }}</span>
			</p>
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
			<input type="date" :id="filter.uid + '-start_date'" v-model="filter.value[0]"/>
			<div v-if="filter.operator === 'between' || filter.operator === '!between'" class="em-mt-8">
				<p>{{ translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_AND') }}</p>
				<input type="date" :id="filter.uid + '-end_date'" v-model="filter.value[1]"/>
			</div>
		</section>
	</div>
</template>

<script>
export default {
	name: "DateFilter.vue",
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
				{ value: 'superior', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_SUPERIOR_TO')},
				{ value: 'inferior', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_INFERIOR_TO')},
				{ value: 'superior_or_equal', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_SUPERIOR_OR_EQUAL_TO')},
				{ value: 'inferior_or_equal', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_INFERIOR_OR_EQUAL_TO')},
				{ value: 'between', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_BETWEEN')},
				{ value: '!between', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_NOT_BETWEEN')}
			],
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