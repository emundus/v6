<template>
  <div id="form-builder-rules" class="self-start w-full">
    <div class="p-8">
      <h2 class="mb-3" v-if="rules.length > 0">{{ translate('COM_EMUNDUS_FORMBUILDER_RULES') }}</h2>
      <div class="flex flex-col gap-3">
        <div v-for="rule in rules" :key="rule.id">
          <div class="rounded-lg px-3 py-4 flex flex-col gap-6" :class="{ 'bg-neutral-400	border border-neutral-600': rule.published == 0, 'bg-white': rule.published == 1}">

            <div class="flex justify-between items-start">
              <div :id="'condition_'+rule.id" class="flex flex-col gap-2">
                <div v-for="condition in rule.conditions" class="flex items-center">
                  <span class="material-icons-outlined !text-2xl !font-medium mr-3 text-black">alt_route</span>
                  <div class="leading-8">
                    <span class="font-medium mr-1">{{ translate('COM_EMUNDUS_FORMBUILDER_RULE_IF') }}</span>
                    <span>{{ condition.elt_label }}</span>
                    <span class="p-2 rounded-lg label-darkblue ml-2 mr-2">{{ operator(condition.state) }}</span>
                    <span>{{ getvalues(condition) }}</span>
                  </div>
                </div>
              </div>

              <div class="cursor-pointer">
                <v-popover :popoverArrowClass="'custom-popover-arraow'" :open-class="'form-builder-pages-popover'"
                           :placement="'left'">
                  <span class="material-icons font-bold	!text-xl">more_vert</span>

                  <template slot="popover">
                    <transition :name="'slide-down'" type="transition">
                      <div>
                        <nav aria-label="action" class="em-flex-col-start">
                          <p @click="$emit('add-rule','js',rule)" class="py-3 px-4 w-full">
                            {{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_EDIT') }}
                          </p>
                          <p @click="publishRule(rule, 1)" class="py-3 px-4 w-full" v-if="rule.published == 0">
                            {{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_PUBLISH') }}
                          </p>
                          <p @click="publishRule(rule, 0)" class="py-3 px-4 w-full" v-if="rule.published == 1">
                            {{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_UNPUBLISH') }}
                          </p>
<!--                          <p @click="cloneRule(rule)" class="py-3 px-4 w-full">
                            {{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_DUPLICATE') }}
                          </p>-->
                          <p @click="deleteRule(rule)" class="py-3 px-4 w-full text-red-500">
                            {{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_DELETE') }}
                          </p>
                        </nav>
                      </div>
                    </transition>
                  </template>
                </v-popover>
              </div>
            </div>


            <div v-if="rule.conditions.length > 1">
              <p class="font-medium">{{ translate('COM_EMUNDUS_FORMBUILDER_RULE_GROUP_' + rule.group) }}</p>
            </div>

            <div :id="'action_'+rule.id" class="flex flex-col gap-2">
              <div v-for="action in rule.actions" class="flex items-center">
                <span class="material-icons-outlined !text-2xl !font-medium mr-3 text-black"
                      v-if="['show','show_options'].includes(action.action)">visibility</span>
                <span class="material-icons-outlined !text-2xl !font-medium mr-3 text-black"
                      v-if="['hide','hide_options'].includes(action.action)">visibility_off</span>
                <div>
                  <span class="font-medium mr-1">{{
                      translate('COM_EMUNDUS_FORMBUILDER_RULE_ACTION_' + action.action.toUpperCase())
                    }}</span>

                  <span v-if="['show_options','hide_options'].includes(action.action)">{{ elementOptions(action) }}</span>
                  <span v-if="['show_options','hide_options'].includes(action.action)" class="font-medium"> {{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_OF_FIELD') }}</span>

                  <span> {{ action.labels.join(', ') }}</span>
                </div>
              </div>
            </div>

            <span class="material-icons-outlined self-end" v-if="rule.published == 0">visibility_off</span>

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
      if (condition.options) {
        let index = condition.options.sub_values.findIndex(option => option == condition.values);
        return condition.options.sub_labels[index];
      } else {
        return condition.values;
      }
    },

    elementOptions(action) {
      let options = [];
      if(action.params) {
        let action_params = JSON.parse(action.params);

        action_params.forEach(param => {
          options.push(param.value);
        });
      }

      return options.join(', ');
    },

    deleteRule(rule) {
      Swal.fire({
        title: this.translate('COM_EMUNDUS_FORM_BUILDER_RULE_DELETE_TITLE'),
        text: this.translate('COM_EMUNDUS_FORM_BUILDER_RULE_DELETE_CONFIRM'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: this.translate('COM_EMUNDUS_FORM_BUILDER_DELETE_RULE'),
        cancelButtonText: this.translate('COM_EMUNDUS_FORM_BUILDER_CANCEL'),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
      }).then((result) => {
        if (result.value) {
          this.loading = true;
          formService.deleteRule(rule.id).then(response => {
            if (response.status) {
              this.getConditions();
            } else {
              this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR'), this.translate(response.msg));
            }
          });
        }
      });
    },

    publishRule(rule, state) {
      this.loading = true;
      formService.publishRule(rule.id, state).then(response => {
        if (response.status) {
          this.getConditions();
        } else {
          this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR'), this.translate(response.msg));
        }
      });
    },

    /*cloneRule(rule)
    {
      this.loading = true;
      formService.cloneRule(rule.id).then(response => {
        if (response.status) {
          this.getConditions();
        } else {
          this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR'), this.translate(response.msg));
        }
      });
    }*/
  },
}
</script>

<style lang="scss">
#form-builder-rules, #form-builder-rules-js-condition {
  .label-darkblue {
    background-color: var(--neutral-300) !important;
    border: 1px solid var(--neutral-800);
    color: var(--neutral-900);
    text-shadow: none;
  }
}
</style>
