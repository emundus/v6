<template>
  <div id="form-builder-rules-js-condition" class="self-start w-full">
    <div class="flex justify-between items-center">
      <h2>{{ conditionLabel }}</h2>
      <button v-if="index !== 0" type="button" @click="$emit('remove-condition', index)" class="w-auto">
        <span class="material-icons-outlined text-red-500">close</span>
      </button>
    </div>

    <div>
      <div class="flex items-center mt-2 ml-6">
        <p class="mr-4">{{ translate('COM_EMUNDUS_FORMBUILDER_RULE_IF') }}</p>
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

      <div>
        <div class="flex items-center gap-3">
          <span class="p-2 rounded-lg label-darkblue ml-2 mr-2">{{ translate('COM_EMUNDUS_FORMBUILDER_RULE_OPERATOR_EQUALS') }}</span>
          <span class="p-2 rounded-lg label-darkblue ml-2 mr-2">{{ translate('COM_EMUNDUS_FORMBUILDER_RULE_OPERATOR_NOT_EQUALS') }}</span>
        </div>
      </div>
    </div>


  </div>
</template>

<script>
import formService from '../../../../services/form';

import formBuilderMixin from '../../../../mixins/formbuilder';
import globalMixin from '../../../../mixins/mixin';
import errorMixin from '../../../../mixins/errors';
import Swal from 'sweetalert2';
import Multiselect from 'vue-multiselect';

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
  mixins: [formBuilderMixin, globalMixin, errorMixin],
  data() {
    return {
      loading: false,
    };
  },
  mounted() {
    if (this.page.id) {}
  },
  methods: {
    labelTranslate({ label }) {
      return label.fr;
    }
  },
  computed: {
    conditionLabel() {
      return this.condition.label !== '' ? this.condition.label : `Condition n°${this.index + 1}`;
    }
  }
}
</script>

<style lang="scss">
</style>
