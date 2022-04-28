<template>
  <div id="form-builder-radio-button">
    <div v-if="loading" class="em-loader">

    </div>
    <div v-else>
      <div class="element-option"
           v-for="(option, index) in element.params.sub_options.sub_labels"
           :key="option"
      >
        <input
            :type="type"
            :name="'element-id-' + element.id"
            :value="element.params.sub_options.sub_values[index]"
        > {{ option }}
      </div>
      <div id="add-option" class="em-flex-row em-flex-start em-s-justify-content-center">
        <input
            :type="type"
            :name="'element-id-' + element.id"
        >
        <input
            type="text"
            class="editable-data editable-data-input"
            v-model="newOption"
            @keyup.enter="addOption"
            @focusout="addOption"
            :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_ADD_OPTION')"
        >
      </div>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../../services/formbuilder';

export default {
  props: {
    element: {
      type: Object,
      required: true
    },
    type: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      loading: false,
      newOption: '',
    };
  },
  created () {
    this.getSubOptionsTranslation();
  },
  methods: {
    async getSubOptionsTranslation() {
      for (let i = 0; i < this.element.params.sub_options.sub_labels.length; i++) {
        this.loading = true;

        formBuilderService.getJTEXT(this.element.params.sub_options.sub_labels[i]).then(response => {
          this.element.params.sub_options.sub_labels[i] = response.data;
          this.$forceUpdate();

          if (i+1 == this.element.params.sub_options.sub_labels.length) {
            this.loading = false;
          }
        });
      }
    },
    addOption() {
      if (this.newOption.length) {
        this.element.params.sub_options.sub_labels.push(this.newOption);
        this.element.params.sub_options.sub_values.push(null);
        this.newOption = '';

        formBuilderService.updateParams(this.element);
      }
    },
  },
  watch: {
    "element.params.sub_options.sub_labels": {
      handler: function (newValue) {
        this.getSubOptionsTranslation();
      },
    },
  }
}
</script>

<style lang="scss">
.editable-data-input {
  padding: 0 !important;
  height: auto !important;
  border: none !important;

  &:focus {
    outline: none !important;
  }
}
</style>