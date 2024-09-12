<template>
  <div id="form-builder-wysiwig">
    <div v-if="loading" class="em-loader"></div>
    <div v-else>
      <div v-show="!editable" v-html="element.element" :id="element.id" @click="editable = true"></div>
      <transition :name="'slide-down'" type="transition">
        <editor-quill
            v-if="editable"
            :height="'30em'"
            :text="element.default"
            :enable_variables="false"
            :id="'editor_' + element.id"
            :key="dynamicComponent"
            v-model="element.default"
            @focusout="updateDisplayText"
        ></editor-quill>
      </transition>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../../services/formbuilder';
import EditorQuill from "@/components/editorQuill";

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
    EditorQuill
  },
  data() {
    return {
      loading: false,

      editable: false,
      dynamicComponent: 0,
    };
  },
  created () {},
  methods: {
    updateDisplayText(value){
      this.editable = false;
      formBuilderService.updateDefaultValue(this.$props.element.id,value).then((response) => {
        this.$emit('update-element');
      })
    }
  },
  watch: {}
}
</script>

<style lang="scss">

</style>
