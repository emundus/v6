<template>
  <div id="form-builder-documents">
    <div id="form-builder-title" class="em-pointer em-flex-row em-flex-space-between em-p-16" @click="$emit('show-documents')">
      <span>{{ translate('COM_EMUNDUS_FORM_BUILDER_EVERY_DOCUMENTS') }}</span>
      <span id="add-document" class="material-icons-outlined em-pointer" @click="createDocument">add</span>
    </div>
    <div
        v-for="document in documents"
        :key="document.id"
        @click="$emit('show-documents')"
        class="em-p-16"
    >
      <p class="document-label">{{ document.label }}</p>
    </div>
  </div>
</template>

<script>
import formService from '../../services/form.js';
import errors from "../../mixins/errors";

export default {
  name: 'FormBuilderDocuments',
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
  mixins: [errors],
  data () {
    return {
      documents: [],
    }
  },
  created () {
    this.getDocuments();
    if (this.$store.getters['formBuilder/getDocumentModels'].length === 0) {
      this.getDocumentModels();
    }
  },
  methods: {
    getDocuments () {
      formService.getDocuments(this.profile_id).then(response => {
        if (response.status) {
          this.documents = response.data.filter((format) => {
            return format.params !== "emundus_fileUpload";
          });
        } else {
          this.displayError(this.translate('COM_EMUNDUS_FORM_BUILDER_GET_DOCUMENTS_FAILED'), response.msg);
        }
      });
    },
    getDocumentModels () {
      formService.getDocumentModels().then(response => {
        if (response.status) {
          this.$store.dispatch('formBuilder/updateDocumentModels', response.data);
        }
      });
    },
    createDocument() {
      this.$emit('open-create-document');
    },
  }
}
</script>

<style lang="scss">
#form-builder-documents {
  #form-builder-title {
    margin-top: 0;
    font-weight: 700;
    font-size: 16px;
    line-height: 19px;
    letter-spacing: .0015em;
    color: #080c12;
  }

  p {
    cursor: pointer;
    font-weight: 400;
    font-size: 14px;
    line-height: 18px;
  }
}
</style>
