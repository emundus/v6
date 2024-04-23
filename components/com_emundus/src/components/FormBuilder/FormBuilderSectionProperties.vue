<template>
  <div id="form-builder-element-properties">
    <div class="em-flex-row em-flex-space-between em-p-16">
      <p>{{ translate("COM_EMUNDUS_FORM_BUILDER_SECTION_PROPERTIES") }}</p>
      <span class="material-icons-outlined em-pointer" @click="$emit('close')">close</span>
    </div>
    <ul id="properties-tabs" class="em-flex-row em-flex-space-between em-p-16 em-w-90">
      <li
          v-for="tab in publishedTabs"
          :key="tab.id"
          :class="{ 'is-active': tab.active, 'em-w-50': publishedTabs.length == '2', 'em-w-100': publishedTabs.length == 1 }"
          class="em-p-16 em-pointer"
          @click="selectTab(tab)"
      >
        {{ translate(tab.label) }}
      </li>
    </ul>
    <div id="properties">
      <div v-if="tabs[0].active" id="section-parameters" class="em-p-16">
        <label for="section-label">{{ translate('COM_EMUNDUS_FORM_BUILDER_SECTION_LABEL') }}</label>
        <input id="section-label" name="section-label" class="em-w-100" type="text" v-model="section_tmp.label"/>
      </div>
      <div v-if="tabs[1].active" class="em-p-16">
        <form-builder-section-params :params="params"  :repetable=this.$data.repetable :section="section_tmp"></form-builder-section-params>
      </div>
    </div>
    <div class="em-flex-row em-flex-space-between actions em-m-16">
      <button class="em-primary-button" @click="saveProperties()">
        {{ translate("COM_EMUNDUS_FORM_BUILDER_SECTION_PROPERTIES_SAVE") }}
      </button>
    </div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';
import sectionParams from '../../../data/form-builder-groups-params.json'
import FormBuilderSectionParams from "./FormBuilderSections/FormBuilderSectionParams";


export default {
  name: 'FormBuilderSectionProperties',
  components: {FormBuilderSectionParams},
  props: {
    section: {
      type: Object,
      required: true
    },
    profile_id: {
      type: Number,
      required: true
    },
    context : {
      type: String,
      default: '',
      required: false
    }
  },
  data() {
    return {
      section_tmp: {},
      repetable: true,
      params: [],
      tabs: [
        {
          id: 0,
          label: "COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_GENERAL",
          active: false,
          published: false,
        },
        {
          id: 1,
          label: "COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_PARAMETERS",
          active: true,
          published: true,
        }
      ]
    };
  },
  created() {
	  this.paramsAvailable();
	  this.getSection();
    this.checkIfRepetable();
    if(this.context === "delete"){
      this.$emit('stopDelete')
      this.saveProperties()
    }
  },
  methods: {
    saveProperties() {
      console.log("jojo");
      formBuilderService.updateGroupParams(this.section_tmp.id,this.section_tmp.params, this.shortDefaultLang).then(() => {
        this.$emit('close');
      });
    },
    toggleHidden() {
      this.section_tmp.params.hidden = !this.section_tmp.hidden;
    },
    selectTab(tab) {
      this.tabs.forEach(t => {
        t.active = false;
      });
      tab.active = true;
    },
    paramsAvailable(){
      if(typeof sectionParams['parameters'] !== 'undefined'){
        this.tabs[1].published = true;
        this.params = sectionParams['parameters'];
      } else {
        this.tabs[1].active = false;
        this.tabs[0].active = true;
        this.tabs[1].published = false;
      }
    },
    getSection(){
      formBuilderService.getSection(this.$props.section.group_id).then((response) => {
        this.section_tmp = response.data.group;
      });
    },
    checkIfRepetable(){
      for (let key in this.section.elements) {
        if (this.section.elements.hasOwnProperty(key)) {
          let element = this.section.elements[key];
          if (element.plugin === 'emundus_fileupload') {
            this.repetable = false;
            break;
          }else {
            this.repetable = true;
          }
        }
      }
    }
  },
  computed: {
    sysadmin: function(){
      return parseInt(this.$store.state.global.sysadminAccess);
    },
	  publishedTabs() {
			return this.tabs.filter((tab) => {
				return tab.published;
			});
	  }
  },
  watch: {
    section: function(){
      this.paramsAvailable();
      this.getSection();
      this.checkIfRepetable();
    }
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
    border-bottom: 2px solid #EDEDED;
    transition: all .3s;

    &.is-active {
      border-bottom: 2px solid black;
    }
  }

}
</style>
