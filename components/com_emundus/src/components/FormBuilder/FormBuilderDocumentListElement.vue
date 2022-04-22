<template>
  <div id="form-builder-document-list-element"
    @click="editDocument"
  >
    <div class="section-card em-mt-32 em-mb-32 em-w-100 em-flex-column">
      <div class="section-identifier em-bg-main-500 em-pointer">
        {{ translate('COM_EMUNDUS_FORM_BUILDER_DOCUMENT') }} {{ documentIndex }} / {{ totalDocuments }}
      </div>
      <div
          class="section-content"
          :class="{
            'closed': closedSection,
          }"
      >
        <div v-if="documentData.id">
          <div class="em-w-100 em-flex-row em-flex-space-between">
            <span class="section-title">{{ documentData.name.fr }}</span>
            <span id="delete-section" class="material-icons em-red-500-color em-pointer delete" @click="deleteDocument">delete</span>
          </div>
          <p> {{ documentData.description.fr }} </p>
          <p>{{ translate('COM_EMUNDUS_FORM_BUILDER_ALLOWED_TYPES') }} : {{ documentData.allowed_types }}</p>
          <p>{{ translate('COM_EMUNDUS_FORM_BUILDER_MAX_DOCUMENTS') }} : {{ documentData.nbmax }}</p>
        </div>
        <div v-else>
          <span class="section-title">{{ document.label }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import formService from '../../services/form';

export default {
  name: "FormBuilderDocumentListElement",
  props: {
    document: {
      type: Object,
      required: true,
    },
    totalDocuments: {
      type: Number,
      default: 1,
    },
    documentIndex: {
      type: Number,
      default: 1,
    },
  },
  data () {
    return {
      closedSection: false,
      documentData: {},
    }
  },
  created() {
    this.getDocumentModel(this.document.docid);
  },
  methods: {
    getDocumentModel(documentId = null) {
      this.models = this.$store.getters['formBuilder/getDocumentModels'];

      if (this.models.length > 0) {
        this.documentData = this.models.find(model => model.id === documentId);
      } else {
        formService.getDocumentModels(documentId).then(response => {
          if (response.status) {
            this.documentData = response.data;
          } else {
            this.documentData = {};
          }
        });
      }
    },
    editDocument(event) {
      if (event.target.id === 'delete-section') {
        return;
      }

      this.$emit('edit-document');
    },
    deleteDocument(event) {
      event.preventDefault();
      formService.removeDocumentFromProfile(this.document.id).then(response => {
        this.$emit('delete-document', this.document.id);
        this.$destroy();
      });
    },
  },
}
</script>

<style lang="scss">
#form-builder-document-list-element {
  .section-card {
    .section-identifier {
      color: white;
      padding: 8px 24px;
      border-radius: 4px 4px 0px 0px;
      display: flex;
      align-self: flex-end;;
    }

    .section-content {
      padding: 32px;
      border-top: 4px solid #20835F;
      background-color: white;
      width: 100%;
      transition: all 0.3s ease-in-out;

      &.closed {
        max-height: 93px;
        overflow: hidden;
      }

      .section-title {
        font-weight: 800;
        font-size: 20px;
        line-height: 25px;
      }

      .empty-section-element {
        border: 1px dashed;
        opacity: 0.2;
        padding: 11px;
        margin: 32px 0 0 0;
      }
    }
  }
}
</style>