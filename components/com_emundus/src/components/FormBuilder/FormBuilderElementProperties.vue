<template>
  <div id="form-builder-element-properties">
    <div class="em-flex-row em-flex-space-between em-p-16">
      <p>{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES") }}</p>
      <span
          class="material-icons em-pointer"
          @click="$emit('close')"
      >
        close
      </span>
    </div>
    <div id="properties">
      <p class="em-p-16">{{ element.label.fr }}</p>
      <div id="element-parameters" class="em-p-16">
        <p>{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_PARAMETERS") }}</p>

        <div class="em-flex-row em-flex-space-between em-w-100">
          <span>{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_UNPUBLISH") }}</span>
          <label class="element-published em-switch">
            <div>
              <input type="checkbox" v-model="isPublished" @click="togglePublish"/>
              <span class="em-slider em-round"></span>
            </div>
          </label>
        </div>

        <div class="em-flex-row em-flex-space-between em-w-100">
          <span>{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_REQUIRED") }}</span>
          <label class="element-required em-switch">
            <div>
              <input type="checkbox" v-model="element.FRequire" @click="element.FRequire = !element.FRequire;"/>
              <span class="em-slider em-round"></span>
            </div>
          </label>
        </div>

      </div>
      <hr/>
      <div class="em-p-16">
        <component
            :is="componentType"
            :element="element"
            :prid="profile_id"
            :databases="databases"
            @subOptions="setElementSubOptions"
        ></component>
      </div>
    </div>
    <div class="em-flex-row em-flex-space-between actions em-m-16">
      <button
        class="em-primary-button"
        @click="saveProperties()"
      >
        {{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_SAVE") }}
      </button>
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
          "radiobutton",
          "databasejoin"
      ],
    };
  },
  mounted() {
    this.getDatabases();
  },
  methods: {
    getDatabases(){
      formBuilderService.getDatabases().then(response => {
        if (response.status) {
          this.databases = response.data.data;
        }
      });
    },
    setElementSubOptions(subOptions) {
      if (typeof this.element.params.sub_options !== 'undefined') {
        this.element.params.sub_options.sub_labels = subOptions.map(value => value.sub_label);
        this.element.params.sub_options.sub_values = subOptions.map(value => value.sub_value);
      }
    },
    saveProperties()
    {
      formBuilderService.updateParams(this.element).then(response => {
        if (response.status) {
          this.$emit('close');
        }
      });
    },
    togglePublish() {
      this.element.publish = !this.element.publish;
      formBuilderService.toggleElementPublishValue(this.element.id).then(response => {
        if (!response.status) {
          this.element.publish = !this.element.publish;
          // TODO: show error
        }
      });
    },
  },
  computed: {
    componentType() {
      let type = '';
      switch (this.element.plugin) {
        case 'databasejoin':
          type = this.element.params.database_join_display_type =='radio' ?  'radiobutton' : this.element.params.database_join_display_type;
          break;
        default:
          type = this.element.plugin;
      }

      return type;
    },
    isPublished() {
      return !(this.element.publish);
    },
  }
}
</script>

<style lang="scss">

</style>