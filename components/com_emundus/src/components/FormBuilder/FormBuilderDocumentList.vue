<template>
  <div id="form-builder-document-list">
    <div id="required-documents" class="em-w-100 em-mb-32 em-mt-32">
      <p class="em-font-size-24 em-font-weight-800">{{ translate('COM_EMUNDUS_FORM_BUILDER_REQUIRED_DOCUMENTS') }}</p>
      <form-builder-document-list-element
          v-for="(document, index) in requiredDocuments"
          :key="'required-' + document.id"
          :document="document"
          :documentIndex="index + 1"
          :totalDocuments="requiredDocuments.length"
          @edit-document="editDocument(document)"
      >
      </form-builder-document-list-element>
    </div>
    <div id="optional-documents" class="em-w-100 em-mb-32 em-mt-32">
      <p class="em-font-size-24 em-font-weight-800">{{ translate('COM_EMUNDUS_FORM_BUILDER_OPTIONAL_DOCUMENTS') }}</p>
      <form-builder-document-list-element
          v-for="(document, index) in optionalDocuments"
          :key="'optional-' + document.id"
          :document="document"
          :documentIndex="index + 1"
          :totalDocuments="optionalDocuments.length"
          @edit-document="editDocument(document)"
      >
      </form-builder-document-list-element>
    </div>
    <button id="add-document" class="em-secondary-button" @click="addDocument">{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_DOCUMENT') }}</button>
  </div>
</template>

<script>
import FormBuilderDocumentListElement from './FormBuilderDocumentListElement.vue';

import formService from "../../services/form";

export default {
  name: 'FormBuilderDocumentList',
  components: {
    FormBuilderDocumentListElement
  },
  props: {
    profile_id: {
      type: Number,
      required: true
    },
    campaign_id: {
      type: Number,
      required: true
    },
  },
  data () {
    return {
      documents: [],
      closedSection: false,
    }
  },
  created () {
    this.getDocuments();
  },
  methods: {
    getDocuments () {
      formService.getDocuments(this.profile_id).then(response => {
        this.documents = response.data.data;
      });
    },
    addDocument () {
      this.$emit('add-document');
    },
    editDocument (document) {
      this.$emit('edit-document', document);
    },
  },
  computed: {
    requiredDocuments () {
      return this.documents.filter(document => document.mandatory == 1);
    },
    optionalDocuments () {
      return this.documents.filter(document => document.mandatory == 0);
    }
  }
}
</script>

<style lang="scss">
#form-builder-document-list {
  width: calc(100% - 80px);
  margin: 40px 40px;

  #add-document {
    width: fit-content;
    padding: 24px;
    margin: auto;
    background-color: #fff;
  }
}
</style>