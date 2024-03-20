<template>
  <div id="form-builder-rules-js-condition" class="self-start w-full">
    <div class="flex justify-between items-center">
      <h2>{{ conditionLabel }}</h2>
      <button v-if="index !== 0" type="button" @click="$emit('remove-condition', index)" class="w-auto">
        <span class="material-icons-outlined text-red-500">close</span>
      </button>
    </div>

    <div class="mt-4 ml-4 flex">
      <p class="mr-4 mt-3 font-bold">{{ translate('COM_EMUNDUS_FORMBUILDER_RULE_IF') }}</p>

      <div class="flex flex-col ml-2 w-full">
        <div class="flex items-center">
          <multiselect
              v-model="condition.field"
              label="label_tag"
              :custom-label="labelTranslate"
              track-by="name"
              :options="elements"
              :multiple="false"
              :taggable="false"
              select-label=""
              selected-label=""
              deselect-label=""
              :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_RULE_SELECT_FIELD')"
              :close-on-select="true"
              :clear-on-select="false"
              :searchable="true"
              :allow-empty="true"
          ></multiselect>
        </div>

        <div class="mt-4">
          <div class="flex items-center gap-3">
          <span v-for="operator in operators" :key="operator.id" class="cursor-pointer p-2 rounded-lg ml-1 border border-neutral-500" @click="condition.state = operator.value" :class="{ 'label-darkblue': condition.state == operator.value }">
            {{ translate(operator.label) }}
          </span>
          </div>

          <div class="mt-6">
            <multiselect
                v-if="options_plugins.includes(condition.field.plugin) || condition.field.plugin == 'yesno'"
                v-model="condition.values"
                label="value"
                track-by="primary_key"
                :options="options"
                :multiple="false"
                :taggable="false"
                select-label=""
                selected-label=""
                deselect-label=""
                :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_RULE_SELECT_FIELD')"
                :close-on-select="true"
                :clear-on-select="false"
                :searchable="true"
                :allow-empty="true"
            ></multiselect>
            <input v-else v-model="condition.values" />
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
    condition: {
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

      operators: [
        { id: 1, label: 'COM_EMUNDUS_FORMBUILDER_RULE_OPERATOR_EQUALS', value: '=' },
        { id: 2, label: 'COM_EMUNDUS_FORMBUILDER_RULE_OPERATOR_NOT_EQUALS', value: '!=' }
      ],
      options: [],
      options_plugins : ['dropdown','databasejoin','radiobutton','checkbox']
    };
  },
  mounted() {
    if (this.page.id) {}
  },
  methods: {
    labelTranslate({ label }) {
      return label.fr;
    },
  },
  computed: {
    conditionLabel() {
      return this.condition.label !== '' ? this.condition.label : `Condition n°${this.index + 1}`;
    }
  },
  watch: {
    'condition.field': {
      handler: function (val) {
        this.condition.values = '';
        this.options = [];

        if(this.options_plugins.includes(val.plugin)) {
          if(val.plugin == 'databasejoin') {
            this.loading = true;

            this.getDatabasejoinOptions(val.params.join_db_name,val.params.join_key_column,val.params.join_val_column,val.params.join_val_column_concat).then(response => {
              if (response.status && response.data != '') {
                this.options = response.options;
              } else {
                this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_ERROR'), this.translate(response.msg));
              }
              this.loading = false;
            });
          } else {
            formBuilderService.getJTEXTA(val.params.sub_options.sub_labels).then(response => {
              if (response) {
                val.params.sub_options.sub_labels.forEach((label, index) => {
                  val.params.sub_options.sub_labels[index] = Object.values(response.data)[index];
                });
              }

              Object.entries(val.params.sub_options).forEach((option, key) => {
                let new_option = {
                  primary_key: option,
                  value: val.params.sub_options.sub_labels[key]
                };

                this.options.push(new_option);
              });

              this.loading = false;
            });
          }
        }

        if(val.plugin == 'yesno') {
          this.options = [
            { primary_key: 0, value: this.translate('COM_EMUNDUS_FORMBUILDER_NO') },
            { primary_key: 1, value: this.translate('COM_EMUNDUS_FORMBUILDER_YES') }
          ];
        }
      },
      deep: true
    }
  }
}
</script>

<style lang="scss">
</style>
