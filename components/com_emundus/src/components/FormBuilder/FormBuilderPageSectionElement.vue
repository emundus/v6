<template>
  <div
      class="form-builder-page-section-element"
      :class="{
        'unpublished': !element.publish,
      }"
      @click="$emit('open-element-properties')"
  >
    <span
        v-if="element.label_value && element.labelsAbove != 2"
        ref="label"
        class="element-title editable-data"
        contenteditable="true"
        @focusout="updateLabel"
    >
      {{ element.label.fr }}
    </span>
    <div class="element-field">
      <span v-html="element.element" :id="element.id">
      </span>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';

export default {
  props: {
    element: {
      type: Object,
      default: {}
    },
  },
  methods: {
    updateLabel()
    {
        this.element.label.fr = this.$refs.label.innerText;
        formBuilderService.updateTranslation({value: this.element.id, key: 'element'}, this.element.label_tag, this.element.label);
    },
    updateElement()
    {
      formBuilderService.updateParams(this.element);
    }
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
  }


  .element-title {
    margin-bottom: 24px !important;
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