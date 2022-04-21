<template>
  <div id="form-builder-create-document">
    <div class="em-flex-row em-flex-space-between em-p-16">
      <p>{{ translate("COM_EMUNDUS_FORM_BUILDER_CREATE_DOCUMENT") }}</p>
      <span
          class="material-icons em-pointer"
          @click="$emit('close')"
      >
        close
      </span>
    </div>
    <form class="em-p-16">
      <label>{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_MODEL") }}</label>
      <select id="document-model" @select="selectModel">
        <option v-for="(document, index) in models" :value="document.id">{{ document.name.fr }}</option>
      </select>

      <label>{{ translate("COM_EMUNDUS_FORM_BUILDER_REQUIRED") }}</label>
      <div class="em-toggle">
        <input type="checkbox" class="em-toggle-check" v-model="selectedDocument.mandatory" @click="selectedDocument.mandatory != selectedDocument.mandatory">
        <strong class="b em-toggle-switch"></strong>
        <strong class="b em-toggle-track"></strong>
      </div>

      <label for="title">{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_NAME") }}</label>
      <input type="text" id="title" v-model="selectedDocument.name.fr">

      <label>{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_DESCRIPTION") }}</label>
      <editor
          v-model="selectedDocument.description.fr"
          :text="selectedDocument.description.fr"
          :lang="'fr'"
          :placeholder="translate('COM_EMUNDUS_FORM_BUILDER_DOCUMENT_DESCRIPTION_PLACEHOLDER')"
          :id="'editor'"
          :height="'200'"
      ></editor>

      <label for="nbmax">{{ translate("COM_EMUNDUS_FORM_BUILDER_DOCUMENT_NBMAX") }}</label>
      <input type="number" id="nbmax" v-model="selectedDocument.nbmax">

      <label>{{ translate('COM_EMUNDUS_FORM_BUILDER_DOCUMENT_TYPES') }}</label>
      <!-- checkboxes from filetypes -->
      <div
          v-for="(filetype, index) in fileTypes"
          :key="filetype.value"
      >
        <input
            type="checkbox"
            name="filetypes"
            :id="filetype.value"
            :value="filetype.value"
            v-model="selectedDocument.selectedTypes"
        >
        <label :for="filetype.value"> {{ translate(filetype.title) }} </label>

      </div>

      <input type="submit" @submit="updateDocument">
    </form>
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
  },
  components: {
    editor
  },
  data() {
    return {
      selectedDocument: {
        mandatory: true,
        nbmax: 1,
        description: {
          fr: ''
        },
        name: {
          fr: ''
        },
        selectedTypes: [],
      },
      fileTypes: [],
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
        }
      });
    },
    getFileTypes() {
      this.fileTypes = require('../../data/form-builder-filetypes.json');
      this.fileTypes.forEach(filetype => {
        this.selectedDocument.selectedTypes[filetype.value] = false;
      });
    },
    selectModel(event) {
      let model = this.models.find(model => model.id == event.target.value);
      this.title = model.name.fr;
      this.description = model.description.fr;
      this.maxFiles = model.nbmax;
      this.required = model.required;
    },
    updateDocument()
    {
      const data = {
        document: this.selectedDocument,
        types: this.selectedFileTypes,
        pid: this.profile_id,
        did: 1,
        isModeleAndUpdate: false
      };

      campaignService.updateDocument(data).then(response => {
        if (response.status) {

        }
      });
    }
  }
}
</script>