<template>
  <div id="form-builder-wysiwig">
    <div v-if="loading" class="em-loader"></div>
    <div v-else>
      <div v-show="!editable" v-html="element.element" :id="element.id" @click="editable = true"></div>
      <transition :name="'slide-down'" type="transition">
        <editor
            v-if="editable"
            :height="'30em'"
            :text="element.default"
            :lang="'fr'"
            :enable_variables="false"
            :id="'editor_' + element.id"
            :key="dynamicComponent"
            v-model="element.default"
            @focusout="updateDisplayText"
        ></editor>
      </transition>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../../services/formbuilder';
import Editor from "../../editor";

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
    Editor
  },
  data() {
    return {
      loading: false,

      editable: false,
      dynamicComponent: 0,
    };
  },
  created() {
  },
  methods: {
    updateDisplayText(value) {
      this.editable = false;
      formBuilderService.updateDefaultValue(this.$props.element.id, value).then((response) => {
        this.$emit('update-element');
      })
    }
  },
  watch: {}
}
</script>

<style lang="scss">

</style>
