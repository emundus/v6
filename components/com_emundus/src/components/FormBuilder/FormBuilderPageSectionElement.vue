<template>
  <div
      class="form-builder-page-section-element"
      :class="{
        'unpublished': !element.publish,
      }"
      @click="$emit('open-element-properties')"
  >
    <div class="em-flex-row em-flex-space-between em-w-100 em-mb-16">
      <span
          v-if="element.label_value && element.labelsAbove != 2"
          ref="label"
          class="element-title editable-data"
          contenteditable="true"
          @focusout="updateLabel"
      >
        {{ element.label.fr }}
      </span>
      <span id="delete-element" class="material-icons em-red-500-color em-pointer" @click="deleteElement">delete</span>
    </div>
    <div class="element-field">
      <form-builder-radio-button v-if="element.plugin == 'radiobutton'" :element="element"></form-builder-radio-button>
      <span v-else v-html="element.element" :id="element.id">
      </span>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';
import formBuilderMixin from "../../mixins/formbuilder";
import FormBuilderRadioButton from "./FormBuilderSectionSpecificElements/FormBuilderRadioButton";

export default {
  components: {
    FormBuilderRadioButton
  },
  props: {
    element: {
      type: Object,
      default: {}
    },
  },
  mixins: [formBuilderMixin],
  methods: {
    updateLabel()
    {
      this.element.label.fr = this.$refs.label.innerText;
      formBuilderService.updateTranslation({value: this.element.id, key: 'element'}, this.element.label_tag, this.element.label);
      this.updateLastSave();
    },
    updateElement()
    {
      formBuilderService.updateParams(this.element);
      this.updateLastSave();
    },
    deleteElement() {
      this.swalConfirm(
          this.translate("COM_EMUNDUS_FORM_BUILDER_DELETE_ELEMENT"),
          this.element.label.fr,
          this.translate("COM_EMUNDUS_FORM_BUILDER_DELETE_ELEMENT_CONFIRM"),
          this.translate("JNO"),
          () => {
            formBuilderService.deleteElement(this.element.id);
            this.$emit('delete-element', this.element.id);
            this.updateLastSave();
          }
      );
    },
  }
}
</script>

<style lang="scss">
.form-builder-page-section-element {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: flex-start;
  margin: 12px 0;
  padding: 12px;
  border-radius: 4px;
  transition: 0.3s all;
  border: 1px solid white;

  &.unpublished {
    opacity: 0.5;
  }

  &:hover {
    border: 1px solid black;

    #delete-element {
      display: block;
      opacity: 1;
      pointer-events: all;
    }
  }

  #delete-element {
    transition: 0.3s all;
    opacity: 0;
    pointer-events: none;
  }

  .element-field {
    width: 100%;
  }

  .element-required {
    width: 48px;
    height: 24px;
    margin-top:15px;

    input:checked + .em-slider:before {
      transform: translateX(22px);
    }

    .em-slider {
      border-radius: 24px;

      &::before {
        height: 14px;
        width: 14px;
        bottom: 5px;
      }
    }
  }
}
</style>