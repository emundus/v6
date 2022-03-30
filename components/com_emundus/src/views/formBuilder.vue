<!-- <template>
  <div class="em-w-100">
    <notifications
        group="foo-velocity"
        animation-type="velocity"
        :speed="500"
        position="bottom left"
        :classes="'vue-notification-custom'"
    />
    <div v-if="indexHighlight != -1">
      <ModalAffectCampaign
          :prid="prid"
          :testing="testing"
      />
      <ModalMenu
          :profileId="prid"
          :actualLanguage="actualLanguage"
          :manyLanguages="manyLanguages"
          :languages="languages"
          @AddMenu="pushMenu"
          @modalClosed="optionsModal = false"
      />
      <ModalSide
          v-for="(value, index) in formObjectArray"
          :key="index"
          v-show="formObjectArray[indexHighlight]"
          :ID="value.rgt"
          :element="value.object"
          :link="formObjectArray[indexHighlight].link"
          :menus="formObjectArray"
          :index="index"
          :files="files"
          :actualLanguage="actualLanguage"
          :manyLanguages="manyLanguages"
          :languages="languages"
          @show="show"
          @UpdateUx="UpdateUXT"
          @UpdateName="UpdateName"
          @UpdateIntro="UpdateIntro"
          @UpdateVue="updateFormObjectAndComponent"
          @removeMenu="removeMenu"
          @modalClosed="optionsModal = false"
      />
      <ModalAddDocuments
          :pid="prid"
          :currentDoc="currentDoc"
          :langue="actualLanguage"
          :manyLanguages="manyLanguages"
          @modalClosed="optionsModal = false"
          @UpdateDocuments="getDocuments"
      />
    </div>
    <div class="em-formbuilder-grid-3" v-if="indexHighlight != -1">
      <div style="background: white">
        <div class="em-sidebar-elements">
          <transition name="move-right">
            <div>
              <div>
              <span class="em-body-16-semibold" :title="translations.addItem">
                {{ translations.addItem }}
              </span>
                <hr class="em-sidebar-divider">
              </div>
              <transition :name="'slide-right'" type="transition">
                <div>
                  <draggable
                      v-model="plugins"
                      v-bind="dragOptions"
                      handle=".handle"
                      @start="startDragging();dragging = true;draggingIndex = index"
                      @end="addingNewElement($event)"
                      drag-class="plugin-drag"
                      chosen-class="plugin-chosen"
                      ghost-class="plugin-ghost">
                    <div class="em-flex-row em-flex-space-between em-plugin handle em-grab" v-for="(plugin,index) in plugins" :key="'plugin_' + index" :id="'plugin_' + plugin.value" @dblclick="addingNewElementByDblClick(plugin.value)" :title="plugin.name">
                      <div class="em-flex-row">
                        <em :class="plugin.icon"></em>
                        <span class="em-ml-8">{{plugin.name}}</span>
                      </div>
                      <span class="material-icons-outlined">drag_indicator</span>
                    </div>
                  </draggable>
                </div>
              </transition>
            </div>
          </transition>
        </div>
      </div>

      <div class="em-sidebar-preview em-mt-32">
        <div class="em-text-align-center">
          <div v-show="!updateFormLabel">
            <span class="em-h4" @click="enableUpdatingForm">{{profileLabel}}</span>
          </div>

          <div v-show="updateFormLabel">
            <div class="em-flex-row">
              <input v-model="profileLabel" @keyup.enter="updateLabelForm()" :id="'update_label_form_' + prid"/>
              <span class="material-icons-outlined em-ml-8">done</span>
            </div>
          </div>
        </div>

        <div v-if="menuHighlight === 0">
          <div>
            <Builder
                :object="formObjectArray[indexHighlight]"
                v-if="formObjectArray[indexHighlight]"
                :UpdateUx="UpdateUx"
                @show="show"
                @UpdateFormBuilder="updateFormObjectAndComponent"
                @createGroup="createGroup"
                @removeGroup="removeGroup"
                @modalClosed="optionsModal = false"
                @modalOpen="optionsModal = true"
                :key="builderKey"
                :rgt="rgt"
                :prid="prid"
                :eval="0"
                :files="files"
                :actualLanguage="actualLanguage"
                :manyLanguages="manyLanguages"
                ref="builder"
            />
          </div>
        </div>
        <div v-if="menuHighlight === 1">
          <div>
            <Builder
                :object="submittionPages[indexHighlight]"
                v-if="submittionPages[indexHighlight]"
                :UpdateUx="UpdateUx"
                @show="show"
                @UpdateFormBuilder="updateFormObjectAndComponent"
                @createGroup="createGroup"
                @removeGroup="removeGroup"
                @modalClosed="optionsModal = false"
                @modalOpen="optionsModal = true"
                :key="builderSubmitKey"
                :rgt="rgt"
                :prid="prid"
                :eval="0"
                :files="files"
                :actualLanguage="actualLanguage"
                :manyLanguages="manyLanguages"
                ref="builder_submit"
            />
          </div>
        </div>
      </div>

      <div style="background: white">
        <div class="em-sidebar-navigation">
          <div class="em-flex-row em-mb-8">
            <span>{{ translations.Savingat }} {{lastUpdate}}</span>
            <span class="material-icons-outlined em-ml-8 sync-loading">sync</span>
          </div>

          <div v-if="formObjectArray">
            <div class="em-mb-44">
              <div class="em-flex-row em-flex-space-between em-mb-32">
                <span class="em-h4">{{ translations.FormPage }}</span>
                <span @click="$modal.show('modalMenu')" :title="translations.addMenuAction" class="material-icons em-pointer">add</span>
              </div>

              <draggable
                  handle=".handle"
                  v-model="formList"
                  :class="'draggables-list'"
                  @end="SomethingChange"
              >
                <div v-for="(value, index) in formList" :key="index" class="MenuForm em-mb-16 em-flex-row" @mouseover="enableGrab(index)" @mouseleave="disableGrab()">
                  <span class="material-icons handle" :style="grab && indexGrab == index ? 'opacity: 1' : 'opacity: 0'">drag_indicator</span>
                  <span @click="changeGroup(index,value.rgt);menuHighlight = 0"
                        class="MenuFormItem em-pointer"
                        :title="value.label"
                        :class="indexHighlight == index && menuHighlight === 0 ? 'MenuFormItem_current' : ''" >
                  {{value.label}}
                </span>
                </div>
              </draggable>

              <div v-if="submittionPages">
                <div v-for="(value, index) in submittionPages" :key="index" class="MenuForm" style="margin-left: 18px">
                <span @click="menuHighlight = 1;indexHighlight = index"
                      class="MenuFormItem em-pointer"
                      :title="value.object.show_title.value != '' ? value.object.show_title.value : translations.SubmittionPage"
                      :class="indexHighlight == index && menuHighlight === 1 ? 'MenuFormItem_current' : ''">
                  {{value.object.show_title.value ? value.object.show_title.value : translations.SubmittionPage}}
                </span>
                </div>
              </div>
            </div>

            <div class="em-mb-44">
              <div class="em-flex-row em-flex-space-between em-mb-32">
                <span class="em-h4">{{ translations.Documents }}</span>
                <span @click="currentDoc = null;$modal.show('modalAddDocuments');" :title="translations.AddNewDocument" class="material-icons em-pointer">add</span>
              </div>

              <draggable
                  handle=".handle"
                  v-model="documentsList"
                  :class="'draggables-list'"
                  @end="reorderingDocuments"
              >
                <div v-for="(doc, index) in documentsList" :key="index" class="MenuForm em-mb-16 em-flex-row em-flex-space-between" @mouseover="enableGrabDocuments(index)" @mouseleave="disableGrabDocuments()" v-if="doc.displayed==1">
                  <div class="em-flex-row">
                    <span class="material-icons handle" :style="grabDocs && indexGrabDocuments == index ? 'opacity: 1' : 'opacity: 0'">drag_indicator</span>
                    <span class="MenuFormItem em-pointer" :title="doc.label" @click="currentDoc = doc.docid;$modal.show('modalAddDocuments')">
                    {{doc.label}}<span v-if="doc.mandatory == 1" style="color: red">*</span>
                  </span>
                  </div>
                  <span @click="removeDocument(index,doc.id)" :style="grabDocs && indexGrabDocuments == index ? 'opacity: 1' : 'opacity: 0'" class="material-icons">close</span>
                </div>
              </draggable>
            </div>

            <div class="em-flex-row em-flex-space-between">
              <button class="em-secondary-button em-w-auto" @click="exitForm" :title="translations.Validate">{{translations.ExitFormbuilder}}</button>
              <button class="em-primary-button em-w-auto" @click="sendForm" :title="translations.Validate">{{translations.Validate}}</button>
            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>


