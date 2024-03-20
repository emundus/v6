<template>
  <div id="form-builder-rules-js-action" class="self-start w-full">
    <div class="flex justify-between items-center">
      <h2>{{ actionLabel }}</h2>
      <button v-if="index !== 0" type="button" @click="$emit('remove-action', index)" class="w-auto">
        <span class="material-icons-outlined text-red-500">close</span>
      </button>
    </div>

    <div class="mt-4 flex ml-4">
      <p class="mr-4 mt-3 font-bold">{{ translate('COM_EMUNDUS_FORMBUILDER_RULE_THEN') }}</p>

      <div class="flex flex-col w-full ml-2">
        <div class="flex items-center">
          <div class="form-group w-full">
            <select class="w-full" v-model="action.action">
              <option v-for="action in actions" :value="action.value">{{ translate(action.label) }}</option>
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
                :options="elements"
                :multiple="true"
                :taggable="false"
                select-label=""
                selected-label=""
                deselect-label=""
                :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_RULE_SELECT_FIELDS')"
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
        { id: 1, label: 'COM_EMUNDUS_FORMBUILDER_RULE_ACTION_SHOW', value: 'show' },
        { id: 2, label: 'COM_EMUNDUS_FORMBUILDER_RULE_ACTION_HIDE', value: 'hide' }
      ],
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
    actionLabel() {
      return `Action n°${this.index + 1}`;
    }
  },
  watch: {
    'action.action': {
      handler: function (val) {
      },
      deep: true
    }
  }
}
</script>

<style lang="scss">
</style>
