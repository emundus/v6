<template>
  <div id="form-builder-document-list">
    <div id="required-documents" class="em-w-100 em-mb-32 em-mt-32">
      <p>{{ translate('REQUIRED_DOCUMENTS') }}</p>
      <form-builder-document-list-element
          v-for="(document, index) in requiredDocuments"
          :key="'required-' + document.id"
          :document="document"
          :documentIndex="index + 1"
          :totalDocuments="requiredDocuments.length"
      >
      </form-builder-document-list-element>
    </div>
    <div id="optional-documents" class="em-w-100 em-mb-32 em-mt-32">
      <p>{{ translate('OPTIONAL_DOCUMENTS') }}</p>
      <form-builder-document-list-element
          v-for="(document, index) in optionalDocuments"
          :key="'optional-' + document.id"
          :document="document"
          :documentIndex="index + 1"
          :totalDocuments="optionalDocuments.length"
      >
      </form-builder-document-list-element>
    </div>
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
}
</style>