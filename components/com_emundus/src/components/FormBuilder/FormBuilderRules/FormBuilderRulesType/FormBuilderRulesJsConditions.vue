<template>
  <div id="form-builder-rules-js-conditions" class="self-start w-full">
    <div class="flex justify-between items-center">
      <h3>{{ conditionLabel }}</h3>
      <button v-if="index !== 0" type="button" @click="$emit('remove-condition', index)" class="w-auto">
        <span class="material-icons-outlined text-red-500">close</span>
      </button>
    </div>

    <div class="mt-4 ml-4 flex" v-for="condition in conditions">
      <form-builder-rules-js-condition :elements="elements" :index="index" :condition="condition" @remove-condition="$emit('remove-condition')" :page="page" :multiple="Object.values(conditions).length > 1" />
    </div>

    <button type="button" @click="$emit('add-condition',index)" class="em-tertiary-button mt-2 w-auto float-right">{{ translate('COM_EMUNDUS_ONBOARD_PARAMS_ADD_REPEATABLE_CONDITION') }}</button>
  </div>
</template>

<script>
import formService from '../../../../services/form';

import formBuilderMixin from '../../../../mixins/formbuilder';
import globalMixin from '../../../../mixins/mixin';
import fabrikMixin from '../../../../mixins/fabrik';
import errorMixin from '../../../../mixins/errors';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';
import formBuilderService from "@/services/formbuilder";
import FormBuilderRulesJsCondition
  from "@/components/FormBuilder/FormBuilderRules/FormBuilderRulesType/FormBuilderRulesJsCondition.vue";

export default {
  components: {
    FormBuilderRulesJsCondition,
    Multiselect
  },
  props: {
    page: {
      type: Object,
      default: {}
    },
    conditions: {
      type: Array,
      default: {}
    },
    index: {
      type: Number,
      default: 0
    },
    elements: {
      type: Array,
      default: []
    }
  },
  mixins: [formBuilderMixin, globalMixin, errorMixin, fabrikMixin],
  data() {
    return {
      loading: false,
    };
  },
  mounted() {},
  methods: {
    labelTranslate({label}) {
      return label ? label.fr : '';
    },
  },
  computed: {
    conditionLabel() {
      return `-- ${this.index + 1} --`;
    }
  },
  watch: {}
}
</script>

<style lang="scss">
</style>
