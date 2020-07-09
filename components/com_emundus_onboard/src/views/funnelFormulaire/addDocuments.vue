<template>
  <div class="container-evaluation">
    <ModalAddDocuments
            :cid="this.campaignId"
            @UpdateDocuments="updateList"
    />
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
            <li
              class="list-group-item"
              :id="'itemDoc' + document.id"
              v-for="(document, indexDoc) in documents"
              :key="indexDoc"
            >
              <em class="fas fa-arrows-alt-v handle"></em>
              <div style="display: inline;">
                <span
                  class="draggable"
                  >{{ document.value }}</span
                >
                <button type="button" @click="deleteDoc(indexDoc,document.id)" class="buttonDeleteDoc">
                  <em class="fas fa-times"></em>
                </button>
                <div style="float: right"
                     :class="document.need == 1
                     ? 'text-required'
                     : ''
              ">
                  {{ inners[langue][2] }}
                </div>
                <div class="toggle"
                  @click="changeState(indexDoc, document.id)"
                  :id="'spanDoc' + document.id"
                  style="float: right; margin: 0 12px"
                  class="toggle changeStateDoc"
                >
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
                     : ''
                ">
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
          v-bind="undocDragOptions"
        >
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
                <span class="draggable">{{ undocument.value }}</span>
                <div :id="'spanDoc' + undocument.id" style="float: right">
                  {{ inners[langue][0] }}
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

    <div class="section-sauvegarder-et-continuer-funnel">
      <div class="w-container">
        <div class="container-evaluation w-clearfix">
          <a @click="$parent.next()" class="bouton-sauvergarder-et-continuer-3">
            {{Continuer }}
          </a>
          <a class="bouton-sauvergarder-et-continuer-3 w-retour" @click="previousMenu()">
            {{ Retour }}
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import draggable from "vuedraggable";
import ModalAddDocuments from "../advancedModals/ModalAddDocuments";

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
    langue: Number,
    formulaireEmundus: Number,
    menuHighlight: Number
  },

  data() {
    return {
      drag: false,

      obligatoireDoc: 0,

      inners: [
        ["Non assigné", "Facultatif", "Obligatoire"],
        ["Unassigned", "Optional", "Mandatory"]
      ],

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
                              ordering: unattachment.ordering,
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
      console.log(id);
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
</style>
