<template>
  <div id="form-builder-document-list">
    <div id="required-documents" class="em-w-100 em-mb-32 em-mt-32">
      <p class="em-font-size-24 em-font-weight-600">{{ translate('COM_EMUNDUS_FORM_BUILDER_REQUIRED_DOCUMENTS') }}</p>

      <div v-if="requiredDocuments.length > 0">
        <draggable v-model="requiredDocuments" group="form-builder-documents" :sort="false">
          <transition-group id="required-documents">
            <form-builder-document-list-element
                v-for="(document, index) in requiredDocuments"
                :key="'required-' + document.id"
                :document="document"
                :documentIndex="index + 1"
                :totalDocuments="requiredDocuments.length"
                :profile_id="profile_id"
                @edit-document="editDocument(document)"
                @delete-document="deleteDocument"
                @move-document="moveDocument"
            >
            </form-builder-document-list-element>
          </transition-group>
        </draggable>
      </div>
      <div v-if="requiredDocuments.length < 1" class="empty-documents em-mt-16 em-mb-16">
        <draggable :list="emptyDocuments" group="form-builder-documents" :sort="false" class="draggables-list">
          <transition-group id="required-documents">
            <p class="em-w-100 em-text-align-center em-p-16" v-for="(item, index) in emptyDocuments" :key="index">
              {{ translate(item.text) }}
            </p>
          </transition-group>
        </draggable>
      </div>
      <button id="add-document" class="em-secondary-button" @click="addDocument('1')">{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_REQUIRED_DOCUMENT') }}</button>
    </div>
    <div id="optional-documents" class="em-w-100 em-mb-32 em-mt-32">
      <p class="em-font-size-24 em-font-weight-600">{{ translate('COM_EMUNDUS_FORM_BUILDER_OPTIONAL_DOCUMENTS') }}</p>
      <div v-if="optionalDocuments.length > 0">
        <draggable v-model="optionalDocuments" group="form-builder-documents" :sort="false">
          <transition-group id="optional-documents">
            <form-builder-document-list-element
              v-for="(document, index) in optionalDocuments"
              :key="'optional-' + document.id"
              :document="document"
              :documentIndex="index + 1"
              :totalDocuments="optionalDocuments.length"
              :profile_id="profile_id"
              @edit-document="editDocument(document)"
              @delete-document="deleteDocument"
              @move-document="moveDocument"
            >
            </form-builder-document-list-element>
          </transition-group>
        </draggable>
      </div>
      <div v-if="optionalDocuments.length < 1" class="empty-documents em-mt-16 em-mb-16">
        <draggable :list="emptyDocuments" group="form-builder-documents" :sort="false" class="draggables-list">
          <transition-group id="optional-documents">
            <p class="em-w-100 em-text-align-center em-p-16" v-for="(item, index) in emptyDocuments" :key="index">
              {{ translate(item.text) }}
            </p>
          </transition-group>
        </draggable>
      </div>
      <button id="add-document" class="em-secondary-button" @click="addDocument('0')">{{ translate('COM_EMUNDUS_FORM_BUILDER_CREATE_OPTIONAL_DOCUMENT') }}</button>
    </div>
  </div>
</template>

<script>
import FormBuilderDocumentListElement from './FormBuilderDocumentListElement.vue';
import draggable from "vuedraggable";
import formService from "../../services/form";
import campaignService from "../../services/campaign";

export default {
  name: 'FormBuilderDocumentList',
  components: {
    FormBuilderDocumentListElement,
    draggable
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
      emptyDocuments: [{
        text: "COM_EMUNDUS_FORM_BUILDER_EMPTY_DOCUMENTS"
      }],
      closedSection: false,
    }
  },
  created () {
	  this.getDocuments();
  },
	methods: {
    getDocuments () {
      formService.getDocuments(this.profile_id).then(response => {
        this.documents = response.data.data.filter((document) => {
          return document.id;
        });
      });
    },
    moveDocument (documentToMove, direction) {
      let requiredDocumentsInOrder = this.requiredDocuments.map((document, index) => {
        return {
          id: document.id,
          order: index
        }
      });

      let optionalDocumentsInOrder = this.optionalDocuments.map((document, index) => {
        return {
          id: document.id,
          order: index
        }
      });

      // get position of document id in those lists
      let position = null;
      let lastPosition = null;
      let moved = true;
      if (documentToMove.mandatory == 1) {
        position = requiredDocumentsInOrder.findIndex(document => document.id === documentToMove.id);
        lastPosition = requiredDocumentsInOrder.length;
      } else {
        position = optionalDocumentsInOrder.findIndex(document => document.id === documentToMove.id);
        lastPosition = optionalDocumentsInOrder.length;
      }

      if (position != null) {
        /**
         * Si le document est en première position ET est requis, on ne fait rien
         * Si le document est en première position ET est optionnel, on le passe en dernière position des requis
         * Si le document est en dernière position ET est optionnel, on ne fait rien
         * Si le document est en dernière position ET est requis, on le passe en première position des optionnels
         * Sinon, on change juste les positions
         */

        if (documentToMove.mandatory == 1) {
          if (position == 0 && direction === 'up') {
            moved = false;
          } else if (position == (lastPosition-1) && direction === 'down') {
            // update document and put it inside
            documentToMove.mandatory = false;
            requiredDocumentsInOrder = requiredDocumentsInOrder.filter((document) => {
              return document.id != documentToMove.id;
            });
            optionalDocumentsInOrder.unshift({
              id: documentToMove.id,
              order: 0
            });
          } else {
            if (direction === 'up') {
              requiredDocumentsInOrder[position].id = requiredDocumentsInOrder[position - 1].id;
              requiredDocumentsInOrder[position - 1].id = documentToMove.id;
            } else {
              requiredDocumentsInOrder[position].id = requiredDocumentsInOrder[position + 1].id;
              requiredDocumentsInOrder[position + 1].id = documentToMove.id;
            }
          }
        } else {
          if (position == 0 && direction == 'up') {
            documentToMove.mandatory = true;
            optionalDocumentsInOrder = optionalDocumentsInOrder.filter((document) => {
              return document.id != documentToMove.id
            });

            requiredDocumentsInOrder.push({
              id: documentToMove.id,
              order: requiredDocumentsInOrder.length
            });
          } else if (position == (lastPosition-1) && direction === 'down') {
            moved = false;
          } else {
            if (direction === 'up') {
              optionalDocumentsInOrder[position].id = optionalDocumentsInOrder[position - 1].id;
              optionalDocumentsInOrder[position - 1].id = documentToMove.id;
            } else {
              optionalDocumentsInOrder[position].id = optionalDocumentsInOrder[position + 1].id;
              optionalDocumentsInOrder[position + 1].id = documentToMove.id;
            }
          }
        }

        if (moved) {
          requiredDocumentsInOrder.forEach((doc, index) => {
            doc.order = index;
          });
          optionalDocumentsInOrder.forEach((doc, index) => {
            doc.order = index + requiredDocumentsInOrder.length;
          });

          this.documents.forEach((document, index) => {
            const foundReq = requiredDocumentsInOrder.find((reqDocument) => {
              return reqDocument.id == document.id;
            });

            const foundOpt = optionalDocumentsInOrder.find((optDocument) => {
              return optDocument.id == document.id;
            });

            if (foundReq) {
              this.documents[index].mandatory = 1;
              this.documents[index].ordering = foundReq.order;
            } else if (foundOpt) {
              this.documents[index].mandatory = 0;
              this.documents[index].ordering = foundOpt.order;
            }
          });

          formService.reorderDocuments(this.documents).then((response) => {
            campaignService.setDocumentMandatory({
              mandatory: documentToMove.mandatory,
              pid: this.profile_id,
              did: documentToMove.attachment_id,
            });
          });
        }
      }
    },
    addDocument (mandatory = "1") {
      this.$emit('add-document', mandatory);
    },
    editDocument (document) {
      this.$emit('edit-document', document);
    },
    deleteDocument () {
      this.$emit('delete-document');
      this.getDocuments();
    },
  },
  computed: {
    requiredDocuments () {
      const requiredDocuments = this.documents.filter(document => document.mandatory == 1);
      return requiredDocuments.sort((a, b) => {
        return a.ordering - b.ordering;
      });
    },
    optionalDocuments () {
      const optionalDocuments = this.documents.filter(document => document.mandatory == 0);
      return optionalDocuments.sort((a, b) => {
        return a.ordering - b.ordering;
      });
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

  .empty-documents {
    border: 1px dashed black;
    opacity: .2;
  }
}
</style>
