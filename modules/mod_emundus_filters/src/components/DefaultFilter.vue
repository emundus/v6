<template>
	<div class="default-filter em-w-100 em-mb-16">
		<div class="em-flex-row em-flex-space-between">
			<p class="recap-label">{{ filter.label }}</p>
			<div>
				<span @mouseenter="resetHover = true" @mouseleave="resetHover = false" class="material-icons-outlined em-pointer reset-filter-btn" :class="{'em-blue-400-color': resetHover}" @click="resetFilter" :alt="translate('MOD_EMUNDUS_FILTERS_RESET')">refresh</span>
				<span v-if="!filter.default" class="material-icons-outlined em-red-500-color em-pointer" @click="$.emit('remove-filter')">close</span>
			</div>
		</div>
		<div class="default-filter-card em-border-radius-8 em-border-neutral-400 em-box-shadow em-white-bg em-p-8">
			<section v-if="!opened" class="recap" @click="opened = !opened">
				<div v-if="filter.value" class="em-flex-row em-flex-wrap em-flex-gap-8">
					<span class="recap-operator label label-darkblue em-mr-4"> {{ selectedOperatorLabel }}</span>
					<span class="recap-value label label-default"> {{ filter.value }}</span>
				</div>
				<p v-else> {{ translate('MOD_EMUNDUS_FILTERS_PLEASE_SELECT') }}</p>
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
			<span class="material-icons-outlined em-pointer toggle-open-close" @click="opened = !opened">{{opened ? 'keyboard_arrow_up' : 'keyboard_arrow_down'}}</span>
		</div>
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
			],
			resetHover: false
		}
	},

	methods: {
		resetFilter() {
			this.filter.operator = '=';
			this.filter.value = '';
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
.default-filter-card {
	position: relative;
}

.default-filter-card  .toggle-open-close {
	position: absolute;
	top: 4px;
	right: 4px;
}

span.label {
	font-weight: normal !important;
	display: flex !important;
	width: fit-content;
}

.recap {
	overflow: hidden;
}
</style>