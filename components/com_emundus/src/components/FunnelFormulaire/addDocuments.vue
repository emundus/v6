<template>
  <div class="container-evaluation">
    <ModalAddDocuments
        :cid="this.campaignId"
        :pid="this.profileId"
        :doc="this.currentDoc"
        :langue="langue"
        :manyLanguages="manyLanguages"
        @UpdateDocuments="updateList"
    />

    <a @click="$modal.show('modalAddDocuments')" class="bouton-ajouter bouton-ajouter-green pointer" style="width: max-content">
      <div class="add-button-div">
        <em class="fas fa-plus em-mr-4"></em>
        {{ createDocument }}
      </div>
    </a>

    <transition :name="'slide-down'" type="transition">
      <div class="w-form em-flex-row" style="align-items: unset">
        <ul style="padding-left: 0" class="ml-0 w-50">
          <h2 class="blue-text-instruction" v-html="documentNoAssigned"></h2>
          <draggable
              v-model="undocuments"
              v-bind="dragOptionsUndoc"
              tag="ul"
              class="list-group ml-0"
              handle=".handle"
              @end="addingToDocs($event)"
              group="documents">
            <transition-group type="transition" :value="!drag ? 'flip-list' : null">
              <li class="list-doc-item undocuments-item"
                  :class="
                  undocument.need == 1
                    ? 'documentObligatoire'
                    : undocument.need == 0
                    ? 'documentFacultatif'
                    : ''
                "
                  :id="'undoc_' + indexUndoc"
                  v-for="(undocument, indexUndoc) in undocuments"
                  :key="indexUndoc"
              >
                <div class="em-flex-space-between em-flex-row">
                  <div class="em-flex-row">
                    <em class="fas fa-grip-vertical handle" style="color: #cecece;"></em>
                    <span class="em-flex-row ml-10px">
                      {{ undocument.value }}
                      <span class="document-allowed_types">({{ undocument.allowed_types }})</span>
                    </span>
                  </div>
                  <div :id="'spanDoc' + undocument.id" class="text-no-assigned">
                    <button type="button" @click="addUndoc(indexUndoc)" class="buttonAddDoc" :title="addDoc">
                      <em class="fas fa-plus"></em>
                    </button>
                    <button type="button" v-show="undocument.can_be_deleted" class="ml-10px buttonDeleteDoc" :title="deleteDoc" @click="deleteDocument(undocument.id,indexUndoc)">
                      <em class="fas fa-trash-alt" style="color: white"></em>
                    </button>
                  </div>
                </div>
                <div class="em-flex-row doc-desc">
                  <p v-html="undocument.description"></p>
                  <a @click="openUpdateDoc(undocument)" class="cta-block pointer">
                    <em class="fas fa-pen"></em>
                  </a>
                </div>
              </li>
            </transition-group>
          </draggable>
        </ul>

        <hr class="vertical-divider">

        <ul style="padding-left: 0" class="ml-0 w-50">
          <h2 class="blue-text-instruction" v-html="documentAssigned"></h2>
          <draggable
              v-model="documents"
              tag="ul"
              class="list-group ml-0"
              handle=".handle"
              @end="removeToDocs($event)"
              v-bind="dragOptions"
              group="documents"
          >
            <transition-group type="transition" :value="!drag ? 'flip-list' : null" style="display: block;min-height: 200px">
              <li class="list-doc-item"
                  :id="'itemDoc' + document.id"
                  v-for="(document, indexDoc) in documents"
                  :key="indexDoc">
                <div class="em-flex-space-between em-flex-row">
                  <div class="em-flex-row">
                    <em class="fas fa-grip-vertical handle" style="color: #cecece;"></em>
                    <span class="em-flex-row ml-10px">
                      <span>{{ document.value }}</span>
                      <span class="document-allowed_types">({{ document.allowed_types }})</span>
                    </span>
                  </div>
                  <div class="em-flex-row">
                    <div class="em-flex-row" style="margin-right: 30px">
                      <div @click="updateMandatory(document)"
                           :id="'spanDoc' + document.id"
                           style="float: right; margin: 0 12px"
                           class="toggle changeStateDoc">
                        <input
                            type="checkbox"
                            true-value="1"
                            false-value="0"
                            class="check"
                            id="published"
                            name="published"
                            v-model="document.need"
                        />
                        <strong class="b switch"></strong>
                        <strong class="b track"></strong>
                      </div>
                      <div :class="document.need == 1
                           ? 'text-required'
                           : ''">
                        {{ Mandatory }}
                      </div>
                    </div>
                    <button type="button" @click="deleteDocFromForm(indexDoc)" :title="removeDoc" class="buttonDeleteDoc">
                      <em class="fas fa-times"></em>
                    </button>
                  </div>
                </div>
                <div class="em-flex-row doc-desc">
                  <p>{{ document.description }}</p>
                  <a @click="openUpdateDoc(document)" class="cta-block pointer">
                    <em class="fas fa-pen"></em>
                  </a>
                </div>
              </li>
            </transition-group>
          </draggable>
        </ul>
      </div>
    </transition>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import axios from "axios";
import draggable from "vuedraggable";
import ModalAddDocuments from "../AdvancedModals/ModalAddDocuments";
import Swal from "sweetalert2";

const qs = require("qs");

