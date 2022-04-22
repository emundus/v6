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
    <ul id="properties-tabs" class="em-flex-row em-flex-space-between em-p-16 em-w-90">
      <li
          v-for="tab in tabs"
          :key="tab.id"
          :class="{ 'is-active': tab.active }"
          class="em-p-16 em-pointer"
          @click="selectTab(tab)"
      >
        {{ translate(tab.label) }}
      </li>
    </ul>
    <div id="properties">
      <div
          v-if="tabs[0].active"
          id="element-parameters"
          class="em-p-16"
      >
        <div class="em-flex-row em-flex-space-between em-w-100 em-pt-16 em-pb-16">
          <span>{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_UNPUBLISH") }}</span>
          <div class="em-toggle">
            <input type="checkbox" class="em-toggle-check" v-model="isPublished" @click="togglePublish">
            <strong class="b em-toggle-switch"></strong>
            <strong class="b em-toggle-track"></strong>
          </div>
        </div>

        <div class="em-flex-row em-flex-space-between em-w-100 em-pt-16 em-pb-16">
          <span>{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_REQUIRED") }}</span>
          <div class="em-toggle">
            <input type="checkbox" class="em-toggle-check" v-model="element.FRequire" @click="element.FRequire = !element.FRequire;">
            <strong class="b em-toggle-switch"></strong>
            <strong class="b em-toggle-track"></strong>
          </div>
        </div>

      </div>
      <div
          v-if="tabs[1].active"
          class="em-p-16"
      >
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
      tabs: [
        {
          id: 0,
          label: "COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_GENERAL",
          active: true,
        },
        {
          id: 1,
          label: "COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_PARAMETERS",
          active: false,
        }
      ]
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
    selectTab(tab) {
      this.tabs.forEach(t => {
        t.active = false;
      });
      tab.active = true;
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
#properties-tabs {
  list-style-type: none;
  margin: auto;
  align-items: center;

  li {
    text-align: center;
    width: 50%;
    border-bottom: 2px solid transparent;
    transition: all .3s;

    &.is-active {
      border-bottom: 2px solid black;
    }
  }

}
</style>