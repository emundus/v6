<template>
  <div class="form-builder-page-section-element">
    <span
        v-if="element.label_value && element.labelsAbove != 2"
        v-html="element.label_value"
    ></span>
    <div class="element-field">
      <date-picker
          v-if="element.plugin == 'birthday'"
          :config="{
            format: 'DD/MM/YYYY',
            useCurrent: false
          }"
          :v-model="element.value"
      ></date-picker>
    </div>
  </div>
</template>

<script>
import DatePicker from "vue-bootstrap-datetimepicker";

export default {
  components: {
    DatePicker
  },
  props: {
    element: {
      type: Object,
      default: () => ({})
    },
  },
  mounted() {
    console.log(this.element.plugin);
  },
}
</script>