export default {
  name: "addDocuments",
  display: "Handle",

  components: {
    ModalAddDocuments,
    draggable
  },

  props: {
    funnelCategorie: String,
    profileId: Number,
    campaignId: Number,
    langue: String,
    formulaireEmundus: Number,
    menuHighlight: Number,
    manyLanguages: Number
  },

  data() {
    return {
      drag: false,
      loading: false,

      currentDoc: null,

      documents: [],
      undocuments: [],

      attachments: [],
      attachmentsId: [],
      unattachments: [],
      unid: [],

      Retour: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Mandatory: this.translate("COM_EMUNDUS_ONBOARD_ACTIONS_REQUIRED"),
      Continuer: this.translate("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      createDocument: this.translate("COM_EMUNDUS_ONBOARD_CREATE_DOCUMENT"),
      deleteDoc: this.translate("COM_EMUNDUS_ONBOARD_DELETE_DOCUMENT"),
      removeDoc: this.translate("COM_EMUNDUS_ONBOARD_REMOVE_DOCUMENT"),
      addDoc: this.translate("COM_EMUNDUS_ONBOARD_ADD_DOCUMENT"),
      documentAssigned: this.translate("COM_EMUNDUS_ONBOARD_DOCUMENT_ASSIGNED_TO_FORM"),
      documentNoAssigned: this.translate("COM_EMUNDUS_ONBOARD_DOCUMENT_NO_ASSIGNED_TO_FORM")
    };
  },

  methods: {
    updateList() {
      this.documents = [];
      this.undocuments = [];
      this.attachments = [];
      this.attachmentsId = [];
      this.unattachments = [];
      this.unid = [];

      this.getDocuments();
    },

    getDocuments() {
      this.loading = true;
      axios.get("index.php?option=com_emundus&controller=form&task=getalldocuments&prid=" + this.profileId + "&cid=" + this.campaignId)
          .then(response => {
            for (let i = 0; i < response.data.data.length; i++) {
              this.unid.push(response.data.data[i].id);
              this.documents.push(response.data.data[i]);
            }
          }).then(() => {
        axios.get("index.php?option=com_emundus&controller=form&task=getundocuments")
            .then(response => {
              var currentId = 0;

              for (let i = 0; i < response.data.data.length; i++) {
                currentId = response.data.data[i].id;

                if (!this.unid.includes(currentId)) {
                  this.unattachments.push(response.data.data[i]);
                }
              }

              this.undocuments = this.unattachments.map(function(unattachment) {
                var infos = {
                  id: unattachment.id,
                  value: unattachment.value,
                  value_fr: unattachment.value_fr,
                  value_en: unattachment.value_en,
                  description_fr: unattachment.description_fr,
                  description_en: unattachment.description_en,
                  nbmax: unattachment.nbmax,
                  ordering: unattachment.ordering,
                  can_be_deleted: unattachment.can_be_deleted,
                  allowed_types: unattachment.allowed_types,
                  need: -1
                };
                return infos;
              });

              this.currentDoc = null;

              this.loading = false;
            }).catch(e => {
          console.log(e);
        });
      }).catch(e => {
        console.log(e);
      });
    },

    updateMandatory(doc){
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=form&task=updatemandatory",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({ did: doc.id, prid: this.profileId, cid: this.campaignId })
      }).then(() => {
        /*if(doc.need == 0){
          doc.need = 1;
        } else {
          doc.need = 0;
        }*/
      }).catch(error => {
        console.log(error);
      });
    },

    addDocument(undoc){
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=form&task=adddocument",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({ did: undoc.id, prid: this.profileId, cid: this.campaignId })
      }).then(() => {
      }).catch(error => {
        console.log(error);
      });
    },

    removeDocument(id) {
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=form&task=removedocument",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({ did: id, prid: this.profileId, cid: this.campaignId })
      })
          .then(() => {
            //this.$parent.next();
          })
          .catch(error => {
            console.log(error);
          });
    },

    deleteDocument(id,index) {
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEDOCUMENTTYPE"),
        text: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEDOCUMENTTYPE_MESSAGE"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#de6339',
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true
      }).then(result => {
        if(result.value){
          axios({
            method: "POST",
            url: "index.php?option=com_emundus&controller=form&task=deletedocument",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              did: id,
            })
          }).then(() => {
            this.undocuments.splice(index, 1);
          });
        }
      });
    },

    deleteDocFromForm(index) {
      let newIndex = this.undocuments.push(this.documents[index])-1;
      this.documents.splice(index,1);
      this.removeDocument(this.undocuments[newIndex].id);
    },

    addingToDocs: function(evt) {
      this.addDocument(this.documents[evt.newIndex]);
    },

    removeToDocs: function(evt) {
      let index = evt.newIndex;
      this.deleteDoc(index,this.undocuments[index]);
    },

    addUndoc(index) {
      let newIndex = this.documents.push(this.undocuments[index])-1;
      this.undocuments.splice(index,1);
      this.addDocument(this.documents[newIndex]);
    },

    openUpdateDoc(document) {
      this.currentDoc = document;
      setTimeout(() => {
        this.$modal.show('modalAddDocuments');
      },100);
    }
  },

  computed: {
    console: () => console,

    dragOptions() {
      return {
        group: {
          name: "documents",
          put: false
        },
        animation: 200,
        sort: true,
        disabled: false,
        ghostClass: "ghost"
      };
      },

    dragOptionsUndoc() {
      return {
        group: {
          name: "documents",
          put: false
        },
        animation: 200,
        sort: false,
        disabled: false,
        ghostClass: "ghost"
      };
    },

    previousMenu() {
      this.$parent.previous();
    }
  },

  mounted() {},

  created() {
    this.getDocuments();
  }
};
</script>
<style scoped>
.text-no-assigned{
  float: right;
  display: flex;
  width: auto;
}

.cta-block{
  position: relative;
  right: 0;
  bottom: -30%;
  width: 27px;
  height: 27px;
}
</style>
