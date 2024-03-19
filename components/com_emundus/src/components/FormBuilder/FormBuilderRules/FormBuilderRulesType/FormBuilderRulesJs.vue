<template>
  <div id="form-builder-rules-js" class="self-start w-full">
    <h2>{{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_ADD_JS') }}</h2>

    <div v-for="(condition, index) in conditions" class="mt-2 rounded-lg bg-white px-3 py-4 flex flex-col gap-6">
      <form-builder-rules-js-condition :elements="elements" :index="index" :condition="condition" @remove-condition="removeCondition" :page="page" />
    </div>

    <div class="flex justify-end">
      <button type="button" @click="addCondition()" class="em-tertiary-button mt-2 w-auto">{{ translate('COM_EMUNDUS_ONBOARD_PARAMS_ADD_REPEATABLE') }}</button>
    </div>
  </div>
</template>

<script>
import formService from '../../../../services/form';

import formBuilderMixin from '../../../../mixins/formbuilder';
import globalMixin from '../../../../mixins/mixin';
import errorMixin from '../../../../mixins/errors';
import Swal from 'sweetalert2';
import FormBuilderRulesJsCondition
  from "@/components/FormBuilder/FormBuilderRules/FormBuilderRulesType/FormBuilderRulesJsCondition.vue";

export default {
  components: {FormBuilderRulesJsCondition},
  props: {
    page: {
      type: Object,
      default: {}
    },
    elements: {
      type: Array,
      default: []
    }
  },
  mixins: [formBuilderMixin, globalMixin, errorMixin],
  data() {
    return {
      conditions: [],
      loading: false,
    };
  },
  mounted() {
    if (this.page.id) {
      this.conditions.push({
        label: '',
        field: '',
        values: ''
      });
    }
  },
  methods: {
    addCondition() {
      this.conditions.push({
        label: '',
        field: '',
        values: ''
      });
    },
    removeCondition(index) {
      this.conditions = this.conditions.filter((condition, i) => i !== index);
    }
  },
}
</script>

<style lang="scss">
</style>
