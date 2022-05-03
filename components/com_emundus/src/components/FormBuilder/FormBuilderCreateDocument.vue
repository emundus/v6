<template>
  <div id="form-builder-create-document">
    <div class="em-flex-row em-flex-space-between em-p-16">
      <p>{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_PROPERTIES") }}</p>
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
      <li @click="activeTab = 'general'">{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_GENERAL") }}</li>
      <li @click="activeTab = 'advanced'">{{ translate("COM_EMUNDUS_FORM_BUILDER_ELEMENT_PROPERTIES_ADVANCED") }}</li>
    </ul>
    <section id="general-properties" v-if="activeTab === 'general'">
      <label for="title">{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_NAME") }}</label>
      <input type="text" id="title" v-model="document.name.fr">

      <label>{{ translate('COM_EMUNDUS_FORM_BUILDER_DOCUMENT_TYPES') }}</label>
      <div v-for="(filetype, index) in fileTypes" :key="filetype.value">
        <input
          type="checkbox"
          name="filetypes"
          :id="filetype.value"
          :value="filetype.value"
          @change="checkFileType"
        >
        <label :for="filetype.value"> {{ translate(filetype.title) }} ({{ filetype.value }})</label>
      </div>

      <label for="nbmax">{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_NBMAX") }}</label>
      <input type="number" id="nbmax" v-model="document.nbmax">

      <label>{{ translate("COM_EMUNDUS_FORM_BUILDER_REQUIRED") }}</label>
      <div class="em-toggle">
        <input type="checkbox" class="em-toggle-check" v-model="document.mandatory" @click="document.mandatory != document.mandatory">
        <strong class="b em-toggle-switch"></strong>
        <strong class="b em-toggle-track"></strong>
      </div>
    </section>
    <section id="advanced-properties" v-if="activeTab === 'advanced'">
      <label>{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_CATEGORY") }}</label>
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
      </div>
    </section>
    <button class="em-primary-button"  @click="updateDocument">{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_DOCUMENT') }}</button>
  </div>
</template>

<script>
import formService from '../../services/form';
import campaignService from '../../services/campaign';
import editor from '../editor.vue';

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
    editor
  },
  data() {
    return {
      models: [],
      document: {
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
    };
  },
  created(){
    this.getDocumentModels();
    this.getFileTypes();
  },
  methods: {
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
        this.document.mandatory = model.mandatory;
        this.document.nbmax = model.nbmax;
        this.document.description = model.description;
        this.document.name = model.name;
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