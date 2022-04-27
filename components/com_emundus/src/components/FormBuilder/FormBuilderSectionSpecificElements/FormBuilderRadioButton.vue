<template>
  <div id="form-builder-radio-button">
    <div class="element-option"
      v-for="(option, index) in element.params.sub_options.sub_labels"
      :key="option"
    >
      <input
          type="radio"
          :name="'element-id-' + element.id"
          :value="element.params.sub_options.sub_values[index]"
      > {{ option }}
    </div>
    <div id="add-option">
      <input
          type="radio"
          :name="'element-id-' + element.id"
      >
      <p
          v-model="newOption"
          contenteditable="true"
      >
      </p>
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
  },
  data() {
    return {
      newOption: '',
    };
  },
  created () {
    this.getSubOptionsTranslation();
  },
  methods: {
    getSubOptionsTranslation() {
      this.element.params.sub_options.sub_labels.forEach((label, index) => {
        formBuilderService.getJTEXT(label).then(response => {
          this.element.params.sub_options.sub_labels[index] = response.data;
          this.$forceUpdate();
        });
      });
    }
  }
}
</script>