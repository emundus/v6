<template>
  <div id="form-builder-element-properties">
    <div class="em-flex-row em-flex-space-between em-p-16 em-flex-align-start">
      <div>
        <p>{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES") }}</p>
        <span class="em-font-size-14 em-neutral-700-color">{{ element.label[shortDefaultLang] }}</span>
      </div>
      <span class="material-icons-outlined em-pointer" @click="$emit('close')">close</span>
    </div>
    <ul id="properties-tabs" class="em-flex-row em-flex-space-between em-p-16 em-w-90">
      <li
          v-for="tab in publishedTabs"
          :key="tab.id"
          :class="{ 'is-active': tab.active, 'em-w-50': publishedTabs.length == 2, 'em-w-100':  publishedTabs.length == 1}"
          class="em-p-16 em-pointer"
          @click="selectTab(tab)"
      >
        {{ translate(tab.label) }}
      </li>
    </ul>
    <div id="properties">
      <div v-if="tabs[0].active" id="element-parameters" class="em-p-16">
        <label for="element-label">{{ translate('COM_EMUNDUS_FORM_BUILDER_ELEMENT_LABEL') }}</label>
        <input id="element-label" name="element-label" class="em-w-100" type="text" v-model="element.label[shortDefaultLang]"/>
        <div class="em-flex-row em-flex-space-between em-w-100 em-pt-16 em-pb-16">
          <span>{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_UNPUBLISH") }}</span>
          <div class="em-toggle">
            <input type="checkbox" class="em-toggle-check" v-model="isPublished" @click="togglePublish">
            <strong class="b em-toggle-switch"></strong>
            <strong class="b em-toggle-track"></strong>
          </div>
        </div>

        <div class="em-flex-row em-flex-space-between em-w-100 em-pt-16 em-pb-16" v-show="this.element.plugin !== 'display'">
          <span>{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_REQUIRED") }}</span>
          <div class="em-toggle">
            <input type="checkbox" class="em-toggle-check" v-model="element.FRequire" @click="element.FRequire = !element.FRequire;">
            <strong class="b em-toggle-switch"></strong>
            <strong class="b em-toggle-track"></strong>
          </div>
        </div>

        <div class="w-full em-pt-16 em-pb-16" v-show="this.element.plugin == 'panel'">
          <label for="element-default">{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_CONTENT") }}</label>
          <textarea id="element-default" name="element-default" v-model="element.default" class="w-full resize-y"></textarea>
        </div>

        <div class="em-flex-row em-flex-space-between em-w-100 em-pt-16 em-pb-16" v-if="sysadmin">
          <span>{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_HIDDEN") }}</span>
          <div class="em-toggle">
            <input type="checkbox" class="em-toggle-check" v-model="isHidden" @click="toggleHidden">
            <strong class="b em-toggle-switch"></strong>
            <strong class="b em-toggle-track"></strong>
          </div>
        </div>

      </div>
      <div v-if="tabs[1].active" class="em-p-16">
        <FormBuilderElementParams :element="element" :params="params" :key="element.id" :databases="databases" />
      </div>
    </div>
    <div class="em-flex-row em-flex-space-between actions em-m-16">
      <button class="em-primary-button" @click="saveProperties()">
        {{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_SAVE") }}
      </button>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import formBuilderService from '../../services/formbuilder';
import elementParams from '../../../data/form-builder-elements-params.json'
import formBuilderMixin from "../../mixins/formbuilder";

import FormBuilderElementParams from "./FormBuilderElements/FormBuilderElementParams";

export default {
  name: 'FormBuilderElementProperties',
  components: {
    FormBuilderElementParams
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
	mixins: [formBuilderMixin],
  data() {
    return {
      databases: [],
      params: [],
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
          published: true,
        },
        {
          id: 1,
          label: "COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_PARAMETERS",
          active: false,
          published: true,
        }
      ],

      loading: false,
    };
  },
  mounted() {
    this.getDatabases();
    this.paramsAvailable();
  },
  methods: {
    getDatabases(){
      formBuilderService.getDatabases().then(response => {
        if (response.status) {
          this.databases = response.data.data;
        }
      });
    },
    saveProperties() {
      this.loading = true;
      formBuilderService.updateTranslation({value: this.element.id, key: 'element'}, this.element.label_tag, this.element.label);

	    if (['radiobutton', 'checkbox', 'dropdown'].includes(this.element.plugin)) {
		    formBuilderService.getJTEXTA(this.element.params.sub_options.sub_labels).then(response => {
					if (response) {
						this.element.params.sub_options.sub_labels.forEach((label, index) => {
							this.element.params.sub_options.sub_labels[index] = Object.values(response.data)[index];
						});

						formBuilderService.updateParams(this.element).then(response => {
							if (response.status) {
								this.loading = false;
								this.updateLastSave();
								this.$emit('close');
							}
						});
					}
				});
	    } else {
		    formBuilderService.updateParams(this.element).then(response => {
			    if (response.status) {
				    this.loading = false;
				    this.updateLastSave();
				    this.$emit('close');
			    }
		    });
	    }
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
    toggleHidden() {
      this.element.hidden = !this.element.hidden;
      formBuilderService.toggleElementHiddenValue(this.element.id).then(response => {
        if (!response.status) {
          this.element.hidden = !this.element.hidden;
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
    paramsAvailable(){
      if(typeof elementParams[this.element.plugin] !== 'undefined'){
        this.tabs[1].published = true;
        this.params = elementParams[this.element.plugin];
      } else {
        this.tabs[1].active = false;
        this.tabs[0].active = true;
        this.tabs[1].published = false;
      }
    }
  },
  computed: {
    componentType() {
      let type = '';
      switch (this.element.plugin) {
        case 'databasejoin':
          type = this.element.params.database_join_display_type =='radio' ?  'radiobutton' : this.element.params.database_join_display_type;
          break;
        case 'years':
        case 'date':
        case 'birthday':
          type = 'birthday';
          break;
        default:
          type = this.element.plugin;
          break;
      }

      return type;
    },
    isPublished() {
      return !(this.element.publish);
    },
    isHidden() {
      return this.element.hidden;
    },
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
    'element.id': function(value){
      this.paramsAvailable();
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
