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
    <transition :name="'slide-down'" type="transition">
    <div class="w-form">
      <ul style="padding-left: 0">
        <draggable
          v-model="documents"
          tag="ul"
          class="list-group"
          handle=".handle"
          v-bind="dragOptions"
        >
          <transition-group type="transition" :value="!drag ? 'flip-list' : null">
            <li class="list-group-item"
              :id="'itemDoc' + document.id"
              v-for="(document, indexDoc) in documents"
              :key="indexDoc">
              <em class="fas fa-grip-vertical handle" style="color: #cecece;"></em>
              <div style="display: inline;">
                <span class="draggable">
                  {{ document.value }}
                  <span class="document-allowed_types">({{ document.allowed_types }})</span>
                </span>
                <button type="button" class="buttonDeleteDoc" style="margin-left: 0" @click="openUpdateDoc(document)">
                  <em class="fas fa-pencil-alt"></em>
                </button>
                <button type="button" @click="deleteDoc(indexDoc,document.id)" class="buttonDeleteDoc">
                  <em class="fas fa-times"></em>
                </button>
                <div style="float: right"
                     :class="document.need == 1
                     ? 'text-required'
                     : ''">
                  {{ inners[langue][2] }}
                </div>
                <div @click="changeState(indexDoc, document.id)"
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
                <div style="float: right"
                     :class="document.need == 0
                     ? 'text-non-required'
                     : ''">
                  {{ inners[langue][1] }}
                </div>
              </div>
            </li>
          </transition-group>
        </draggable>
      </ul>

      <hr>

      <ul style="padding-left: 0">
        <draggable
          v-model="undocuments"
          tag="ul"
          class="list-group"
          handle=".handle"
          v-bind="undocDragOptions">
          <transition-group type="transition" :value="!drag ? 'flip-list' : null">
            <li
              class="list-group-item undocuments-item"
              :class="
                undocument.need == 1
                  ? 'documentObligatoire'
                  : undocument.need == 0
                  ? 'documentFacultatif'
                  : ''
              "
              :id="'itemDoc' + undocument.id"
              v-for="(undocument, indexUndoc) in undocuments"
              :key="indexUndoc"
            >
              <button type="button" @click="addUndoc(indexUndoc)" class="buttonAddDoc">
                <em class="fas fa-plus"></em>
              </button>
              <div style="display: inline;">
                <span class="draggable">{{ undocument.value }} <span class="document-allowed_types">({{ undocument.allowed_types }})</span></span>
                <button type="button" class="buttonDeleteDoc" style="margin-left: 0" @click="openUpdateDoc(undocument)">
                  <em class="fas fa-pencil-alt"></em>
                </button>
                <div :id="'spanDoc' + undocument.id" class="text-no-assigned">
                  <span class="col-md-10">{{ inners[langue][0] }}</span>
                  <div v-show="undocument.can_be_deleted" class="ml-10px" @click="deleteDocument(undocument.id,indexUndoc)">
                    <em class="fas fa-trash-alt" style="color: red; cursor: pointer"></em>
                  </div>
                </div>
              </div>
            </li>
          </transition-group>
        </draggable>
      </ul>
      <div class="text-center">
        <button class="bouton-sauvergarder-et-continuer-3" style="float: none" type="button" @click="$modal.show('modalAddDocuments')">{{createDocument}}</button>
      </div>
    </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";
import draggable from "vuedraggable";
import ModalAddDocuments from "../advancedModals/ModalAddDocuments";
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
    obligatoireDoc: Number,
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

      obligatoireDoc: 0,
      currentDoc: null,

      inners: {
        fr: ["Non assigné", "Facultatif", "Obligatoire"],
        en: ["Unassigned", "Optional", "Mandatory"]
      },

      documents: [],
      undocuments: [],

      attachments: [],
      attachmentsId: [],
      unattachments: [],
      unid: [],

      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      createDocument: Joomla.JText._("COM_EMUNDUS_ONBOARD_CREATE_DOCUMENT")
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
      axios.get("index.php?option=com_emundus_onboard&controller=form&task=getalldocuments&prid=" + this.profileId + "&cid=" + this.campaignId)
              .then(response => {
                for (let i = 0; i < response.data.data.length; i++) {
                  this.unid.push(response.data.data[i].id);
                  this.documents.push(response.data.data[i]);
                }
              }).then(() => {
                axios.get("index.php?option=com_emundus_onboard&controller=form&task=getundocuments")
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
                        }).catch(e => {
                          console.log(e);
                        });
              }).catch(e => {
                console.log(e);
              });
    },

    updateDocuments() {
      for (let j = 0; j < this.documents.length; j++) {
        this.documents[j].ordering = j;
      }

      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=form&task=updatedocuments",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({ body: this.documents, prid: this.profileId, cid: this.campaignId })
      })
        .then(() => {
          //this.$parent.next();
        })
        .catch(error => {
          console.log(error);
        });
    },

    removeDocument(id) {
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=form&task=removedocument",
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
        title: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DELETEDOCUMENTTYPE"),
        text: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DELETEDOCUMENTTYPE_MESSAGE"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#de6339',
        confirmButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true
      }).then(result => {
        if(result.value){
          axios({
            method: "POST",
            url: "index.php?option=com_emundus_onboard&controller=form&task=deletedocument",
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

    changeState(index, id) {
      this.obligatoireDoc = this.documents[index].need;

      if (this.obligatoireDoc < 1 && this.obligatoireDoc >= -1) {
        this.documents[index].need++;
      } else {
        this.documents[index].need = 0;
      }
      this.updateDocuments()
    },

    deleteDoc(index,id) {
      this.documents[index].need = -1;
      var oldDoc = this.documents[index];
      this.documents.splice(index, 1);
      this.undocuments.push(oldDoc);
      this.removeDocument(id);
    },

    addUndoc(index) {
      this.undocuments[index].need = 0;
      var oldUndoc = this.undocuments[index];
      this.undocuments.splice(index, 1);
      this.documents.push(oldUndoc);
      this.updateDocuments();
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
        animation: 200,
        group: "description",
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
<style>
  .text-required{
    color: #de6339;
  }
  .text-non-required{
    color: #de6339;
  }

  .text-no-assigned{
    float: right;
    display: flex;
    width: 100px
  }
</style>
