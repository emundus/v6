<template>
  <div id="form-builder-radio-button">
    <div v-if="loading" class="em-loader"></div>
    <div v-else>
      <draggable
          v-model="arraySubValues"
          handle=".handle-options"
          @end="updateOrder">
        <div class="element-option em-flex-row em-flex-space-between em-mt-8 em-mb-8" v-for="(option, index) in arraySubValues" :key="option" @mouseover="optionHighlight = index;" @mouseleave="optionHighlight = null">
          <div class="em-flex-row em-w-100">
            <div class="em-flex-row">
              <span class="icon-handle" :style="optionHighlight === index ? 'opacity: 1' : 'opacity: 0'">
                <span class="material-icons-outlined handle-options em-grab">drag_indicator</span>
              </span>
            </div>
            <input v-if="type !== 'dropdown'" :type="type" :name="'element-id-' + element.id" :value="element.params.sub_options.sub_values[index]">
            <div v-else>{{ index+1 }}. </div>
            <input
                type="text"
                class="editable-data editable-data-input em-ml-4 em-w-100"
                v-model="option.sub_label"
                @keyup.enter="updateOption(index, option.sub_label)"
                @focusout="updateOption(index, option.sub_label)"
                :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_ADD_OPTION')">
          </div>
          <div class="em-flex-row">
            <span class="material-icons-outlined em-pointer" @click="removeOption(index)" :style="optionHighlight === index ? 'opacity: 1' : 'opacity: 0'">close</span>
          </div>
        </div>
      </draggable>
      <div id="add-option" class="em-flex-row em-flex-start em-s-justify-content-center">
        <span class="icon-handle" style="opacity: 0">
          <span class="material-icons-outlined handle-options">drag_indicator</span>
        </span>
        <input v-if="type !== 'dropdown'" :type="type" :name="'element-id-' + element.id">
        <div v-else>{{ element.params.sub_options.sub_labels.length+1 }}. </div>
        <input
          type="text"
          class="editable-data editable-data-input em-ml-4 em-w-100"
          v-model="newOption"
          @keyup.enter="addOption"
          @focusout="addOption"
          :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_ADD_OPTION')">
      </div>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../../services/formbuilder';
import draggable from "vuedraggable";

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
  components: {
    draggable,
  },
  data() {
    return {
      loading: false,
      newOption: '',
      arraySubValues: [],

      optionHighlight: null,
    };
  },
  created () {
    this.getSubOptionsTranslation();
  },
  methods: {
    async getSubOptionsTranslation() {
      this.loading = true;

      formBuilderService.getJTEXTA(this.element.params.sub_options.sub_labels).then(response => {
        this.element.params.sub_options.sub_labels.forEach((label, index) => {
          this.element.params.sub_options.sub_labels[index] = Object.values(response.data)[index];
        });

        this.element.params.sub_options.sub_values.forEach((value, i) => {
          let object = {
            'sub_value' : value,
            'sub_label' : this.element.params.sub_options.sub_labels[i],
          };
          this.arraySubValues.push(object);
        });

        this.$forceUpdate();
        this.loading = false;
      });
    },
    addOption() {
      if (this.newOption.length) {
        this.element.params.sub_options.sub_labels.push(this.newOption);
        this.element.params.sub_options.sub_values.push(null);

        let object = {
          'sub_value' : null,
          'sub_label' : this.newOption,
        };
        this.arraySubValues.push(object);

        this.newOption = '';
        formBuilderService.updateParams(this.element);
      }
    },
    updateOption(index, option) {
      this.element.params.sub_options.sub_labels[index] = option;
      formBuilderService.updateParams(this.element);
    },
    updateOrder() {
      let new_sub_values = [];
      let new_sub_labels = [];
      this.arraySubValues.forEach((value, i) => {
        new_sub_values.push(value.sub_value);
        new_sub_labels.push(value.sub_label);
      });

      this.element.params.sub_options.sub_labels = new_sub_labels;
      this.element.params.sub_options.sub_values = new_sub_values;
      formBuilderService.updateParams(this.element);
    },
    removeOption(index) {
      this.arraySubValues.splice(index,1);
      this.element.params.sub_options.sub_labels.splice(index,1);
      this.element.params.sub_options.sub_values.splice(index,1);

      formBuilderService.updateParams(this.element);
    }
  },
  watch: {
    "element.params.sub_options.sub_labels": {
      handler: function (newValue) {
        // TODO: find a better way to do this
        //this.getSubOptionsTranslation();
      },
    },
  }
}
</script>

<style lang="scss">
.editable-data-input {
  padding: 0 !important;
  height: auto !important;
  border: unset !important;
  border-bottom: solid 2px transparent !important;
  border-radius: 0 !important;

  &:focus {
    outline: none !important;
    box-shadow: unset !important;
    border-bottom: solid 2px #20835F !important;
    border-radius: 0 !important;
  }

  &:hover{
    box-shadow: unset !important;
    border-bottom: solid 2px rgba(32, 131, 95, 0.87) !important;
    border-radius: 0 !important;
  }
}
.element-option,#add-option{
  .icon-handle{
    height: 18px;
    width: 18px;
    transition: all .3s;
  }
}
</style>
