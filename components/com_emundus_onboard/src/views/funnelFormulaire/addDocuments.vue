<template>
  <div class="container-evaluation">
    <ModalAddDocuments
        :cid="this.campaignId"
        :pid="this.profileId"
        :doc="this.currentDoc"
        :type="this.update_type"
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
              @end="updateDocumentsOrder"
          >
            <transition-group type="transition" :value="!drag ? 'flip-list' : null">
              <li class="list-group-item"
                  :id="'itemDoc' + document.id"
                  v-for="(document, indexDoc) in documents"
                  :key="indexDoc">
                <em class="fas fa-grip-vertical handle" style="color: #cecece;"></em>
                <div style="display: inline;">
                <span class="draggable">
                  <span>{{ document.label[langue] }}</span>
                  <span v-if="document.allowed_types !== ''" class="document-allowed_types">({{ document.allowed_types }})</span>
                  <span v-else class="document-allowed_types">({{ document.type_allowed_types }})</span>
                </span>
                  <button type="button" class="buttonDeleteDoc" style="margin-left: 0" @click="openUpdateDoc(document,'document')">
                    <em class="fas fa-pencil-alt"></em>
                  </button>
                  <button type="button" @click="removeDocument(indexDoc,document.id)" class="buttonDeleteDoc">
                    <em class="fas fa-times"></em>
                  </button>
                  <div style="float: right"
                       :class="document.mandatory == 1
                     ? 'text-required'
                     : ''">
                    {{ inners[langue][2] }}
                  </div>
                  <div @click="changeState(indexDoc,document.id)"
                       :id="'spanDoc' + document.id"
                       style="float: right; margin: 0 12px"
                       class="toggle changeStateDoc">
                    <input
                        type="checkbox"
                        true-value="1"
                        false-value="0"
                        class="check"
                        id="published"
                        name="mandatory"
                        v-model="document.mandatory"
                    />
                    <strong class="b switch"></strong>
                    <strong class="b track"></strong>
                  </div>
                  <div style="float: right"
                       :class="document.mandatory == 0
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
          <transition-group type="transition" :value="!drag ? 'flip-list' : null">
            <li class="list-group-item undocuments-item"
                :id="'itemDoc' + doctype.id"
                v-for="(doctype, indexUndoc) in documents_types"
                :key="indexUndoc">
              <button type="button" @click="addDocuments(doctype.id)" class="buttonAddDoc">
                <em class="fas fa-plus"></em>
              </button>
              <div style="display: inline;">
                <span class="draggable">{{ doctype.label[langue] }} <span class="document-allowed_types">({{ doctype.allowed_types }})</span></span>
                <button type="button" class="buttonDeleteDoc" style="margin-left: 0" @click="openUpdateDoc(doctype,'type')">
                  <em class="fas fa-pencil-alt"></em>
                </button>
                <div :id="'spanDoc' + doctype.id" class="text-no-assigned">
                  <div v-show="doctype.can_be_deleted" class="ml-10px" @click="deleteDocument(doctype.id,indexUndoc)">
                    <em class="fas fa-trash-alt" style="color: red; cursor: pointer"></em>
                  </div>
                </div>
              </div>
            </li>
          </transition-group>
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
      update_type: null,

      inners: {
        fr: ["Non assigné", "Facultatif", "Obligatoire"],
        en: ["Unassigned", "Optional", "Mandatory"]
      },

      documents: [],
      documents_types: [],

      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      createDocument: Joomla.JText._("COM_EMUNDUS_ONBOARD_CREATE_DOCUMENT")
    };
  },

  methods: {
    updateList() {
      this.documents = [];
      this.documents_types = [];

      this.getDocuments();
    },

    getDocuments() {
      axios.get("index.php?option=com_emundus_onboard&controller=campaign&task=getalldocuments&prid=" + this.profileId + "&cid=" + this.campaignId)
          .then(response => {
            this.documents = response.data.data;
          }).then(() => {
            axios.get("index.php?option=com_emundus_onboard&controller=campaign&task=getundocuments")
                .then(response => {
                  this.documents_types = response.data.data;
                }).catch(e => {
                  console.log(e);
                });
      }).catch(e => {
        console.log(e);
      });
    },

    updateDocumentsOrder() {
      this.documents.forEach((document, index) => {
        document.ordering = index;
      });
      console.log(this.documents);
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=campaign&task=updatedocumentsorder",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          documents: this.documents,
        })
      });
    },

    addDocuments(tid) {
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=campaign&task=adddocument",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          type: tid,
          pid: this.profileId,
          cid: this.campaignId
        })
      }).then((result) => {
        console.log(result)
        this.documents.push(result.data.document);
      });
    },

    removeDocument(index,id) {
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=campaign&task=removedocument",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({ did: id })
      }).then(() => {
            this.documents.splice(index, 1);
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
            url: "index.php?option=com_emundus_onboard&controller=campaign&task=deletedocument",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              did: id,
            })
          }).then(() => {
            this.documents_types.splice(index, 1);
          });
        }
      });
    },

    changeState(index, id) {
      if (this.documents[index].mandatory == 0) {
        this.documents[index].mandatory = 1;
      } else {
        this.documents[index].mandatory = 0;
      }
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=campaign&task=updatedocumentmandatory",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          did: id,
          mandatory: this.documents[index].mandatory
        })
      });
    },

    openUpdateDoc(document,type) {
      this.currentDoc = document;
      this.update_type = type;
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
