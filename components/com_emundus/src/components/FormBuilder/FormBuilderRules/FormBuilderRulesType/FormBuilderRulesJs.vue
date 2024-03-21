<template>
  <div id="form-builder-rules-js" class="self-start w-full">
    <h2>{{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_ADD_JS') }}</h2>

    <div id="form-builder-rules-js-conditions-block">
      <div v-for="(condition, index) in conditions" class="mt-2 rounded-lg bg-white px-3 py-4 flex flex-col gap-6">
        <form-builder-rules-js-condition :elements="elements" :index="index" :condition="condition" @remove-condition="removeCondition" :page="page" />
      </div>

      <div class="flex justify-end">
        <button type="button" @click="addCondition()" class="em-tertiary-button mt-2 w-auto">{{ translate('COM_EMUNDUS_ONBOARD_PARAMS_ADD_REPEATABLE') }}</button>
      </div>
    </div>

    <div id="form-builder-rules-js-actions-block">
      <div v-for="(action, index) in actions" class="mt-2 rounded-lg bg-white px-3 py-4 flex flex-col gap-6">
        <form-builder-rules-js-action :elements="elements" :index="index" :action="action" @remove-action="removeAction" :page="page" />
      </div>

      <div class="flex justify-end">
        <button type="button" @click="addAction()" class="em-tertiary-button mt-2 w-auto">{{ translate('COM_EMUNDUS_ONBOARD_PARAMS_ADD_REPEATABLE') }}</button>
      </div>
    </div>

    <hr/>
    <button class="mt-4 em-primary-button w-auto float-right" @click="saveRule">{{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_SAVE') }}</button>

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
import FormBuilderRulesJsAction
  from "@/components/FormBuilder/FormBuilderRules/FormBuilderRulesType/FormBuilderRulesJsAction.vue";

export default {
  components: {FormBuilderRulesJsAction, FormBuilderRulesJsCondition},
  props: {
    page: {
      type: Object,
      default: {}
    },
    elements: {
      type: Array,
      default: []
    },
    rule: {
      type: Object,
      default: {}
    }
  },
  mixins: [formBuilderMixin, globalMixin, errorMixin],
  data() {
    return {
      conditions: [],
      actions: [],
      loading: false,
    };
  },
  mounted() {
    if (this.page.id) {
      if(this.rule !== null && this.rule.conditions.length > 0) {
        this.conditions = this.rule.conditions;
      } else {
        this.conditions.push({
          label: '',
          field: '',
          values: '',
          state: '='
        });
      }

      if (this.rule !== null && this.rule.actions.length > 0) {
        this.actions = this.rule.actions;
      } else {
        this.actions.push({
          action: 'show',
          fields: [],
          params: []
        });
      }
    }
  },
  methods: {
    addCondition() {
      this.conditions.push({
        label: '',
        field: '',
        values: '',
        state: '='
      });
    },
    addAction() {
      this.actions.push({
        action: 'show',
        fields: []
      });
    },
    removeCondition(index) {
      this.conditions = this.conditions.filter((condition, i) => i !== index);
    },
    removeAction(index) {
      this.actions = this.actions.filter((condition, i) => i !== index);
    },

    saveRule() {
      let conditions_post = [];
      let actions_post = [];

      this.conditions.forEach((condition) => {
        if(condition.field && condition.values) {
          conditions_post.push({
            label: condition.label,
            field: condition.field.name,
            values: typeof condition.values === 'object' ? condition.values.primary_key : condition.values,
            state: condition.state
          });
        }
      });

      this.actions.forEach((action) => {
        if(action.fields) {
          let fields = [];
          if(typeof action.fields == 'Array') {
            action.fields.forEach((field) => {
              fields.push(field.name);
            });
          } else {
            fields.push(action.fields.name);
          }

          actions_post.push({
            action: action.action,
            fields: fields,
            params: action.params
          });
        }
      });

      if(conditions_post.length == 0) {
        this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_RULE_ERROR'), this.translate('COM_EMUNDUS_FORM_BUILDER_RULE_ERROR_CONDITION_EMPTY'));
        return;
      }

      if(actions_post.length == 0) {
        this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_RULE_ERROR'), this.translate('COM_EMUNDUS_FORM_BUILDER_RULE_ERROR_ACTION_EMPTY'));
        return;
      }

      if(this.rule !== null ) {
        formService.editRule(this.rule.id, conditions_post, actions_post).then(response => {
          if (response.status) {
            Swal.fire({
              title: this.translate('COM_EMUNDUS_FORM_BUILDER_RULE_EDIT_SUCCESS'),
              icon: 'success',
            }).then(() => {
              this.$emit('close-rule-add-js')
            });
          } else {
            this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_RULE_ERROR'), this.translate(response.msg));
          }
        });
      } else {
        formService.addRule(this.page.id, conditions_post, actions_post).then(response => {
          if (response.status) {
            Swal.fire({
              title: this.translate('COM_EMUNDUS_FORM_BUILDER_RULE_SUCCESS'),
              icon: 'success',
              customClass: {
                title: 'em-swal-title',
                confirmButton: 'em-swal-confirm-button',
              },
            }).then(() => {
              this.$emit('close-rule-add-js')
            });
          } else {
            this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_RULE_ERROR'), this.translate(response.msg));
          }
        });
      }
    }
  },
}
</script>

<style lang="scss">
</style>
