<template>
  <div id="form-builder-element-properties">
    <div class="em-flex-row em-flex-space-between">
      <p>{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES") }}</p>
      <span
          class="material-icons em-pointer"
          @click="$emit('close')"
      >
        close
      </span>
    </div>
    <div id="properties">
      <component
          :is="element.plugin"
          :element="element"
          :prid="profile_id"
          :databases="databases"
      ></component>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';

import birthday from '../formClean/Plugin/birthday.vue';
import checkbox from '../formClean/Plugin/checkbox.vue';
import display from '../formClean/Plugin/display.vue';
import dropdown from '../formClean/Plugin/dropdown.vue';
import field from '../formClean/Plugin/field.vue';
import fileupload from '../formClean/Plugin/fileupload.vue';
import radiobutton from '../formClean/Plugin/radiobtn.vue';
import textarea from '../formClean/Plugin/textarea.vue';
import yesno from '../formClean/Plugin/yesno.vue';

export default {
  name: 'FormBuilderElementProperties',
  components: {
    birthday,
    checkbox,
    display,
    dropdown,
    field,
    fileupload,
    radiobutton,
    textarea,
    yesno,
  },
  props: {
    element: {
      type: Object,
      required: true
    },
    profile_id: {
      type: Number,
      required: true
    },
  },
  data() {
    return {
      databases: [],
      elementsNeedingDb: [
          "dropdown",
          "checkbox",
          "radiobutton"
      ]
    };
  },
  mounted() {
    this.getDatabases();
  },
  methods: {
    getDatabases(){
      if (this.elementsNeedingDb.indexOf(this.element.plugin) > -1) {
        formBuilderService.getDatabases().then(response => {
          if (response.status) {
            this.databases = response.data.data;
          }
        });
      }
    },
  }
}
</script>

<style lang="scss">

</style>