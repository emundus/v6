<template>
  <div id="form-builder-rules" class="self-start w-full">
    <div class="p-8">
      <h2 class="mb-3" v-if="rules.length > 0">{{
          translate('COM_EMUNDUS_FORMBUILDER_RULES') + this.$props.page.label
        }}</h2>

      <button id="add-section" class="em-primary-button px-6 py-3 mb-4" @click="$emit('add-rule','js')">
        {{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_ADD_CONDITION') }}
      </button>

      <div class="relative flex items-center">
        <input
            v-model="keywords"
            type="text"
            class="formbuilder-searchbar bg-transparent"
            :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_SEARCH_CONDITION')"
        />
        <button v-if="keywords !== ''" type="button" @click="keywords = ''" class="w-auto absolute right-3 h-[16px]">
          <span class="material-icons-outlined">close</span>
        </button>
      </div>


      <div class="flex flex-col gap-3 mt-3" v-if="!loading">
        <h5 v-if="searchedRules.length == 0">{{ translate('COM_EMUNDUS_FORM_BUILDER_RULES_NOT_FOUND') }}</h5>

        <div v-for="(rule, index) in searchedRules" :key="rule.id">
          <div class="rounded-lg px-3 py-4 flex flex-col gap-6 border border-neutral-600"
               :class="{ 'bg-neutral-400': rule.published == 0, 'bg-white': rule.published == 1}">

            <div class="flex justify-between items-start">
              <h3>{{ ruleLabel(rule, index) }}</h3>

              <div class="cursor-pointer">
                <v-popover :popoverArrowClass="'custom-popover-arraow'" :open-class="'form-builder-pages-popover'"
                           :placement="'left'">
                  <span class="material-icons !font-bold	!text-2xl">more_vert</span>

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

            <div :id="'condition_'+rule.id" class="flex flex-col gap-2">
              <div v-for="(grouped_condition,key) in Object.values(rule.conditions)">
                <p v-if="(key != 0 && grouped_condition.length > 1)" class="font-medium ml-1 mr-2 mb-2">
                  {{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_CONDITION_' + rule.group) }}</p>

                <div class="flex flex-col gap-4" :class="{ 'bg-neutral-300 rounded p-2': grouped_condition.length > 1}">
                  <div v-for="(condition,condition_index) in grouped_condition" class="flex items-center">
                    <span
                        v-if="(condition_index == 0 && grouped_condition.length > 1) || (key == 0 && grouped_condition.length == 1)"
                        class="material-icons-outlined !text-2xl !font-medium mr-1 text-black">alt_route</span>
                    <span v-if="(condition_index != 0 && grouped_condition.length > 1)" class="font-medium ml-1 mr-2">{{
                        translate('COM_EMUNDUS_FORM_BUILDER_RULE_CONDITION_' + condition.group_type)
                      }}</span>
                    <span v-if="(key != 0 && grouped_condition.length == 1)" class="font-medium ml-1 mr-2">{{
                        translate('COM_EMUNDUS_FORM_BUILDER_RULE_CONDITION_' + rule.group)
                      }}</span>

                    <div class="leading-8">
                      <span class="font-medium mr-1"
                            v-if="(condition_index == 0  && grouped_condition.length > 1) || (key == 0 && grouped_condition.length == 1)">{{
                          translate('COM_EMUNDUS_FORMBUILDER_RULE_IF')
                        }}</span>

                      <span class="conditions-label">{{ condition.elt_label }}</span>

                      <span class="p-1 rounded-md label-darkblue ml-1 mr-2">{{ operator(condition.state) }}</span>
                      <span>{{ getvalues(condition) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <hr class="m-0"/>
            <div :id="'action_'+rule.id" class="flex flex-col gap-2">
              <div v-for="(action,action_index) in rule.actions" class="flex items-center">
                <span class="material-icons-outlined !text-2xl !font-medium mr-3 text-black"
                      v-if="['show','show_options'].includes(action.action)">visibility</span>
                <span class="material-icons-outlined !text-2xl !font-medium mr-3 text-black"
                      v-if="['hide','hide_options'].includes(action.action)">visibility_off</span>
                <span class="material-icons-outlined !text-2xl !font-medium mr-3 text-black"
                      v-if="['set_optional','set_mandatory'].includes(action.action)">indeterminate_check_box</span>
                <span class="material-icons-outlined !text-2xl !font-medium mr-3 text-black"
                      v-if="['define_repeat_group'].includes(action.action)">repeat</span>
                <div>
                  <span class="font-medium mr-1">{{
                      translate('COM_EMUNDUS_FORMBUILDER_RULE_ACTION_' + action.action.toUpperCase())
                    }}</span>

                  <span v-if="['show_options','hide_options'].includes(action.action)">{{
                      elementOptions(action)
                    }}</span>
                  <span v-if="['show_options','hide_options'].includes(action.action)"
                        class="font-medium"> {{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_OF_FIELD') }}</span>

                  <span v-if="['define_repeat_group'].includes(action.action)">{{
                      translate('COM_EMUNDUS_FORMBUILDER_RULE_ACTION_DEFINE_REPEAT_BETWEEN') + ' ' + elementOptions(action) + ' ' + translate('COM_EMUNDUS_FORMBUILDER_RULE_ACTION_DEFINE_REPEAT_REPETITIONS')
                    }}</span>

                  <span class="actions-label">{{ action.labels.join(', ') }}</span>
                </div>
              </div>
            </div>

            <span class="material-icons-outlined self-end" v-if="rule.published == 0">visibility_off</span>

          </div>
        </div>
      </div>

      <button v-if="searchedRules.length > 5" id="add-section" class="em-primary-button px-6 py-3 mt-4"
              @click="$emit('add-rule','js')">
        {{ translate('COM_EMUNDUS_FORM_BUILDER_RULE_ADD_CONDITION') }}
      </button>
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
      elements: [],
      keywords: '',

      loading: false,
    };
  },
  created() {
    this.keywords = this.$store.getters['formBuilder/getRulesKeywords'];
    if(this.keywords) {
      setTimeout(() => {
        this.highlight(this.keywords);
      }, 500);
    }

    if (this.page.id) {
      this.getConditions();

      formService.getPageObject(this.page.id).then(response => {
        if (response.status && response.data != '') {
          this.fabrikPage = response.data;
        } else {
          this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR'), this.translate(response.msg));
        }

        Object.entries(this.fabrikPage.Groups).forEach(([key, group]) => {
          Object.entries(group.elements).forEach(([key, element]) => {
            if (!element.hidden) {
              this.elements.push(element);
            }
          });
        });
      });
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

      if (action.params) {
        try {
          let action_params = JSON.parse(action.params);

          if (action.action == 'define_repeat_group') {
            if (action_params.length > 0) {
              let min = action_params[0].minRepeat;
              let max = action_params[0].minRepeat;
              options.push(min);
              options.push(max);
            }
          } else {
            action_params.forEach(param => {
              options.push(param.value);
            });
          }
        } catch (e) {
          return console.error(e); // error in the above string (in this case, yes)!
        }
      }

      if (options.length > 0) {
        if (action.action == 'define_repeat_group') {
          options = options.join(' ' + this.translate('COM_EMUNDUS_FORMBUILDER_RULE_ACTION_DEFINE_REPEAT_AND') + ' ');
        } else {
          options = options.join(', ');
        }
      } else {
        options = '';
      }

      return options;
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

    ruleLabel(rule, index) {
      if (rule.label && rule.label.trim() != '') {
        return rule.label;
      } else {
        return this.translate('COM_EMUNDUS_FORM_BUILDER_RULE_CONDITION') + (index + 1);
      }
    },

    groupedType(conditions) {
      let type = 'AND';
      Object.values(conditions).forEach(condition => {
        condition.forEach(cond => {
          type = cond.group_type;
        });
      });

      return type;
    },

    highlight(searchTerm) {
      const conditions = document.querySelectorAll(".conditions-label");
      const actions = document.querySelectorAll(".actions-label");
      const elements = [...conditions, ...actions];

      elements.forEach((element) => {
        const text = element.innerText;
        let regex = new RegExp(`(${searchTerm})`, "gi");
        // Check if the element's text contains the search term
        if (searchTerm && text.match(regex)) {
          // Split the text into parts (matched and unmatched)
          const parts = text.split(regex);
          // Create a new HTML structure with the matched term highlighted
          const highlightedText = parts
              .map((part) =>
                  part.match(regex)
                      ? `<span style="background-color: var(--em-yellow-1);">${part}</span>`
                      : part
              )
              .join("");
          // Replace the original text with the highlighted version
          element.innerHTML = highlightedText;
        } else {
          element.innerHTML = text;
        }
      });
    },

    removeHighlight(field) {
      let element = document.getElementById(field);

      if (element) {
        element.innerHTML = element.innerText;
      }
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
  computed: {
    searchedRules() {
      if (this.keywords) {
        let elements_found = this.elements.filter(element => element.label.fr.toLowerCase().includes(this.keywords.toLowerCase()));
        return this.rules.filter(rule => {
          let found = false;
          if (rule.label) {
            found = rule.label.toLowerCase().includes(this.keywords.toLowerCase())
          }
          if (!found) {
            Object.values(rule.conditions).forEach((grouped_conditions, key) => {
              grouped_conditions.forEach((condition, index) => {
                if (elements_found.find(element => element.name == condition.field)) {
                  found = true;
                }
              })
            });
          }
          if (!found) {
            rule.actions.forEach((action, index) => {
              action.fields.forEach(field => {
                if (elements_found.find(element => element.name == field)) {
                  found = true;
                }
              });
            });
          }

          return found;
        });
      } else {
        return this.rules;
      }
    }
  },
  watch: {
    keywords: {
      handler: function (val) {
        this.$store.commit('formBuilder/updateRulesKeywords', val);
        this.highlight(val);
      }
    }
  }
}
</script>

<style lang="scss">
#form-builder-rules, #form-builder-rules-js-conditions {
  .label-darkblue {
    background-color: var(--em-profile-color) !important;
    color: white;
    text-shadow: none;
  }

  input.formbuilder-searchbar {
    border-width: 0 0 1px 0;
    border-radius: 0;
    border-color: var(--neutral-900);
    background-color: transparent;

    &:focus {
      outline: unset;
      border-bottom-color: var(--em-form-outline-color-focus);
    }
  }

  .highlight {
    background-color: var(--em-yellow-1);
  }
}
</style>