<script>
import axios from "axios";

import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/js/all.js";

import "../assets/css/formbuilder.scss";
import draggable from "vuedraggable";

import Builder from "../components/formClean/Builder";
import ModalSide from "../components/formClean/ModalSide";
import ModalMenu from "../components/formClean/ModalMenu";

import formbuilderService from "../services/formbuilder";
import formService from "../services/form";
import campaignService from "../services/campaign";
import settingsService from "../services/settings";

import _ from 'lodash';
import ModalAffectCampaign from "../components/formClean/ModalAffectCampaign";
import ModalAddDocuments from "@/components/AdvancedModals/ModalAddDocuments";
import Swal from "sweetalert2";
import {global} from "../store/global";

const qs = require("qs");

export default {
  name: "FormBuilder",
  components: {
    ModalAddDocuments,
    ModalAffectCampaign,
    Builder,
    ModalSide,
    ModalMenu,
    draggable,
  },
  data() {
    return {
      prid: "",
      index: "",
      cid: "",
      manyLanguages: 0,
      actualLanguage: "",

      // UX variables
      actions_menu: true,
      optionsModal: false,
      UpdateUx: false,
      menuHighlight: 0,
      indexHighlight: -1,
      indexGrab: "0",
      indexGrabDocuments: "0",
      updateFormLabel: false,
      animation: {
        enter: {
          opacity: [1, 0],
          translateX: [0, -300],
          scale: [1, 0.2]
        },
        leave: {
          opacity: 0,
          height: 0
        }
      },
      loading: false,
      first_loading: false,
      lastUpdate: new Date().getHours() + ':' + (new Date().getMinutes()<10?'0':'') + new Date().getMinutes(),
      //

      // Forms variables
      formObjectArray: [],
      submittionPages: [],
      formList: [],
      profileLabel: "",
      id: 0,
      grab: 0,
      grabDocs: 0,
      rgt: 0,
      builderKey: 0,
      builderSubmitKey: 0,
      files: 0,
      //

      // Documents variable
      currentDoc: null,
      documentsList: [],
      //

      // Testing
      campaignsAffected: {},
      testing: false,
      //

      link: '',
      languages: [],

      // Draggabbles variables
      dragging: false,
      draggingIndex: -1,
      elementDisabled: false,
      addingElement: false,
      addingSection:false,
      plugins: {
        field: {
          id: 0,
          value: 'field',
          icon: 'fas fa-font',
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_FIELD")
        },
        nom: {
          id: 8,
          value: 'nom',
          icon: 'fas fa-font',
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_LASTNAME")
        },
        prenom: {
          id: 9,
          value: 'prenom',
          icon: 'fas fa-font',
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_FIRSTNAME")
        },
        email:{
          id:10,
          value: 'email',
          icon: 'fas fa-at',
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_EMAIL")
        },
        yesno:{
          id:12,
          value: 'yesno',
          icon: 'fas fa-toggle-on',
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_YESNO")
        },
        textarea: {
          id: 5,
          value: 'textarea',
          icon: 'far fa-square',
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_TEXTAREA")
        },
        checkbox: {
          id: 2,
          value: 'checkbox',
          icon: 'far fa-check-square',
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_CHECKBOX")
        },
        radiobutton: {
          id: 4,
          value: 'radiobutton',
          icon: 'fas fa-list-ul',
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_RADIOBUTTON")
        },
        dropdown: {
          id: 3,
          value: 'dropdown',
          icon: 'fas fa-th-list',
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_DROPDOWN")
        },
        birthday: {
          id: 1,
          value: 'birthday',
          icon: 'far fa-calendar-alt',
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_BIRTHDAY")
        },
        display: {
          id: 6,
          value: 'display',
          icon: 'fas fa-paragraph',
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_DISPLAY")
        },
        /*fileupload: {
          id: 7,
          value: 'emundus_fileupload',
          icon: 'fas fa-file-upload',
          //name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_DISPLAY")
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_FILE")
        },*/
      },
      sections: {
        default_empty: {
          id: 0,
          value: [],
          icon: 'fas fa-font',
          name: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMPTY_SECTION"),

        },
        personal_informations: {
          id: 0,
          value: ['nom','prenom','email','telephone','birthday','nationalite'],
          icon: 'fas fa-id-card-alt',
          name: Joomla.JText._("COM_EMUNDUS_ONBOARD_PERSONAL_INFORMATIONS"),
          label: {
            fr:"Informations Personelles",
            en: "Personal Informations",
          }

        },

        adress: {
          id: 1,
          value: ['adresse','code postal','pays','ville','adresseComplementaire'],
          icon: 'fas fa-address-card',
          name: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADRESSE"),
          label: {
            fr:"Adresse",
            en: "Adress",
          }
        },
        eexperience_pro: {
          id: 1,
          value: ['date_debut','date_fin','fonction','employeur','ville_employeur','pays','missions'],
          icon: 'fas fa-briefcase',
          name: Joomla.JText._("COM_EMUNDUS_ONBOARD_WORK_EXPERIENCE"),
          label: {
            fr:"Expérience professionnelle",
            en: "Work experience",
          }
        },
      },
      //create document when choosing plugin emundunsFileupload plugin
      docForm: {
        name: {
          fr: 'Autres document',
          en: 'Other documents'
        },
        description: {
          fr: '',
          en: ''
        },
        nbmax: 5,
        selectedTypes: {
          pdf: true,
          jpg: true,
          jpeg:true
        },
      },
      translations:{
        addMenu: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_ADDMENU"),
        addMenuAction: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_ADDMENU_ACTION"),
        addGroup: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_ADDGROUP"),
        addItem: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_ADDITEM"),
        Actions: this.translate("COM_EMUNDUS_ONBOARD_ACTIONS"),
        sendFormButton: this.translate("COM_EMUNDUS_ONBOARD_SEND_FORM"),
        Edit: this.translate("COM_EMUNDUS_ONBOARD_MODIFY"),
        FormPage: this.translate("COM_EMUNDUS_ONBOARD_FORM_PAGE"),
        SubmitPage: this.translate("COM_EMUNDUS_ONBOARD_SUBMIT_PAGE"),
        testingForm: this.translate("COM_EMUNDUS_ONBOARD_TESTING_FORM"),
        Form: this.translate("COM_EMUNDUS_ONBOARD_FORM"),
        Documents: this.translate("COM_EMUNDUS_ONBOARD_DOCUMENTS"),
        AddNewDocument: this.translate("COM_EMUNDUS_ONBOARD_ADD_NEW_DOCUMENT"),
        Back: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
        Savingat: this.translate("COM_EMUNDUS_ONBOARD_SAVING_AT"),
        Validate: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        update: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_UPDATE"),
        updating: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_UPDATING"),
        updateSuccess: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_UPDATESUCESS"),
        updateFailed: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_UPDATEFAILED"),
        ExitFormbuilder: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_EXIT_FORMBUILDER"),
        BuildYourForm: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_BUILD_YOUR_FORM"),
        SubmittionPage: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_SUBMITTION_PAGE"),
      },
    };
  },

  methods: {
    slpitProfileIdfromLabel(label){
      return (label.split(/-(.+)/))[1];
    },
    showModal () {
      Swal.fire({
        text: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_NOFORMPAGEWARNING"),
        type: "warning",
      })
    },
    hide () {
      this.$modal.hide('my-first-modal');
    },

    createElement(gid,plugin,order) {
      let list = this.formObjectArray;
      if(this.menuHighlight === 1){
        list = this.submittionPages;
      }
      if(!_.isEmpty(list[this.indexHighlight].object.Groups)){
        this.loading = true;

        if (plugin=="emundus_fileupload"){

          let types = [];
          Object.keys(this.docForm.selectedTypes).forEach(key => {
            if(this.docForm.selectedTypes[key] == true){
              types.push(key);
            }
          });

          let params = {
            document: this.docForm,
            types: types,
            cid: this.cid,
            pid: this.prid,
            did: 20
          }
          this.createElementEMundusFileUpload(params,gid,plugin,order);
        } else {
          formbuilderService.createSimpleElement({
            'gid': Number(gid),
            'plugin': plugin
          }).then((result) => {
            if (result.status !== false) {
              console.log(result);
              this.getSimpleElement(gid, result.data.scalar, order, plugin);
            } else {
              console.log(result);
            }
            this.loading = false;
          });
        }
      }

    },

    getSimpleElement(gid, element, order, plugin){
      this.loading=true;

      formbuilderService.getElement(gid, element).then((response) => {
        if (response.status === false) {
          this.loading = false;
        } else {
          if (plugin == "email") {
            response.data.params.password = 3;
          } else {
            response.data.params.password = 0;
          }

          formbuilderService.updateParams(response.data);
          this.menuHighlightCustumisation(response, gid, order);
          this.loading = false;
        }
      });
    },

    createElementEMundusFileUpload(params,gid,plugin,order) {
      campaignService.updateDocument(params).then((rep) => {
        this.$emit("UpdateDocuments");
        formbuilderService.createSimpleElement({
          gid: Number(gid),
          plugin: plugin,
          attachmentId: rep.data.data
        }).then((result) => {
          if (result.status !== false) {
            formbuilderService.getElement(gid, result.data.scalar).then(response => {
              this.menuHighlightCustumisation(response,gid,order);
              this.getDocuments();
              this.loading = false;
            });
          } else {
            this.loading = false;
          }
        });

      });
    },

    menuHighlightCustumisation(response,gid,order){

      if(this.menuHighlight === 0) {
        this.$set(this.formObjectArray[this.indexHighlight].object.Groups['group_' + gid], 'elements[element' + response.data.id + ']', response.data)
        this.formObjectArray[this.indexHighlight].object.Groups['group_' + gid].elts.splice(order, 0, response.data);
        this.$refs.builder.updateOrder(gid, this.formObjectArray[this.indexHighlight].object.Groups['group_' + gid].elts);
        this.$refs.builder.$refs.builder_viewer.keyElements['element' + response.data.id] = 0;
        this.$refs.builder.$refs.builder_viewer.enableActionBar(response.data.id);
        this.$refs.builder.$refs.builder_viewer.enableLabelInput(response.data.id);
      } else {
        this.$set(this.submittionPages[this.indexHighlight].object.Groups['group_'+gid], 'elements[element' + response.data.id + ']', response.data)
        this.submittionPages[this.indexHighlight].object.Groups['group_'+gid].elts.splice(order,0,response.data);
        this.$refs.builder_submit.updateOrder(gid,this.submittionPages[this.indexHighlight].object.Groups['group_'+gid].elts);
        this.$refs.builder_submit.$refs.builder_viewer.keyElements['element' + response.data.id] = 0;
        this.$refs.builder_submit.$refs.builder_viewer.enableActionBar(response.data.id);
        this.$refs.builder_submit.$refs.builder_viewer.enableLabelInput(response.data.id);
      }
    },
    addingNewElement: function(evt) {
      this.dragging = false;
      this.draggingIndex = -1;

      let plugin = evt.clone.id.split(/_(.+)/)[1];
      let gid = evt.to.parentElement.parentElement.parentElement.id.split('_')[1];
      if(typeof gid != 'undefined'){
        this.createElement(gid, plugin, evt.newIndex);
      }
    },
    addingNewElementByDblClick: _.debounce(function(plugin) {
      let gid = Object.keys(this.formObjectArray[this.indexHighlight].object.Groups)[Object.keys(this.formObjectArray[this.indexHighlight].object.Groups).length-1].split('_')[1];
      let index = this.formObjectArray[this.indexHighlight].object.Groups['group_' + gid].elts.length;
      if(typeof gid != 'undefined'){
        this.createElement(gid,plugin,index)
      }
    }, 250, { 'maxWait': 1000 }),
    createGroupSimpleElements(gid,plugins){

      formbuilderService.createSectionSimpleElements({
        gid: Number(gid),
        plugins: plugins
      }).then( resp => {
        resp.data.data.forEach((el,index) => {
          this.getSimpleElement(gid,el,index);
        });
      });
    },
    createGroup(plugins = [],label = '') {
      this.loading = true;
      let param = this.formObjectArray[this.indexHighlight].object.id;
      if(this.menuHighlight === 1){
        param = this.submittionPages[this.indexHighlight].object.id;
      }

      formbuilderService.createSimpleGroup(param, label).then((result) => {
        formbuilderService.getJTEXT(result.data.group_tag).then((resultTrad) => {
          result.data.group_showLegend = resultTrad.data;

          formbuilderService.getAllTranslations(result.data.group_tag).then((traductions) => {
            result.data.label.fr = traductions.data.fr;
            result.data.label.en = traductions.data.en;

            this.pushGroup(result.data);
            if (plugins.length>0) {
              this.createGroupSimpleElements(result.data.group_id, plugins);
            } else {
              this.loading = false;
            }
          });
        });
      });
    },

    // Update component dynamically
    UpdateName(index, label) {
      this.formObjectArray[index].object.show_title.value = label;
    },
    UpdateIntro(index, intro) {
      this.formObjectArray[index].object.intro_value = intro;
    },
    UpdateUXT() {
      this.UpdateUx = true;
    },
    updateLabelForm(){
      formService.updateFormLabel({
        label: this.profileLabel,
        prid: this.prid,
      }).then(() => {
        this.show("foo-velocity", "success", this.updateSuccess, this.update);
        this.updateFormLabel = false;
      }).catch(e => {
        this.show("foo-velocity", "error", this.updateFailed, this.updating);
        console.log(e);
      });
    },
    pushGroup(group) {
      if (this.menuHighlight === 0) {
        this.formObjectArray.forEach((form, index) => {
          if (form.object.id == group.formid) {
            this.formObjectArray[index]['object']['Groups']['group_' + group.group_id] = {
              elements: {},
              elts: [],
              group_id: group.group_id,
              group_showLegend: group.group_showLegend,
              label: {
                fr: group.label.fr,
                en: group.label.en,
              },
              group_tag: group.group_tag,
              ordering: group.ordering
            };
          }
        });
        this.$refs.builder.getDataObject();
        this.$refs.builder.$refs.builder_viewer.openGroup[group.group_id] = true;
      } else {
        this.submittionPages.forEach((form, index) => {
          if (form.object.id == group.formid) {
            this.submittionPages[index]['object']['Groups']['group_' + group.group_id] = {
              elements: {},
              elts: [],
              group_id: group.group_id,
              group_showLegend: group.group_showLegend,
              label: {
                fr: group.label.fr,
                en: group.label.en,
              },
              group_tag: group.group_tag,
              ordering: group.ordering
            };
          }
        });
        this.$refs.builder_submit.getDataObject();
        this.$refs.builder_submit.$refs.builder_viewer.openGroup[group.group_id] = true;
      }
      this.elementDisabled = false;
      setTimeout(() => {
        window.scrollTo(0,document.body.scrollHeight);
      }, 200);
    },
    pushMenu(menu){
      this.formList.push(menu);
      axios.get("index.php?option=com_emundus&view=form&formid=" + menu.id + "&format=vue_jsonclean")
          .then(response => {
            this.formObjectArray.push({
              object: response.data,
              rgt: menu.rgt,
              link: menu.link
            });
            this.indexHighlight = this.formObjectArray.length - 1;
          })
    },
    removeMenu(form_id) {
      this.formObjectArray.forEach((form, index) => {
        if(form.object.id == form_id) {
          this.formObjectArray.splice(index, 1);
        }
      });
      this.formList.forEach((form, index) => {
        if(form.id == form_id) {
          this.formList.splice(index,1);
        }
      });
      this.builderKey += 1;
      this.indexHighlight -= 1;
    },
    removeGroup(group_id, form_id) {
      this.formObjectArray.forEach((form, index) => {
        if(form.object.id == form_id){
          delete this.formObjectArray[index]['object']['Groups']['group_'+group_id];
        }
      });
      this.builderKey += 1;
    },
    updateFormObjectAndComponent(){
      this.formObjectArray = [];
      this.getDataObjectSingle(this.indexHighlight);
      this.builderKey += 1;
    },
    getElement(element,gid){
      formbuilderService.getElement(gid, element).then(response => {
        this.formObjectArray[this.indexHighlight].object.Groups['group_'+gid].elements['element'+response.data.id] = response.data;
        this.formObjectArray[this.indexHighlight].object.Groups['group_'+gid].elts.push(response.data);
        this.builderKey += 1;
      });
    },
    //

    /**
     * ** Methods for notify
     */
    tip(){
      this.showTip(
          "foo-velocity",
          this.translate("COM_EMUNDUS_ONBOARD_UPDATEFORMTIP") + '<br/>' + this.translate("COM_EMUNDUS_ONBOARD_UPDATEFORMTIP1") + '<br/>' + this.translate("COM_EMUNDUS_ONBOARD_UPDATEFORMTIP2"),
          this.translate("COM_EMUNDUS_ONBOARD_TIP"),
      );
    },

    show(group, type = "", text = "", title = "Information") {
      this.$notify({
        group,
        title: `${title}`,
        text,
        type
      });
      this.lastUpdate = new Date().getHours() + ':' + (new Date().getMinutes()<10?'0':'') + new Date().getMinutes();
      document.getElementsByClassName('sync-loading')[0].style.transform = 'rotate(360deg)';
      setTimeout(() => {
        document.getElementsByClassName('sync-loading')[0].style.transform = 'unset';
      },1500);
    },
    showTip(group, text = "", title = "Information") {
      this.$notify({
        group,
        title: `${title}`,
        text: text,
        duration: 20000
      });
    },
    clean(group) {
      this.$notify({ group, clean: true });
    },
    enableUpdatingForm(){
      this.updateFormLabel = true;
      setTimeout(() => {
        document.getElementById('update_label_form_' + this.prid).focus();
      }, 100);
    },

    async getDataObject() {
      await this.asyncForEach(this.formList, async (element) => {
        let ellink = element.link.replace("fabrik","emundus");
        await axios.get(ellink + "&format=vue_jsonclean")
            .then(response => {
              this.formObjectArray.push({
                object: response.data,
                rgt: element.rgt,
                link: element.link
              });
            }).then(() => {
              this.formObjectArray.sort((a, b) => a.rgt - b.rgt);
            }).catch(e => {
              console.log(e);
            });
      });
      this.loading = false;
      this.indexHighlight = 0;
      this.elementDisabled = _.isEmpty(this.formObjectArray[this.indexHighlight].object.Groups);
      this.rgt = this.formObjectArray[this.indexHighlight].rgt;
    },

    async getDataObjectSingle(index) {

      if(this.formList.length>0) {
        let ellink = this.formList[index].link.replace("fabrik", "emundus");
        await axios.get(ellink + "&format=vue_jsonclean")
            .then(response => {
              this.formObjectArray[index].object = response.data;
            }).then(() => {
              this.formObjectArray.sort((a, b) => a.rgt - b.rgt);
            }).catch(e => {
              console.log(e);
            });

        this.elementDisabled = _.isEmpty(this.formObjectArray[index].object.Groups);
        this.rgt = this.formObjectArray[index].rgt;
      }

      this.loading = false;

      this.indexHighlight = index;
    },

    async asyncForEach(array, callback) {
      for (let index = 0; index < array.length; index++) {
        await callback(array[index], index, array);
      }
    },

    getCookie(cname) {
      var name = cname + "=";
      var decodedCookie = decodeURIComponent(document.cookie);
      var ca = decodedCookie.split(';');
      for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
          c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
          return c.substring(name.length, c.length);
        }
      }
      return "";
    },

    getFilesByForm() {
      formService.getFilesByForm(this.prid).then(response => {
        this.files = response.data.data;
        if(this.files != 0){
          this.tip();
        }
      });
    },

    getSubmittionPage() {
      formService.getSubmissionPage(this.prid).then(response => {
        let ellink = response.data.link.replace("fabrik","emundus");
        axios.get(ellink + "&format=vue_jsonclean")
            .then(rep => {
              this.submittionPages.push({
                object: rep.data,
                rgt: response.data.rgt,
                link: response.data.link
              });
            });
      });
    },

    /**
     *  ** Récupère toute les forms du profile ID
     */
    getForms() {
      this.loading = true;
      formService.getFormsByProfileId(this.prid).then(response => {
        this.formList = response.data.data;
        setTimeout(() => {
          //this.getDataObject();
          if (this.formList.length > 0){
            this.formList.forEach((element) => {
              this.formObjectArray.push({
                object: {},
                rgt: element.rgt,
                link: element.link
              });
            });
          } else{
            this.showModal();
          }

          this.loading=false;
          this.getDataObjectSingle(0);
          this.getProfileLabel(this.prid);

        }, 100);
      }).catch(e => {
        console.log(e);
      });
    },

    getDocuments(){
      formService.getDocuments(this.prid).then(response => {
        this.documentsList = response.data.data;
      });
    },

    removeDocument(index,did){
      formService.removeDocumentFromProfile(did).then(() => {
        this.documentsList.splice(index,1);
      });
    },

    /**
     * Récupère le nom du formulaire
     */
    getProfileLabel(profile_id) {
         formService.getProfileLabelByProfileId(profile_id).then(response => {
           if (status !== false) {
             this.profileLabel = response.data.data.label;
           }
         }).catch(e => {
            console.log(e);
         });
    },

    exitForm(){
      window.location.href = 'index.php?option=com_emundus&view=form';
    },

    sendForm() {
      if (this.formList.length > 0) {
        if (this.cid != 0) {
          window.location.href = 'index.php?option=com_emundus&view=form&layout=addnextcampaign&cid=' + this.cid + '&index=4';
        } else {
          formService.getAssociatedCampaigns(this.prid).then(response => {
            if(response.data.data.length > 0){
              history.go(-1);
            } else {
              this.$modal.show('modalAffectCampaign');
            }
          }).catch(e => {
            console.log(e);
          });
        }
      } else {
        this.showModal();
      }
    },

    testForm() {
      formbuilderService.getTestingParams(this.prid).then(response => {
        this.campaignsAffected = response.data.campaign_files;
        if (Object.keys(this.campaignsAffected).length > 1) {
          this.$modal.show('modalTestingForm');
        } else if (Object.keys(this.campaignsAffected).length > 0) {
          if (this.campaignsAffected[0].files.length > 0) {
            this.$modal.show('modalTestingForm');
          } else {
            formbuilderService.createTestingFile(this.campaignsAffected[0].id).then((rep) => {
              window.open('/index.php?option=com_emundus&task=openfile&fnum=' + rep.data.fnum + '&redirect=1==&Itemid=1079#em-panel');
            });
          }
        } else {
          this.testing = true;
          formService.getAssociatedCampaigns(this.prid).then(() => {
            this.$modal.show('modalAffectCampaign');
          });
        }
      });
    },

    // Triggers
    changeGroup(index){
      this.loading = true;
      if(_.isEmpty(this.formObjectArray[index].object)) {
        this.getDataObjectSingle(index).then(() => {
          this.loading = false;
        });
      } else {
        this.elementDisabled = _.isEmpty(this.formObjectArray[index].object.Groups);
        this.rgt = this.formObjectArray[index].rgt;
        this.indexHighlight = index;
        this.loading = false;
      }

      if (this.menuHighlight === 1) {
        this.$refs.builder_submit.$refs.builder_viewer.clickUpdatingLabel = false;
        this.$refs.builder_submit.$refs.builder_viewer.translate.label = false;
      } else {
        this.$refs.builder.$refs.builder_viewer.clickUpdatingLabel = false;
        this.$refs.builder.$refs.builder_viewer.translate.label = false;
      }
      document.cookie = 'page_' + this.prid + '='+index+'; expires=Session; path=/'
    },
    SomethingChange: function() {
      this.dragging = true;
      this.formList.forEach((menu, index) => {
        menu.rgt = this.formObjectArray[index].rgt;
      });
      this.reorderItems();
    },
    showElements() {
      if(this.elementDisabled){
        this.addingElement = false;
      } else {
        this.addingElement = !this.addingElement;
      }
    },
    showSections() {

      this.addingSection = !this.addingSection;

    },
    //

    // Draggable documents
    reorderingDocuments: function () {
      this.documentsList.forEach((doc, index) => {
        doc.ordering = index;
      });

      formService.reorderDocuments(this.documentsList);
    },

    // Draggable pages
    reorderItems(){
      this.formList.forEach(item => {
        formbuilderService.reorderMenu({
          rgt: item.rgt,
          link: item.link
        }).catch(e => {
          console.log(e);
        });
      });
    },
    enableGrab(index){
      if(this.formList.length !== 1){
        this.indexGrab = index;
        this.grab = true;
      }
    },
    disableGrab(){
      this.indexGrab = 0;
      this.grab = false;
    },
    enableGrabDocuments(index){
      this.indexGrabDocuments = index;
      this.grabDocs = true;
    },
    disableGrabDocuments(){
      this.indexGrabDocuments = 0;
      this.grabDocs = false;
    },
    startDragging(){
      if(typeof document.getElementsByClassName('no-elements-tip')[0] != 'undefined'){
        document.getElementsByClassName('no-elements-tip')[0].style.background = '#fff';
        document.getElementsByClassName('no-elements-tip')[0].style.border = '2px dashed #16afe1';
        document.getElementsByClassName('no-elements-tip')[0].innerHTML = '';
      }
    },
    enableActionsMenu(){
      const labels = document.getElementsByClassName('action-label');
      this.actions_menu = !this.actions_menu;
      this.addingElement = false;
      if(!this.actions_menu) {
        labels.forEach((label) => {
          label.style.display = 'none';
        });
      } else {
        setTimeout(() => {
          labels.forEach((label) => {
            label.style.display = 'block';
          });
        }, 300);
      }
    },
    //

    // Get languages
    getLanguages(){
      settingsService.getActiveLanguages().then(response => {
        if (response.status !== false) {
          this.languages = response.data.data;
        }
      });
    }
  },
  created() {
    // Get datas that we need with store
    this.actualLanguage = this.$store.getters['global/actualLanguage'];
    this.manyLanguages = this.$store.getters['global/manyLanguages'];
    this.index = this.$store.getters['global/datas'].index.value;
    this.prid = this.$store.getters['global/datas'].prid.value;
    this.cid = this.$store.getters['global/datas'].cid.value;
    //

    document.querySelector('#g-container-main .g-container').style.width = '100%';
    document.querySelector('#g-container-main .g-container').style.marginLeft = '55px';
    document.querySelector('#g-container-main .g-container').style.paddingRight = '75px';

    this.getForms();
    this.getDocuments();
    this.getSubmittionPage();
    this.getFilesByForm();
    this.getLanguages();
  },

  computed: {
    dragOptions() {
      return {
        group: {
          name: "items",
          pull: "clone",
          put: false
        },
        sort: false,
        disabled: false,
        ghostClass: "ghost"
      };
    }
  }
};
</script>

