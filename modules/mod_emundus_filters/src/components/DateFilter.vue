<template>
  <div class="date-filter em-w-100 em-mb-16" :id="'filter-id-' +  filter.uid" :ref="'filter-id-' +  filter.uid"
       @click="toggleOpened">
    <div class="em-flex-row em-flex-space-between">
      <p class="recap-label" :title="filter.label">{{ filter.label }}</p>
      <div>
        <span @mouseenter="resetHover = true" @mouseleave="resetHover = false"
              class="material-icons-outlined em-pointer reset-filter-btn" :class="{'em-blue-400-color': resetHover}"
              @click="resetFilter" :alt="translate('MOD_EMUNDUS_FILTERS_RESET')">refresh</span>
        <span v-if="!filter.default" class="material-icons-outlined em-red-500-color em-pointer remove-filter-btn"
              @click="$.emit('remove-filter')">close</span>
      </div>
    </div>
    <div class="date-filter-card em-border-radius-8 em-border-neutral-400 em-box-shadow em-white-bg em-p-8">
      <section class="recap em-flex-row em-mt-8" :class="{'hidden': opened}">
        <div v-if="filter.value[0]" class="em-flex-row em-flex-wrap em-flex-gap-8">
          <span class="recap-operator label label-darkblue"> {{ selectedOperatorLabel }}</span>
          <p class="recap-value em-flex-row em-flex-wrap em-flex-gap-8">
            <span v-if="filter.value[0]" class="label label-default">{{ formattedDate(filter.value[0]) }}</span>
            <span v-if="['between', '!between'].includes(filter.operator) && filter.value[1]"
                  class="label label-default"> {{
                translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_AND')
              }} {{ formattedDate(filter.value[1]) }}</span>
          </p>
        </div>
        <p v-else class="em-text-neutral-500"> {{ translate('MOD_EMUNDUS_FILTERS_PLEASE_SELECT') }}</p>
      </section>
      <section class="default-filter-options em-mt-8" :class="{'hidden': !opened}">
        <div class="operators-selection em-flex-row em-flex-wrap em-flex-gap-8">
          <div v-for="operator in operators" :key="filter.uid + '-' + operator.value"
               class="em-p-6-10 em-border-radius-8"
               :class="{'label-default': operator.value !== filter.operator, 'label-darkblue': operator.value === filter.operator}">
            <input class="hidden label"
                   type="radio"
                   :id="filter.uid + '-operator-' + operator.value" :value="operator.value"
                   v-model="filter.operator"
            >
            <label :for="filter.uid + '-operator-' + operator.value" style="margin: 0"
                   class="em-font-size-14">{{ operator.label }}</label>
          </div>
        </div>
        <hr/>
        <input type="date" :id="filter.uid + '-start_date'" v-model="filter.value[0]"/>
        <div v-if="filter.operator === 'between' || filter.operator === '!between'" class="em-mt-8">
          <p>{{ translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_AND') }}</p>
          <input class="em-mt-8" type="date" :id="filter.uid + '-end_date'" v-model="filter.value[1]"/>
        </div>
      </section>
      <span
          class="material-icons-outlined em-pointer toggle-open-close">{{ opened ? 'keyboard_arrow_up' : 'keyboard_arrow_down' }}</span>
    </div>
  </div>
</template>

<script>
import date from '@/mixins/date.js';

export default {
  name: "DateFilter.vue",
  mixins: [date],
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
        {value: '=', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS')},
        {value: '!=', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_IS_NOT')},
        {value: 'superior', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_SUPERIOR_TO')},
        {value: 'inferior', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_INFERIOR_TO')},
        {value: 'superior_or_equal', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_SUPERIOR_OR_EQUAL_TO')},
        {value: 'inferior_or_equal', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_INFERIOR_OR_EQUAL_TO')},
        {value: 'between', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_BETWEEN')},
        {value: '!between', label: this.translate('MOD_EMUNDUS_FILTERS_FILTER_OPERATOR_NOT_BETWEEN')}
      ],
      resetHover: false,
      originalFilterValue: null,
      originalFilterOperator: null
    }
  },
  mounted() {
    this.originalFilterValue = JSON.parse(JSON.stringify(this.filter.value));
    this.originalFilterOperator = this.filter.operator;
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  methods: {
    resetFilter(event) {
      this.filter.operator = '=';
      this.filter.value = ['', ''];

      if (this.opened) {
        this.opened = false;
        document.removeEventListener('click', this.handleClickOutside);
        this.onCloseCard();
      } else {
        this.onCloseCard();
      }

      event.stopPropagation();
    },
    toggleOpened(event = null) {
      if (event && (event.target.closest('.default-filter-options') || event.target.classList.contains('remove-filter-btn'))) {
        return;
      }

      this.opened = !this.opened;
      if (this.opened === false) {
        document.removeEventListener('click', this.handleClickOutside);
        this.onCloseCard();
      } else {
        document.addEventListener('click', this.handleClickOutside);
      }
    },
    onCloseCard() {
      const valueDifferences = (this.filter.value[0] !== this.originalFilterValue[0]) || (this.filter.value[1] !== this.originalFilterValue[1]);
      const operatorDifferences = this.filter.operator !== this.originalFilterOperator;

      if (valueDifferences || operatorDifferences) {
        this.originalFilterValue = this.filter.value;
        this.originalFilterOperator = this.filter.operator;
        this.$emit('filter-changed');
      }
    },
    handleClickOutside(event) {
      if (this.opened) {
        const clickedElement = event.target;
        const componentElement = this.$refs['filter-id-' + this.filter.uid]; // Élément racine

        if (clickedElement && !componentElement.contains(clickedElement) && !clickedElement.closest('#' + componentElement.id)) {
          this.toggleOpened(event);
        }
      }
    }
  },
  computed: {
    selectedOperatorLabel() {
      const selectedOperator = this.operators.find((operator) => {
        return operator.value === this.filter.operator
      });
      return selectedOperator ? selectedOperator.label : '';
    }
  }
}
</script>

<style scoped>
.date-filter-card {
  position: relative;
}

.date-filter-card .toggle-open-close {
  position: absolute;
  top: 4px;
  right: 4px;
}

span.label {
  font-weight: normal !important;
  display: flex !important;
  width: fit-content;
}
</style>