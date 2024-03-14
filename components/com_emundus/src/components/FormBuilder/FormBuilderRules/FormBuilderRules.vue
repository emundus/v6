<template>
  <div id="form-builder-rules" class="self-start w-full">
    <div class="p-8">
      <h2 class="mb-3" v-if="rules.length > 0">{{ translate('COM_EMUNDUS_FORMBUILDER_RULES') }}</h2>
      <div class="flex flex-col gap-3">
        <div v-for="rule in rules" :key="rule.id">
          <div class="rounded-lg bg-white px-3 py-4 flex flex-col gap-6">

            <div :id="'condition_'+rule.id" class="flex flex-col gap-2">
              <div v-for="condition in rule.conditions" class="flex items-center">
                <span class="material-icons-outlined !text-2xl !font-medium mr-3 text-black">alt_route</span>
                <div class="leading-8">
                  <span class="font-medium mr-1">{{ translate('COM_EMUNDUS_FORMBUILDER_RULE_IF') }}</span>
                  <span>{{ condition.label }}</span>
                  <span class="p-2 rounded-lg label-darkblue ml-2 mr-2">{{ operator(condition.state) }}</span>
                  <span>{{ getvalues(condition) }}</span>
                </div>
              </div>
            </div>

            <div v-if="rule.conditions.length > 1">
              <p class="font-medium">{{ translate('COM_EMUNDUS_FORMBUILDER_RULE_GROUP_'+rule.group) }}</p>
            </div>

            <div :id="'action_'+rule.id" class="flex flex-col gap-2">
              <div v-for="action in rule.actions" class="flex items-center">
                <span class="material-icons-outlined !text-2xl !font-medium mr-3 text-black" v-if="action.action == 'show'">visibility</span>
                <span class="material-icons-outlined !text-2xl !font-medium mr-3 text-black" v-if="action.action == 'hide'">visibility_off</span>
                <div>
                  <span class="font-medium mr-1">{{ translate('COM_EMUNDUS_FORMBUILDER_RULE_ACTION_'+action.action.toUpperCase()) }}</span>
                  <span>{{ action.labels.join(', ') }}</span>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import formService from '../../../services/form';

import formBuilderMixin from '../../../mixins/formbuilder';
import globalMixin from '../../../mixins/mixin';
import errorMixin from '../../../mixins/errors';
import Swal from 'sweetalert2';

export default {
  components: {},
  props: {
    page: {
      type: Object,
      default: {}
    },
	  mode: {
		  type: String,
		  default: 'forms'
	  }
  },
  mixins: [formBuilderMixin, globalMixin, errorMixin],
  data() {
    return {
      rules: [],

      loading: false,
    };
  },
  mounted() {
    if (this.page.id) {
      this.getConditions();
    }
  },
  methods: {
    getConditions() {
      this.loading = true;
      formService.getConditions(this.page.id).then(response => {
        if (response.status && response.data != '') {
          this.rules = response.data.conditions;
        } else {
					this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR'), this.translate(response.msg));
        }

	      this.loading = false;
      });
    },

    operator(state) {
      switch (state) {
        case '=':
          return this.translate('COM_EMUNDUS_FORMBUILDER_RULE_OPERATOR_EQUALS');
        case '!=':
          return this.translate('COM_EMUNDUS_FORMBUILDER_RULE_OPERATOR_NOT_EQUALS');
      }
    },

    getvalues(condition) {
      if(condition.options) {
        let index = condition.options.sub_values.findIndex(option => option == condition.values);
        return condition.options.sub_labels[index];
      } else {
        return condition.values;
      }
    },
  },
}
</script>

<style lang="scss">
#form-builder-rules {
  .label-darkblue {
    background-color: var(--neutral-300) !important;
    border: 1px solid var(--neutral-800);
    color: var(--neutral-900);
    text-shadow: none;
  }
}
</style>