<style lang="scss" scoped>
.em-formbuilder-grid-3{
  display: grid;
  grid-gap: 24px;
  grid-template-columns: 20% 60% 20%;
}
.em-sidebar-elements{
  position: sticky;
  left: 0;
  top: 87px;
  padding: 0 10px;
  background: white;
  align-self: start;
}
.em-sidebar-navigation{
  background: white;
  padding: 8px 55px 0 12px;
  position: sticky;
  right: -30px;
  align-self: start;
  top: 87px;
}
.MenuFormItem_current{
  color: #20835F;
}
.MenuForm a:hover{
  color: #1C6EF2;
}
.em-plugin{
  padding: 16px 8px;
  background: #FAFAFA;
  border: solid 1px #F2F2F3;
  margin-bottom: 8px;
}
.em-sidebar-divider{
  border-color: black;
  margin: 8px 0;
}
</style>
-->

<template>
  <div id="form-builder">
    <header class="em-flex-row em-flex-space-between">
      <div class="right-actions">
        <span class="material-icons">
          navigate_before
        </span>
      </div>
      <span class="em-h4">{{ title }}</span>
      <div class="left-actions em-flex-row em-flex-space-between">
        <span class="material-icons">
          save
        </span>
        <span class="material-icons">
          visibility
        </span>
        <button class="em-primary-button">Publier</button>
      </div>
    </header>
    <div class="body em-flex-row em-flex-space-between">
      <aside class="left-panel">
        <form-builder-elements>
        </form-builder-elements>
      </aside>
      <section>

      </section>
      <aside class="right-panel">
        <span class="material-icons">

        </span>
      </aside>
    </div>
  </div>
</template>

<script>
// components
import FormBuilderElements  from "../components/FormBuilder/FormBuilderElements";

// services
import formService from '../services/form.js';

export default {
  name: 'FormBuilder',
  components: {
    FormBuilderElements
  },
  data() {
    return {
      profile_id: 0,
      campaign_id: 0,
      title: '',
    }
  },
  created() {
    this.profile_id = this.$store.state.global.datas.prid.value;
    this.campaign_id = this.$store.state.global.datas.cid.value;
    this.getFormTitle();
  },
  methods: {
    getFormTitle() {
      formService.getProfileLabelByProfileId(this.profile_id).then(response => {
        if (response.status !== false) {
          this.title = response.data.data.label;
        }
      });
    }
  },
}
</script>

<style lang="scss">
#form-builder {
  width: 100%;
  background: white;

  section {
    background: #f8f8f8;
  }
}
</style>