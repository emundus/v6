<template>
  <div id="form-builder-create-document">
    <div class="em-flex-row em-flex-space-between em-p-16">
      <p class="em-font-weight-500">{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_PROPERTIES") }}</p>
      <span class="material-icons em-pointer" @click="$emit('close')">close</span>
    </div>
    <!--<div class="em-p-16">
      <label>{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_MODEL") }}</label>
      <select id="document-model" @change="selectModel" v-if="current_document === null">
        <option value="none"></option>
        <option v-for="(model, index) in models" :value="model.id">{{ model.name.fr }}</option>
      </select>

      <label>{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_DESCRIPTION") }}</label>
      <editor
          v-model="document.description.fr"
          :text="document.description.fr"
          :lang="'fr'"
          :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_DOCUMENT_DESCRIPTION_PLACEHOLDER')"
          :id="'editor'"
          :height="'200'"
      ></editor>
    </div>-->
    <ul id="properties-tabs" class="em-flex-row em-flex-space-between em-p-16 em-w-90">
      <li
          v-for="tab in tabs"
          :key="tab.id"
          :class="{ 'is-active': tab.active, 'is-no-active': !tab.active }"
          class="em-p-16 em-pointer em-font-weight-500"
          @click="selectTab(tab)"
      >
        {{ translate(tab.label) }}
      </li>
    </ul>

    <div id="general-properties" class="em-p-16" v-if="tabs[0].active">
      <div class="em-mb-16 em-flex-row em-flex-space-between">
        <label class="em-font-weight-400">{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_REQUIRED") }}</label>
        <div class="em-toggle">
          <input type="checkbox" class="em-toggle-check" v-model="document.mandatory" @click="document.mandatory != document.mandatory">
          <strong class="b em-toggle-switch"></strong>
          <strong class="b em-toggle-track"></strong>
        </div>
      </div>

      <div class="em-mb-16">
        <label for="title" class="em-font-weight-400">{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_NAME") }}</label>
        <multiselect
            v-model="document.type"
            label="value"
            track-by="id"
            id="title"
            :options="models"
            :multiple="false"
            :taggable="false"
            select-label=""
            selected-label=""
            deselect-label=""
            :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_DOCUMENT_PROPERTIES_SELECT_TYPE')"
            :close-on-select="true"
            :clear-on-select="false"
            :searchable="true"
            :allow-empty="true"
        ></multiselect>
<!--        <input type="text" class="em-w-100" id="title" v-model="document.name.fr">-->
      </div>

      <div class="em-mb-16">
        <label class="em-font-weight-400">{{ translate('COM_EMUNDUS_FORM_BUILDER_DOCUMENT_TYPES') }}</label>
        <div v-for="(filetype, index) in fileTypes" :key="filetype.value" class="em-flex-row em-mb-4">
          <input
            type="checkbox"
            name="filetypes"
            :id="filetype.value"
            :value="filetype.value"
            v-model="document.selectedTypes[filetype.value]"
            @change="checkFileType"
          >
          <label :for="filetype.value" class="em-font-weight-400 em-mb-0-important"> {{ translate(filetype.title) }} ({{ filetype.value }})</label>
        </div>
      </div>

      <div class="em-mb-16">
        <label for="nbmax" class="em-font-weight-400">{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_NBMAX") }}</label>
        <input type="number" id="nbmax" class="em-w-100" v-model="document.nbmax">
      </div>
    </div>

    <div id="advanced-properties" class="em-p-16" v-if="tabs[1].active">
<!--      <label>{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_CATEGORY") }}</label>
      <select id="document-category">
        <option v-for="category in categories" :value="category.id">{{ category.title }}</option>
      </select>

      <label for="min-pdf-pages">{{ translate("COM_EMUNDUS_FORM_BUILDER_MIN_PDF_PAGES") }}</label>
      <input id="min-pdf-pages" type="number" min="0">

      <label for="max-pdf-pages">{{ translate("COM_EMUNDUS_FORM_BUILDER_MAX_PDF_PAGES") }}</label>
      <input id="max-pdf-pages" type="number" min="0">

      <label>{{ translate("COM_EMUNDUS_FORM_BUILDER_IMAGES_DIMENSIONS") }}</label>
      <p></p>
      <div class="em-flex-column">
        <div>
          <p>{{ translate("COM_EMUNDUS_FORM_BUILDER_WIDTH_DIMENSIONS") }}</p>
          <div class="em-flex-row">
            <input type="number" id="min-width" min="0" :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_MINIMUM')">
            <input type="number" id="max-width" min="0" :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_MAXIMUM')">
          </div>
        </div>
        <div>
          <p>{{ translate("COM_EMUNDUS_FORM_BUILDER_HEIGHT_DIMENSIONS") }}</p>
          <div class="em-flex-row">
            <input type="number" id="min-height" min="0" :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_MINIMUM')">
            <input type="number" id="max-height" min="0" :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_MAXIMUM')">
          </div>
        </div>
      </div>-->
    </div>

    <div class="em-p-16">
      <button class="em-primary-button"  @click="updateDocument">{{ translate('COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_SAVE') }}</button>
    </div>
  </div>
</template>

<script>
import formService from '../../services/form';
import campaignService from '../../services/campaign';
import editor from '../editor.vue';
import Multiselect from 'vue-multiselect';

export default {
  name: 'FormBuilderCreateDocument',
  props: {
    profile_id: {
      type: Number,
      required: true
    },
    current_document: {
      type: Object,
      default: null
    }
  },
  components: {
    editor,
    Multiselect
  },
  data() {
    return {
      models: [],
      document: {
        id: null,
        type: {},
        mandatory: true,
        nbmax: 1,
        description: {
          fr: '',
          en: ''
        },
        name: {
          fr: '',
          en: ''
        },
        selectedTypes: {},
        minResolution: {
          width: 0,
          height: 0
        },
        maxResolution: {
          width: 0,
          height: 0
        }
      },
      fileTypes: [],
      activeTab: 'general',
      tabs: [
        {
          id: 0,
          label: "COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_GENERAL",
          active: true,
        },
        {
          id: 1,
          label: "COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_ADVANCED",
          active: false,
        }
      ]
    };
  },
  created(){
    this.getDocumentModels();
    this.getFileTypes();
  },
  methods: {
    selectTab(tab) {
      this.tabs.forEach(t => {
        t.active = false;
      });
      tab.active = true;
    },
    getDocumentModels() {
      formService.getDocumentModels().then(response => {
        if (response.status) {
          this.models = response.data;

          if (this.current_document) {
            this.selectModel({
              target: {
                value: this.current_document.docid
              }
            });
          }
        }
      });
    },
    getFileTypes() {
      this.fileTypes = require('../../data/form-builder-filetypes.json');
      this.fileTypes.forEach(filetype => {
        this.document.selectedTypes[filetype.value] = false;
      });
    },
    checkFileType(event) {
      this.document.selectedTypes[event.target.value] = event.target.checked;
    },
    selectModel(event) {
      if (event.target.value !== 'none') {
        const model = this.models.find(model => model.id == event.target.value);
        this.document.id = model.id;
        this.document.type = model;
        this.document.mandatory = model.mandatory;
        this.document.nbmax = model.nbmax;
        this.document.description = model.description;
        this.document.name = model.name;

        this.fileTypes.forEach(filetype => {
          this.document.selectedTypes[filetype.value] = false;
        });

        let types = model.allowed_types.split(';');
        types.forEach((type) => {
          if(['pdf'].includes(type)) {
            this.document.selectedTypes['pdf'] = true;
          }
          if(['pdf'].includes(type)) {
            this.document.selectedTypes['pdf'] = true;
          }
          if(['jpeg','jpg','png','gif'].includes(type)) {
            this.document.selectedTypes['jpeg;jpg;png;gif'] = true;
          }
          if(['doc','docx','odt','ppt','pptx'].includes(type)) {
            this.document.selectedTypes['doc;docx;odt;ppt;pptx'] = true;
          }
          if(['xls','xlsx','odf'].includes(type)) {
            this.document.selectedTypes['xls;xlsx;odf'] = true;
          }
        });
      }
    },
    updateDocument()
    {
      const create = this.current_document ? false : true;
      const types = [];
      Object.entries(this.document.selectedTypes).forEach(([key, value]) => {
        if (value) {
          types.push(key);
        }
      });

      const data = {
        document: JSON.stringify(this.document),
        types: JSON.stringify(types),
        pid: this.profile_id,
        isModeleAndUpdate: true
      };

      if (this.current_document !== null) {
        data.did = this.current_document.docid;
      }

      campaignService.updateDocument(data, create).then(response => {
        if (response.status) {
          this.$emit('documents-updated');
        } else {
          this.$emit('close');
        }
      });
    }
  },
  watch: {
    current_document(newValue) {
      if (newValue) {
        this.selectModel({
          target: {
            value: newValue.docid
          }
        });
      }
    }
  }
}
</script>
