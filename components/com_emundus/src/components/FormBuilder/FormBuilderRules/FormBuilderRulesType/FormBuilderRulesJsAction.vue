<template>
  <div id="form-builder-rules-js-action" class="self-start w-full">
    <div class="flex justify-between items-center">
      <h2>{{ actionLabel }}</h2>
      <button v-if="index !== 0" type="button" @click="$emit('remove-action', index)" class="w-auto">
        <span class="material-icons-outlined text-red-500">close</span>
      </button>
    </div>

    <div class="mt-4 flex ml-4" v-if="!loading">
      <p class="mr-4 mt-3 font-bold">{{ translate('COM_EMUNDUS_FORMBUILDER_RULE_THEN') }}</p>

      <div class="flex flex-col w-full ml-2">
        <div class="flex items-center">
          <div class="form-group w-full">
            <select class="w-full" v-model="action.action">
              <option v-for="actionOpt in actions" :value="actionOpt.value">{{ translate(actionOpt.label) }}</option>
            </select>
          </div>
        </div>

        <div class="mt-4">
          <div>
            <multiselect
                v-model="action.fields"
                label="label_tag"
                :custom-label="labelTranslate"
                track-by="name"
                :options="availableElements"
                :multiple="actionMultiple"
                :taggable="false"
                select-label=""
                selected-label=""
                deselect-label=""
                :placeholder="actionMultiple ? translate('COM_EMUNDUS_FORM_BUILDER_RULE_SELECT_FIELDS') : translate('COM_EMUNDUS_FORM_BUILDER_RULE_SELECT_FIELD')"
                :close-on-select="!actionMultiple"
                :clear-on-select="false"
                :searchable="true"
                :allow-empty="true"
                :key="action.action"
            ></multiselect>
          </div>

          <div class="mt-4" v-if="['show_options','hide_options'].includes(action.action) && options.length > 0">
            <multiselect
                v-model="action.params"
                label="value"
                track-by="primary_key"
                :options="options"
                :multiple="true"
                :taggable="false"
                select-label=""
                selected-label=""
                deselect-label=""
                :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_RULE_SELECT_OPTIONS')"
                :close-on-select="false"
                :clear-on-select="false"
                :searchable="true"
                :allow-empty="true"
            ></multiselect>
          </div>
        </div>
      </div>
    </div>
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

export default {
  components: {
    Multiselect
  },
  props: {
    page: {
      type: Object,
      default: {}
    },
    action: {
      type: Object,
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

      actions: [
        {id: 1, label: 'COM_EMUNDUS_FORMBUILDER_RULE_ACTION_SHOW', value: 'show', multiple: true},
        {id: 2, label: 'COM_EMUNDUS_FORMBUILDER_RULE_ACTION_HIDE', value: 'hide', multiple: true},
        {id: 3, label: 'COM_EMUNDUS_FORMBUILDER_RULE_ACTION_SHOW_OPTIONS', value: 'show_options', multiple: false},
        {id: 4, label: 'COM_EMUNDUS_FORMBUILDER_RULE_ACTION_HIDE_OPTIONS', value: 'hide_options', multiple: false}
      ],

      options: [],
      options_plugins: ['dropdown', 'databasejoin', 'radiobutton', 'checkbox']
    };
  },
  created() {
    if (this.page.id) {
      /*if(this.$props.action.params) {
        this.$props.action.params.forEach((param, index) => {
          this.$props.action.params[index] = JSON.parse(param)[0];
        });
      }*/

      this.$props.action.fields.forEach((field, index) => {
        this.$props.action.fields[index] = this.elements.find(element => element.name === field);

        if(this.action.action == 'show_options' || this.action.action == 'hide_options') {
          this.defineOptions(this.$props.action.fields[index]);
        }
      });
    }
  },
  methods: {
    labelTranslate({label}) {
      return label.fr;
    },
    defineOptions(val) {
      if (['show_options', 'hide_options'].includes(this.action.action)) {
        if (this.options_plugins.includes(val.plugin)) {
          if (val.plugin == 'databasejoin') {
            this.getDatabasejoinOptions(val.params.join_db_name, val.params.join_key_column, val.params.join_val_column, val.params.join_val_column_concat).then(response => {
              if (response.status && response.data != '') {
                this.options = response.options;
              } else {
                this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR'), this.translate(response.msg));
              }
            });
          } else {
            formBuilderService.getJTEXTA(val.params.sub_options.sub_labels).then(response => {
              if (response) {
                val.params.sub_options.sub_labels.forEach((label, index) => {
                  val.params.sub_options.sub_labels[index] = Object.values(response.data)[index];
                });
              }

              var ctr = 0;
              Object.values(val.params.sub_options.sub_values).forEach((option, key) => {
                let new_option = {
                  primary_key: option,
                  value: val.params.sub_options.sub_labels[key]
                };

                this.options.push(new_option);

                ctr++;
              });
            });
          }
        }
      }
    }
  },
  computed: {
    actionLabel() {
      return `Action n°${this.index + 1}`;
    },
    actionMultiple() {
      return this.actions.find(action => action.value === this.action.action).multiple;
    },
    availableElements() {
      if (!this.actionMultiple) {
        return this.elements.filter(element => ['databasejoin', 'dropdown', 'radiobutton', 'checkbox'].includes(element.plugin));
      } else {
        return this.elements;
      }
    }
  },
  watch: {
    'action.action': {
      handler: function (val, oldVal) {
        if (['show', 'hide'].includes(oldVal) && ['show_options', 'hide_options'].includes(val) && this.action.fields.length > 1) {
          this.action.fields = [];
        } else if(['show', 'hide'].includes(oldVal) && ['show_options', 'hide_options'].includes(val) && this.action.fields.length == 1) {
          this.defineOptions(this.action.fields[0]);
        }
      },
      deep: true
    },

    'action.fields': {
      handler: function (val, oldVal) {
        if (val) {
          this.defineOptions(val);
        }
      },
      deep: true
    }
  }
}
</script>

<style lang="scss">
</style>
