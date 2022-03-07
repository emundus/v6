<template>
  <div class="container-fluid">
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
    <div class="row" v-if="indexHighlight != -1">
      <div class="sidebar-formbuilder" :style="actions_menu ? 'width: 250px' : ''">
        <transition name="move-right">
          <div class="actions-menu menu-block">
<!--            <button class="g-menu-item g-standard burger-button"><img src="/images/emundus/menus/menu.png" @click="enableActionsMenu" style="width: 30px" alt="Menu"></button>-->
            <a class="em-flex-row back-button-action pointer" style="padding: 0 15px" :title="translations.Back">
              {{ translations.BuildYourForm }}
            </a>
            <hr style="width: 80%;margin: 10px auto;">
            <div>
              <div class="action-links">
                  <a class="em-flex-row action-link" style="padding-top: 2em" @click="$modal.show('modalMenu')" :title="translations.addMenu">
                    <span class="material-icons">note_add</span>
                    <label class="action-label col-md-offset-1 col-sm-offset-1">{{translations.addMenu}}</label>
                  </a>
                  <!--<a class="em-flex-row action-link" @click="createGroup()" :title="translations.addGroup">-->
                <a class="em-flex-row action-link" @click="createGroup([],'')" :title="translations.addGroup">
                    <span class="material-icons">table_rows</span>
                    <label class="action-label col-md-offset-1 col-sm-offset-1">{{translations.addGroup}}</label>
                  </a>
                  <a class="em-flex-row action-link" :class="{ 'disable-element': elementDisabled}" @click="showElements" :title="translations.addItem">
                    <span class="material-icons">text_fields</span>
                    <label class="action-label col-md-offset-1 col-sm-offset-1" :class="[{'disable-element': elementDisabled}, addingElement ? 'down-arrow' : 'right-arrow']">{{translations.addItem}}</label>
                  </a>
                <transition :name="'slide-right'" type="transition">
                  <div class="plugins-list" v-if="addingElement">
                    <a class="em-flex-row col-md-offset-1 back-button-action pointer" style="padding: 0 15px" @click="addingElement = !addingElement" :title="Back">
                      <em class="fas fa-arrow-left em-mr-4"></em>
                      {{ translations.Back }}
                    </a>
                    <hr style="width: 80%;margin: 10px auto;">
                    <draggable
                            v-model="plugins"
                            v-bind="dragOptions"
                            handle=".handle"
                            @start="startDragging();dragging = true;draggingIndex = index"
                            @end="addingNewElement($event)"
                            drag-class="plugin-drag"
                            chosen-class="plugin-chosen"
                            ghost-class="plugin-ghost"
                            style="padding-bottom: 2em;margin-top: 10%">
                        <div class="em-flex-row plugin-link col-md-offset-1 col-sm-offset-1 handle" v-for="(plugin,index) in plugins" :key="'plugin_' + index" :id="'plugin_' + plugin.value" @dblclick="addingNewElementByDblClick(plugin.value)" :title="plugin.name">
                          <em :class="plugin.icon"></em>
                          <span class="ml-10px">{{plugin.name}}</span>
                        </div>
                    </draggable>
                  </div>
                </transition>

                <transition :name="'slide-right'" type="transition">
                  <div class="plugins-list" v-if="addingSection">
                    <a class="em-flex-row col-md-offset-1 back-button-action pointer" style="padding: 0 15px" @click="addingSection = !addingSection" :title="Back">
                      <em class="fas fa-arrow-left em-mr-4"></em>
                      {{ translations.Back }}
                    </a>
                    <hr style="width: 80%;margin: 10px auto;">
                   <!-- <draggable
                        v-model="sections"
                        v-bind="dragOptions"
                        handle=".handle"
                        @start="startDragging();dragging = true;draggingIndex = index"
                        @end="addingNewElement($event)"
                        drag-class="plugin-drag"
                        chosen-class="plugin-chosen"
                        ghost-class="plugin-ghost"
                        style="padding-bottom: 2em;margin-top: 10%">-->
                      <div class="em-flex-row plugin-link col-md-offset-1 col-sm-offset-1 " v-for="(section,index) in sections" :id="'section_' + section.value" @click="createGroup(section.value,section.label)" :title="section.name" style="cursor: default" >
                        <em :class="section.icon"></em>
                        <span class="ml-10px">{{section.name}}</span>
                      </div>
                   <!-- </draggable>-->
                  </div>
                </transition>
              </div>
            </div>
          </div>
        </transition>
      </div>
      <div  :class="actions_menu ? 'col-md-8 col-md-offset-4 col-sm-9 col-sm-offset-3' : ''" class="menu-block">
        <div class="heading-block" :class="addingElement || actions_menu ? 'col-md-6' : 'col-md-8'">
          <div class="em-flex-row form-title-block" v-show="!updateFormLabel">
            <h2 class="form-title" @click="enableUpdatingForm" style="padding: 0; margin: 0">{{profileLabel}}</h2>
            <a @click="enableUpdatingForm" style="margin-left: 1em" :title="translations.Edit" class="cta-block pointer">
              <em class="fas fa-pen" data-toggle="tooltip" data-placement="top"></em>
            </a>
          </div>
          <div style="width: max-content;margin-left: 20px" v-show="updateFormLabel">
            <div class="input-can-translate">
              <input v-model="profileLabel" class="form__input field-general w-input" style="width: 400px;" @keyup.enter="updateLabelForm()" :id="'update_label_form_' + prid"/>
              <div class="em-flex-row actions-update-label ml-10px">
                <a @click="updateLabelForm()" :title="translations.Validate">
                  <em class="fas fa-check em-mr-4" data-toggle="tooltip" data-placement="top"></em>
                </a>
              </div>
            </div>
          </div>
        </div>
        <div v-if="menuHighlight === 0" class="form-builder">
          <div class="form-viewer-builder" :class="[addingElement || actions_menu ? 'col-sm-offset-5 col-md-offset-4 col-lg-offset-1 col-sm-7 col-md-6' : 'col-md-8',optionsModal ? 'col-sm-5 col-md-6' : 'col-md-8']">
            <Builder
                    :object="formObjectArray[indexHighlight]"
                    v-if="formObjectArray[indexHighlight]"
                    :UpdateUx="UpdateUx"
                    @show="show"
                    @UpdateFormBuilder="updateFormObjectAndComponent"
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
        <div v-if="menuHighlight === 1" class="form-builder">
          <div class="form-viewer-builder" :class="[addingElement || actions_menu ? 'col-sm-offset-5 col-md-offset-4 col-lg-offset-1 col-sm-7 col-md-6' : 'col-md-8',optionsModal ? 'col-sm-5 col-md-6' : 'col-md-8']">
            <Builder
                    :object="submittionPages[indexHighlight]"
                    v-if="submittionPages[indexHighlight]"
                    :UpdateUx="UpdateUx"
                    @show="show"
                    @UpdateFormBuilder="updateFormObjectAndComponent"
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
        <ul class="col-md-3 sticky-form-pages" :class="[addingElement || actions_menu && formList.length >0? 'ml-10px col-sm-offset-5 col-sm-7' : '',optionsModal ? 'col-sm-5' : '',formList.length ===0 ? 'col-sm-offset-5 col-sm-7':'']" style="margin-top: 0" v-if="formObjectArray">
          <div class="em-flex-row em-flex-space-between em-mb-8">
            <h3 style="padding: 0;margin-bottom: unset">{{ translations.FormPage }}</h3>
            <label class="saving-at">{{ translations.Savingat }} {{lastUpdate}}<em class="fas fa-sync ml-10px"></em></label>
          </div>
          <div class="form-pages">
            <h4 class="ml-10px form-title" style="margin-bottom: 0;padding: 0"><img src="/images/emundus/menus/form.png" class="em-mr-4" :alt="translations.Form">{{ translations.Form }}</h4>
            <draggable
                handle=".handle"
                v-model="formList"
                :class="'draggables-list'"
                @end="SomethingChange"
            >
              <li v-for="(value, index) in formList" :key="index" class="MenuForm" @mouseover="enableGrab(index)" @mouseleave="disableGrab()">
                  <span class="icon-handle" :style="grab && indexGrab == index ? 'opacity: 1' : 'opacity: 0'">
                    <em class="fas fa-grip-vertical handle"></em>
                  </span>
                <a @click="changeGroup(index,value.rgt);menuHighlight = 0"
                   class="MenuFormItem"
                   :title="value.label"
                   :class="indexHighlight == index && menuHighlight === 0 ? 'MenuFormItem_current' : ''" >
                    {{value.label}}
                </a>
              </li>
            </draggable>
            <button class="bouton-sauvergarder-et-continuer" @click="$modal.show('modalMenu');optionsModal = true" style="margin-left: 30px" :title="translations.addMenuAction">{{translations.addMenuAction}}</button>
          </div>
          <div class="form-pages">
            <h4 class="ml-10px form-title" style="margin-bottom: 10px;padding: 0"><em class="far fa-folder-open em-mr-4" :alt="translations.Documents"></em>{{ translations.Documents }}</h4>
            <draggable
                handle=".handle"
                v-model="documentsList"
                :class="'draggables-list'"
                @end="reorderingDocuments"
            >
              <li v-for="(doc, index) in documentsList" :key="index" class="MenuForm" @mouseover="enableGrabDocuments(index)" @mouseleave="disableGrabDocuments()" v-if="doc.displayed==1">
                  <span class="icon-handle" :style="grabDocs && indexGrabDocuments == index ? 'opacity: 1' : 'opacity: 0'">
                    <em class="fas fa-grip-vertical handle"></em>
                  </span>
                <a class="MenuFormItem"
                   :title="doc.label">
                  {{doc.label}}<span v-if="doc.mandatory == 1" style="color: red">*</span>
                </a>
                <a class="cta-block pointer" @click="removeDocument(index,doc.id)" :style="grabDocs && indexGrabDocuments == index ? 'opacity: 1' : 'opacity: 0'">
                  <i class="fas fa-times" style="width: 15px;height: 15px;"></i>
                </a>
                <a @click="currentDoc = doc.docid;$modal.show('modalAddDocuments');optionsModal = true" :title="translations.Edit" class="cta-block pointer" :style="grabDocs && indexGrabDocuments == index ? 'opacity: 1' : 'opacity: 0'">
                  <em class="fas fa-pen" style="width: 15px;height: 14px;" data-toggle="tooltip" data-placement="top"></em>
                </a>
              </li>
            </draggable>
            <button class="bouton-sauvergarder-et-continuer" @click="currentDoc = null;$modal.show('modalAddDocuments');optionsModal = true" style="margin-left: 30px" :title="translations.AddNewDocument">{{translations.AddNewDocument}}</button>

          </div>
          <div class="form-pages" style="padding-top: 20px" v-if="submittionPages">
            <h4 class="ml-10px form-title" style="margin-bottom: 10px;padding: 0"><img src="/images/emundus/menus/confirmation.png" class="em-mr-4" :alt="translations.SubmitPage">{{translations.SubmitPage}}</h4>
            <li v-for="(value, index) in submittionPages" :key="index" class="MenuForm">
              <a @click="menuHighlight = 1;indexHighlight = index"
                 class="MenuFormItem"
                 style="margin-left: 5px"
                 :title="value.object.show_title.value != '' ? value.object.show_title.value : translations.SubmittionPage"
                 :class="indexHighlight == index && menuHighlight === 1 ? 'MenuFormItem_current' : ''">
                {{value.object.show_title.value ? value.object.show_title.value : translations.SubmittionPage}}
              </a>
            </li>
          </div>
          <div class="em-flex-row">
            <button class="bouton-sauvergarder-et-continuer bouton-sauvergarder-et-continuer-green mt-1" @click="sendForm" style="margin-left: 10px" :title="translations.Validate">{{translations.Validate}}</button>
            <button class="bouton-sauvergarder-et-continuer mt-1" @click="exitForm" style="margin-left: 10px" :title="translations.Validate">{{translations.ExitFormbuilder}}</button>
          </div>
        </ul>
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

            axios({
              method: "post",
              url:
                  "index.php?option=com_emundus&controller=formbuilder&task=createsimpleelement",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({
                gid: gid,
                plugin: plugin
              })
            }).then((result) => {

                this.getSimpleElement(gid,result.data.scalar,order,plugin);
              this.loading = false;
            });

          }


        }

      },

      getSimpleElement(gid,element,order,plugin){
        this.loading=true;
        axios({
          method: "get",
          url: "index.php?option=com_emundus&controller=formbuilder&task=getElement",
          params: {
            element: element,
            gid: gid
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {


          if (plugin=="email") {
            response.data.params.password = 3;
          } else {
            response.data.params.password=0;
          }
          axios({
            method: "post",
            url:
                "index.php?option=com_emundus&controller=formbuilder&task=updateparams",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              element: response.data,
            })
          })

          this.menuHighlightCustumisation(response,gid,order);
          this.loading=false;

        });
      },

      createElementEMundusFileUpload(params,gid,plugin,order){
        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=campaign&task=updatedocument",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify(params)
        }).then((rep) => {

          this.$emit("UpdateDocuments");
          axios({
            method: "post",
            url:
                "index.php?option=com_emundus&controller=formbuilder&task=createsimpleelement",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              gid: gid,
              plugin: plugin,
              attachementId: rep.data.data
            })
          }).then((result) => {

            axios({
              method: "get",
              url: "index.php?option=com_emundus&controller=formbuilder&task=getElement",
              params: {
                element: result.data.scalar,
                gid: gid
              },
              paramsSerializer: params => {
                return qs.stringify(params);
              }
            }).then(response => {

             this.menuHighlightCustumisation(response,gid,order);
             this.getDocuments();
              this.loading = false;
            });
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
        if(typeof document.getElementsByClassName('no-elements-tip')[0] != 'undefined') {
          document.getElementsByClassName('no-elements-tip')[0].style.background = '#e4e4e9';
          document.getElementsByClassName('no-elements-tip')[0].style.border = '2px dashed #c3c3ce';
          document.getElementsByClassName('no-elements-tip')[0].innerHTML = this.translate("COM_EMUNDUS_ONBOARD_NO_ELEMENTS_TIPS");
        }
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

        axios({
          method: "post",
          url:
              "index.php?option=com_emundus&controller=formbuilder&task=createsectionsimpleelements",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            gid: gid,
            plugins: plugins,
          })
        }).then(resp=>{

          resp.data.data.forEach((el,index)=>{


            this.getSimpleElement(gid,el,index);
          })

        })
      },
      createGroup(plugins,label) {
        this.loading = true;
        let param = this.formObjectArray[this.indexHighlight].object.id;
        if(this.menuHighlight === 1){
          param = this.submittionPages[this.indexHighlight].object.id;
        }
        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=formbuilder&task=createsimplegroup",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            fid: param,
            label:label


          })
        }).then((result) => {
          axios({
            method: "post",
            url: "index.php?option=com_emundus&controller=formbuilder&task=getJTEXT",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              toJTEXT: result.data.group_tag
            })
          }).then((resultTrad) => {
            result.data.group_showLegend = resultTrad.data;
            axios({
              method: "post",
              url: "index.php?option=com_emundus&controller=formbuilder&task=getalltranslations",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({
                toJTEXT: result.data.group_tag
              })
            }).then((traductions) => {
              result.data.label.fr = traductions.data.fr;
              result.data.label.en = traductions.data.en;

              this.pushGroup(result.data);
              if(plugins.length>0){
                this.createGroupSimpleElements(result.data.group_id, plugins);
              }else{
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
        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=form&task=updateformlabel",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            label: this.profileLabel,
            prid: this.prid,
          })
        }).then(() => {
            this.show("foo-velocity", "success", this.updateSuccess, this.update);
            this.updateFormLabel = false;
        }).catch(e => {
          this.show("foo-velocity", "error", this.updateFailed, this.updating);
          console.log(e);
        });
      },
      pushGroup(group) {
        if(this.menuHighlight === 0) {
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
        axios({
          method: "get",
          url: "index.php?option=com_emundus&controller=formbuilder&task=getElement",
          params: {
            element: element,
            gid: gid
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
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
        document.getElementsByClassName('fa-sync')[0].style.transform = 'rotate(360deg)';
        setTimeout(() => {
          document.getElementsByClassName('fa-sync')[0].style.transform = 'unset';
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
        axios.get("index.php?option=com_emundus&controller=form&task=getfilesbyform&pid=" + this.prid).then(response => {
          this.files = response.data.data;
          if(this.files != 0){
            this.tip();
          }
        });
      },

      getSubmittionPage() {
        axios({
          method: "GET",
          url: "index.php?option=com_emundus&controller=form&task=getsubmittionpage",
          params: {
            prid: this.prid,
          },
          paramsSerializer: params => {
             return qs.stringify(params);
          }
        }).then(response => {
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
        axios({
          method: "get",
          url:
                  "index.php?option=com_emundus&controller=form&task=getFormsByProfileId",
          params: {
            profile_id: this.prid
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
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
        axios({
          method: "get",
          url: "index.php?option=com_emundus&controller=form&task=getDocuments",
          params: {
            pid: this.prid,
          },
          paramsSerializer: params => {
             return qs.stringify(params);
          }
        }).then(response => {

          this.documentsList = response.data.data;

        });
      },

      removeDocument(index,did){
        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=form&task=removeDocumentFromProfile",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            did: did,
          })
        }).then(() => {
            this.documentsList.splice(index,1);
        });
      },

      /**
       * Récupère le nom du formulaire
       */
      getProfileLabel(profile_id) {
        axios({
          method: "get",
          url:
                  "index.php?option=com_emundus&controller=form&task=getProfileLabelByProfileId",
          params: {
            profile_id: profile_id
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        })
                .then(response => {
                  this.profileLabel = response.data.data.label;
                })
                .catch(e => {
                  console.log(e);
                });
      },

      exitForm(){
        window.location.href = 'index.php?option=com_emundus&view=form';
      },

      sendForm() {
        if(this.formList.length>0){
        if(this.cid != 0){
          window.location.href = 'index.php?option=com_emundus&view=form&layout=addnextcampaign&cid=' + this.cid + '&index=4';
        } else {
          axios({
            method: "get",
            url: "index.php?option=com_emundus&controller=form&task=getassociatedcampaign",
            params: {
              pid: this.prid
            },
            paramsSerializer: params => {
              return qs.stringify(params);
            }
          }).then(response => {
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
        axios({
          method: "get",
          url: "index.php?option=com_emundus&controller=formbuilder&task=gettestingparams",
          params: {
           prid : this.prid,
          },
          paramsSerializer: params => {
             return qs.stringify(params);
          }
        }).then(response => {
            this.campaignsAffected = response.data.campaign_files;
            if(Object.keys(this.campaignsAffected).length > 1){
              this.$modal.show('modalTestingForm');
            } else if (Object.keys(this.campaignsAffected).length > 0) {
              if(this.campaignsAffected[0].files.length > 0){
                this.$modal.show('modalTestingForm');
              } else {
                axios({
                  method: "post",
                  url: "index.php?option=com_emundus&controller=formbuilder&task=createtestingfile",
                  headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                  },
                  data: qs.stringify({
                    cid: this.campaignsAffected[0].id,
                  })
                }).then((rep) => {
                  window.open('/index.php?option=com_emundus&task=openfile&fnum=' + rep.data.fnum + '&redirect=1==&Itemid=1079#em-panel');
                });
              }
            } else {
              this.testing = true;
              axios({
                method: "get",
                url:
                    "index.php?option=com_emundus&controller=form&task=getassociatedcampaign",
                params: {
                  pid: this.prid
                },
                paramsSerializer: params => {
                  return qs.stringify(params);
                }
              }).then(() => {
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

        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=form&task=reorderDocuments",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            documents: this.documentsList,

          })
        });
      },
      //

      // Draggable pages
      reorderItems(){
        this.formList.forEach(item => {
          axios({
            method: "post",
            url:
                    "index.php?option=com_emundus&controller=formbuilder&task=reordermenu",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              rgt: item.rgt,
              link: item.link
            })
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
        axios({
          method: "get",
          url: "index.php?option=com_emundus&controller=settings&task=getactivelanguages",
        }).then(response => {
            this.languages = response.data.data;
        });
      }
      //
    },
    created() {
      // Get datas that we need with store
      this.actualLanguage = this.$store.getters['global/actualLanguage'];
      this.manyLanguages = this.$store.getters['global/manyLanguages'];
      this.index = this.$store.getters['global/datas'].index.value;
      this.prid = this.$store.getters['global/datas'].prid.value;
      this.cid = this.$store.getters['global/datas'].cid.value;
      //

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
  .menu-block {
    margin-top: 0;
  }

  .form-title{
    display: flex;
    align-items: center;
    padding: 1em;
    color: black !important;
  }
  .form-title img{
    width: 25px;
  }

  @media (max-width: 768px) {
    .form-title{
      max-width: 250px;
    }
  }
  .container-fluid{
    margin-bottom: 10em;
  }
  .icon-handle{
    color: #cecece;
    position: relative;
    cursor: grab;
    left: 5px;
  }
  .form-viewer-builder{
    background: white;
    transition: all 0.3s ease-in-out;
  }
  .action-label{
    color: black;
    cursor: pointer;
  }
  .disable-element{
    filter: grayscale(1);
    color: gray;
  }
  .fa-pencil-alt{
    color: #de6339;
    cursor: pointer;
  }
  .MenuFormItem
  {
    margin-left: 0;
  }
  .fa-sync{
    transition: all 1s ease-in-out;
  }
  #header-b{
    background: #f8f8f8;
  }
  @media all and (min-width: 1660px) {
    .col-lg-offset-1 {
      margin-left: 13.333%;
    }
  }
  @media all and (min-width: 992px) and (max-width: 1660px) {
    .col-md-offset-2 {
      margin-left: 22.667%;
    }
    .col-lg-offset-1{
      margin-left: 29%;
    }
  }

  @media all and (min-width: 992px){
    .ml-10px {
      margin-left: 10px !important;
    }
  }

  @media all and (min-width: 1280px) and (max-width: 1660px)  {
    .col-lg-offset-1{
      margin-left: 23%;
    }
  }

  @media all and (max-width: 992px) {
    .menu-block{
      display: flex;
      flex-direction: column;
    }
    .sticky-form-pages{
      position: static !important;
      order: 2;
    }
    .heading-block{
      order: 1;
    }
    .form-builder{
      order: 3;
      margin-left: 25px;
    }
  }

  .add-page-icon {
    width: 25px;
    height: 25px;
    margin-left: 0;
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    transition: transform 0.3s ease-in-out;
  }
  .add-group-icon {
    width: 25px;
    height: 25px;
    margin-left: 0;
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    transition: transform 0.3s ease-in-out;
  }
  .add-element-icon {
    width: 25px;
    height: 25px;
    margin-left: 0;
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
    transition: transform 0.3s ease-in-out;
  }
  .vue-notification-custom {
    font-size: 12px;
    padding: 10px;
    margin: 0 5px 5px;
    color: #fff;
    background: #32EE5F !important;
    border-left: 5px solid #16AFE1;
    .notification-content {
      line-height: 20px;
    }
    .translate-icon {
      //background-color: #fff;
      padding: 3px 10px 2px 10px;
      position: static;
      margin-left: 5px;
      background-size: 13px;
    }
    &.success{
      background: #32EE5F !important;
      border-left: 5px solid #16AFE1;
    }
    &.error{
      background: #DB333E !important;
      border-left: 5px solid #16AFE1;
    }
  }
  .MenuForm {
    list-style: none;
    text-decoration: none;
    margin: 10px 0 20px 5px;
    width: auto;
    display: flex;
    align-items: center;
  }
  .MenuFormItem {
    text-decoration: none;
    color: black;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    //white-space: nowrap;
    margin-left: 10px;
    font-size: 14px;
    &:not(.MenuFormItem_current) {
      &:hover {
        color: #12DB42;
        text-decoration: none;
      }
    }
  }
  .MenuFormItem_current {
    color: #12DB42;
    cursor: pointer;
    &:after {
      opacity: 1 !important;
      width: 50% !important;
    }
    &:before {
      opacity: 1 !important;
      width: 50% !important;
    }
  }
  .MenuFormItem.MenuFormItem_current {
    &:hover {
      text-decoration: none;
      color: #12DB42;
    }
  }
  .menus-row {
    display: flex;
    flex-wrap: wrap;
    flex-direction: row;
    padding-left: 0 !important;
    padding-top: 10px;
    margin: 0 auto;
    overflow-x: scroll;
    overflow-y: hidden;
    background-color: #1b1f3c;
    height: auto;
    z-index: 10;
    position: relative;
    box-shadow: 0 0.7px 24.4px rgba(0, 0, 0, 0.013), 0 1.7px 46.9px rgba(0, 0, 0, 0.022), 0 3.1px 67.3px rgba(0, 0, 0, 0.029), 0 5.6px 84.7px rgba(0, 0, 0, 0.037), 0 10.4px 96.9px rgba(0, 0, 0, 0.048), 0 25px 80px rgba(0, 0, 0, 0.07);
    a {
      transition: all 0.2s ease-in-out;
      position: relative;
      &:after {
        content: none;
        position: absolute;
        bottom: -15px;
        width: 100% !important;
        height: 7px;
        margin: 3px 0 0;
        transition: all 0.2s ease-in-out;
        transition-duration: 0.2s;
        opacity: 0;
        background-color: #fff;
        border-radius: 5px;
        right: 0;
      }
    }
  }

  /** VARIABLES **/
  $light-blue: #26CAEA;
  $dark-blue: #16AFE1;
  $light-green: #32EE5F;
  $dark-green: #12DB42;
  $light-yellow: #F0EE6D;

  $night-blue: #353544;
  $dark-grey: #5A5A72;
  $grey: #B0B0BF;
  $light-grey: #F4F4F6;
  $white: #FFFFFF;

  $red: #e5283B;

  $complete-gradient: linear-gradient(rgba(240,238,109,10), rgba(50,238,95,50), rgba(240,238,109,96));
  $simple-gradient: linear-gradient(rgba(240,238,109,5), rgba(240,238,109,120));

  $em-green: #12DB42;
  $em-blue: #16afe1;
  $em-grey: #5A5A72;

  $neutral-100: #FFFFFF;
  $neutral-200: #F2F2F3;
  $neutral-300: #E3E5E8;
  $neutral-400: #C5C8CE;
  $neutral-500: #ACB1B9;
  $neutral-600: #8F96A3;
  $neutral-700: #5E646E;
  $neutral-800: #2B313B;
  $neutral-900: #080C12;

  $main-100: #DFF5E9;
  $main-200: #CCEDE1;
  $main-300: #87D4B8;
  $main-400: #34B385;
  $main-500: #20835F;
  $main-600: #106949;
  $main-700: #0D5946;
  $main-800: #00473D;
  $main-900: #00322B;

  $blue-100: #F0F6FD;
  $blue-200: #C8E1FE;
  $blue-300: #79B6FB;
  $blue-400: #007AFF;
  $blue-500: #1C6EF2;
  $blue-600: #0A53CC;
  $blue-700: #0738AB;
  $blue-800: #042A80;
  $blue-900: #011C5B;

  $red-100: #FFEEEE;
  $red-200: #FEDCDC;
  $red-300: #FBABAB;
  $red-400: #E76767;
  $red-500: #DB333E;
  $red-600: #C31924;
  $red-700: #9F0B15;
  $red-800: #7F050D;
  $red-900: #480005;

  $yellow-100: #FFFBDB;
  $yellow-200: #FFF0B5;
  $yellow-300: #FEEBA1;
  $yellow-400: #FDD46A;
  $yellow-500: #FFC633;
  $yellow-600: #FFB800;
  $yellow-700: #CC8100;
  $yellow-800: #805100;
  $yellow-900: #3E2200;

  $p-4: 4px;
  $p-8: 8px;
  $p-16: 16px;
  $p-24: 24px;
  $p-32: 32px;
  $p-40: 40px;
  $p-48: 48px;
  $p-56: 56px;
  $p-64: 64px;
  $p-72: 72px;
  $p-80: 80px;

  $m-8: 8px;
  $m-16: 16px;
  $m-24: 24px;
  $m-32: 32px;
  $m-40: 40px;
  $m-48: 48px;
  $m-56: 56px;
  $m-64: 64px;
  $m-72: 72px;
  $m-80: 80px;


  .view-form {
    /** FORMBUILDER **/
    .w-row{
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: center;
      margin-bottom: 0;
    }
    .tchooz-sidebar-menu{
      position: fixed !important;
      left: 75px;
      background: white;
      height: 100%;
      top: 0;
      border-left: solid 1px #e0e0e5;
      padding: 10px;
      font-size: 14px;
      max-width: 250px;
      .tchooz-sidebar-menus{
        margin-top: 100px;
      }
    }

    .menus-home-row{
      display: flex;
      flex-direction: row;
      padding-left: 0 !important;
      padding-top: 1em;
      margin: 0 auto;
      overflow-x: scroll;
    }

    .MenuFormHome {
      list-style: none;
      text-decoration: none;
      margin: 10px 10px 30px 0;
      min-width: 100px;
    }

    .MenuFormItemHome {
      text-decoration: none;
      cursor: pointer;
      padding: 5px 30px;
      border-radius: 25px;
      border: solid 2px $dark-green;
      color: $dark-green;
    }
    .MenuFormItemHome:not(.MenuFormItemHome_current):hover {
      background-color: $dark-green;
      color: white;
      text-decoration: none;
    }
    .MenuFormItemHome_current {
      color: white;
      cursor: pointer;
      background-color: $dark-green;
      border: solid 2px $dark-green;
      border-radius: 25px;
    }
    .MenuFormItemHome_current:hover {
      text-decoration: none;
      color: white;
    }
    .MenuFormItemHome_current:after, .MenuFormItemHome_current:before {
      opacity: 1 !important;
      width: 50% !important;
    }
    /**** END ****/

    /**** Buttons ****/
    .bouton-sauvergarder-et-continuer,.bouton-sauvergarder-et-continuer-3 {
      position: relative;
      height: 36px;
      border-radius: 25px;
      padding: 5px 30px;
      border: solid 2px $dark-blue;
      color: white;
      background-color: $dark-blue;
      -webkit-transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
      transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
      display: flex;
      align-items: center;
      font-size: 14px;
      font-weight: 300;
      cursor: pointer;
      &:hover {
        cursor: pointer;
        background-color: transparent;
        color: $dark-blue;
      }
    }

    .bouton-sauvergarder-et-continuer-green{
      border: solid 2px $dark-green;
      color: white;
      background-color: $dark-green;
      &:hover {
        cursor: pointer;
        background-color: transparent;
        color: $dark-green;
      }
    }

    .bouton-sauvergarder-et-continuer-3 {
      float: right;
      width: 90%;
      justify-content: center;
    }

    .w-retour {
      margin-right: 5%;
      background: none !important;
      border: 2px solid grey !important;
      color: grey !important;
      float: left !important;
      &:hover {
        background: grey !important;
        color: white !important;
      }
    }
    .w-delete {
      margin-right: 1em;
      background: #c60a0a;
      border: 2px solid #c60a0a;
      &:hover {
        background: #c60a0a !important;
        color: white !important;
      }
    }

    button,
    html input[type="button"],
    input[type="reset"] {
      border: 0;
      cursor: pointer;
      -webkit-appearance: button;
    }
    /**** END ****/

    .cta-block {
      color: $grey;
      background: transparent;
      font-size: 25px;
      width: 30px;
      height: 30px;
      text-align: center;
      align-items: center;
      display: flex;
      justify-content: center;
      transition: all 0.1s ease-in-out;
      &:hover{
        color: $grey;
      }
    }

    .saving-at{
      color: $dark-blue;
    }

    .form-title-block {
      position: absolute;
      top: -90px;
      z-index: 11;
    }

    .fabrikgrid_0.btn-default {
      background-color: #bd362f;
      padding: 8px 12px;
      height: 38px;

      span {
        color: #fff;
        font-size: 14px;
      }
    }

    .fabrikgrid_1.btn-default {
      padding: 8px 12px;
      height: 35px;
      background-image: linear-gradient(rgb(255, 255, 255), rgb(230, 230, 230));
      border-radius: 4px;
      display: flex;
      align-items: center;

      span {
        color: #848181;
        font-size: 14px;
      }
    }

    fieldset.radio.btn-group {
      display: flex;
      align-items: center;
    }

    .fabrikgrid_radio {
      display: flex !important;
      align-items: center !important;
      vertical-align: middle !important;
      float: left !important;
      margin: 20px 120px 20px 0;
      height: 12px;

      label.radio {
        display: flex !important;
        align-items: center !important;
        width: 100% !important;
        min-height: 20px;
        padding-left: 20px;
        margin-bottom: 5px !important;
        font-weight: bold !important;
        margin-top: 10px;
        position: relative;

        input.fabrikinput {
          position: relative !important;
          margin-right: 7px !important;
          max-width: 15px;
        }
      }
    }

    .fabrikgrid_checkbox {
      display: flex !important;
      align-items: center !important;
      vertical-align: middle !important;
      float: left !important;
      margin: 20px 120px 20px 0;
      height: 12px;

      label.checkbox {
        padding-left: 20px;
      }
    }

    .row-fluid {
      display: inline-block;
    }

    .fabrikLabel {
      margin-bottom: 1em;
    }

    .legend {
      color: $dark-green;
      font-weight: bold;
      width: auto;
      font-size: 18px;
      margin: 0;
    }

    .legend2 {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .input-append {
      display: inline-flex;
    }

    .input-medium {
      height: 100%;
    }

    .dinherit {
      display: inherit;
    }

    .fa-plus-circle {
      margin-right: 5px;
    }

    .btnPM {
      cursor: pointer;
      text-transform: capitalize;
      transition-duration: 0.4s;
      border-radius: 50%;
      font-size: 28px;

      &:hover {
        background-color: #1b1f3c;
        color: white;
      }
    }

    .legendSide {
      display: flex;
      margin: -3px 0 12px;
    }

    .mr-rightS {
      margin: auto 3px 2px auto;
    }

    .BtnModal {
      border-radius: 50%;
      background: #1b1f3c;
      border: 2px solid #1b1f3c;
      color: #fff;
      font: bold 12px Verdana;
      padding: 6px 7px 6px 7px;
      margin-right: 15px;
      cursor: pointer;
      text-transform: capitalize;
      transition-duration: 0.4s;

      &:hover {
        background-color: white;
        color: black;
        border: 2px solid #1b1f3c;
      }
    }

    .inputF {
      display: block;
      width: inherit;
      height: inherit;
      margin: 0 0 0 5px;
    }

    fieldset {
      padding: 2.5% !important;
    }

    .formulairedepresentation {
      width: auto;
    }

    .coldmd2left {
      margin-left: -2.2%;
    }

    .header-form-page {
      padding: 2em 1em 0 1em;
    }

    .page_header {
      font-size: 20px;
      font-weight: bold;
      margin-top: 0 !important;
      margin-bottom: 0 !important;
    }

    .introP, .groupintro {
      padding: 1em;
    }

    .dpflex {
      display: flex;
      margin: 20px 0;
      align-items: center;
    }

    .toright {
      position: relative;
      left: 76.7%;
    }

    .suboptions {
      margin: 1.2% 0;
    }

    .bootstrap-datetimepicker-widget {
      width: 19% !important;
    }

    .separator-top {
      border-top: solid 1px hsla(0, 0%, 81%, 0.5);
    }

    .checkbox {
      span {
        font-weight: normal;
      }

      display: flex !important;
      align-items: center !important;
    }

    .radio {
      span {
        font-weight: normal;
      }
    }

    .checkbox input[type="checkbox"] {
      margin-bottom: 0 !important;
    }

    .radio input[type="radio"] {
      margin-bottom: 0 !important;
    }

    .translate-icon {
      background: url('../assets/images/icons/language.svg') no-repeat center;
      background-size: 15px;
      position: relative;
      right: 2em;
      z-index: 10;
      padding: 12px;
      border-radius: 5px;
      filter: invert(1);
    }

    .translate-icon-selected {
      background-color: #219cc6;
      filter: unset;
      top: 0;
    }

    .fa-sort-down {
      margin-left: 5px;
      margin-bottom: 5px;
    }

    .form-page {
      margin-top: 3em;
    }

    .groups-block {
      background: #f8f8f8;
    }

    .group-item-block {
      margin-bottom: 30px;
      background: white;
    }

    .form-viewer-builder .form-page {
      margin-top: 0;
      background: #fff;
    }

    .elements-block {
      padding-top: 2em;
    }

    .builder-item-element {
      margin-bottom: 10px;
      width: 110%;
      padding-right: 10%;
    }

    .actions-item-bar {
      position: absolute;
      right: -10px;
      a {
        cursor: pointer;
        color: black;

        &:hover {
          color: $dark-blue;
        }
      }
    }

    .element-updating {
      background: transparent;
      border-radius: 4px;
      border: solid 2px $dark-blue !important;
    }

    .draggable-item {
      background: #f8f8f8 !important;
    }

    .builder-item-element__properties {
      align-items: baseline;
      padding: 10px 3em 1em 1em;
      transition: all 0.2s linear;
      border: 2px solid transparent;
      flex-direction: column;
    }

    .unpublished {
      filter: grayscale(1);
      background: repeating-linear-gradient(-45deg, #b8bedf, #a3aad5 5px, #b4b9db 5px, #babed5 10px);
      border-radius: 5px;
    }

    .actions-update-label {
      position: relative;
      height: 33px;

      a {
        cursor: pointer;
      }
    }

    .fa-check {
      color: $dark-green;
    }

    .fa-times {
      color: $red;
      font-weight: lighter;
    }

    .inlineflex {
      display: flex;
      align-content: center;
      align-items: center;
      height: 30px;
    }

    .send-form-button {
      width: 100%;
      margin-top: 3em;
      margin-bottom: 1em;
      background-color: #de6339;
      border: 2px solid #de6339;
      border-radius: 4px;
      height: 50px;
      padding: 10px 30px;
      float: right;
      transition: background-color .2s cubic-bezier(.55, .085, .68, .53);
      cursor: pointer;
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;

      label {
        margin-right: 1em;
      }

      &:hover {
        color: #de6339;
        text-decoration: none;
        background: transparent;
      }
    }

    .test-form-button {
      background-color: #1B1F3C;
      border: 2px solid #1B1F3c;

      &:hover {
        color: #1B1F3C;
      }
    }

    select.fabrikinput.form-control {
      width: 100%;
      padding: 0 5px;
      border: 2px solid #ccc !important;
      background-color: white !important;
      background-image: url(/images/emundus/arrow-2.png) !important;
      background-size: 25px !important;
      background-repeat: no-repeat !important;
      background-position-x: 98% !important;
      background-position-y: 54% !important;
      -webkit-appearance: none;
      border-radius: 4px !important;
      box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
      height: 50px;
      min-height: 28px;
    }

    .draggable-span {
      span {
        display: block;
      }
    }

    .fabrik_characters_left {
      display: flex;
      margin-top: 5px;

      .badge {
        width: max-content;
        background: #de6339;
        margin-right: 10px;
      }
    }

    .toggle-editor.btn-toolbar.pull-right.clearfix {
      float: left !important;
    }

    .form-section {
      display: flex;
      margin: 2em 0 1em 0;
      font-size: 20px;
      padding: 0;
      justify-content: flex-start;
      width: 98%;
      list-style: none;

      li {
        padding: 0 1em 0 1em;

        a {
          cursor: pointer;
          color: black;

          &:hover {
            text-decoration: none;
          }
        }
      }
    }

    .form-section__current {
      color: $dark-blue !important;

      &::after {
        content: "";
        height: 3px;
        margin: 3px 0 0;
        -webkit-transition-duration: .2s;
        transition-duration: .2s;
        background-color: $dark-blue;
        border-radius: 5px;
        display: block;
      }
    }

    .tox {
      .tox-statusbar {
        display: none !important;
      }
    }

    .js-editor-tinymce {
      display: grid;
    }

    .button-add-option {
      float: none;
      text-align: center;
      width: 87%;
      margin-left: 18px;
      margin-bottom: 2em;
    }

    input[type="date"].form-control, input[type="time"].form-control, input[type="datetime-local"].form-control, input[type="month"].form-control {
      line-height: unset !important;
    }

    .form-pages {
      background: white;
      padding: 20px 10px;
    }

    .plugins-list {
      padding-bottom: 2em;
      width: 250px;
      position: fixed;
      display: block;
      top: 0;
      background: white;
      left: 65px;
      padding-top: 5%;
      height: 100%;
      border-left: solid 1px $light-grey;
      overflow: auto;
    }

    .no-elements-tip {
      margin-top: 28px;
      height: 60px;
      border: 2px dashed #c3c3ce;
      border-radius: 4px;
      background-color: #e4e4e9;
      align-items: center;
      display: flex;
      color: #c3c3ce;
      padding-left: 20px;
      position: absolute;
      width: 90%;
    }

    /* Modal */
    .v--modal-overlay {
      background: #0000002e !important;
      overflow-y: auto !important;
      overflow-x: hidden !important;
    }

    .v--modal {
      width: 25% !important;
      height: 100% !important;
      right: 0 !important;
      left: auto !important;
      position: fixed !important;
      box-shadow: unset !important;
      overflow-y: auto !important;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }

    @media all and (max-width: 1024px) {
      .v--modal {
        width: 50% !important;
      }
    }
    /**/

    .icon-featured::before, .icon-default::before, .icon-star::before {
      content: "" !important;
    }

    .settings-elt {
      padding: 5px;
      font-size: 40px;
      background: $dark-blue;
      border-radius: 4px;
      color: white;
      width: 35px !important;
      height: 35px;
    }

    .delete-icon-elt {
      padding: 5px;
      font-size: 40px;
      background: $red;
      border-radius: 4px;
      color: white;
      width: 35px !important;
      height: 35px;
    }

    .tool-icon {
      color: $dark-blue;
      cursor: pointer;
      width: max-content;

      &:hover {
        color: $dark-blue;
        filter: brightness(90%);
      }
    }

    /**** ACTIONS MENU FORMBUILDER ****/
    .sidebar-formbuilder{
      position: fixed;
      width: 250px;
      left: 75px;
      top: 0;
      padding-top: 5.3%;
      z-index: 10;
      height: 100%;
      text-align: left;
      padding-left: 10px;
      background: white;
      transition: all 0.3s ease-in-out;
    }
    .actions-button{
      display: flex;
      justify-content: space-between;
      width: 10vw;
      background: $dark-blue;
      padding: 10px;
      border-radius: 4px;
      left: 0;
      position: fixed;
      color: #fff;
      font-size: 16px;
      font-family: Lato, 'Helvetica Neue', Arial, Helvetica, sans-serif !important;
      margin-top: 1em;
    }
    .heading-block{
      margin-bottom: 1em;
      margin-top: 2em;
      margin-left: 250px;
      width: 67%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      transition: all 0.3s ease-in-out;
    }
    .menu-block {
      height: 100%;
      display: contents;
      margin-top: 2em;
      padding: 0 1em 0 1em;
      transition: all 0.6s ease-in-out;
    }
    .actions-menu {
      position: fixed;
      width: 75px;
      border-radius: 4px;
      overflow-y: scroll;
      height: auto;
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    @media (min-width: 1920px) {
      .actions-menu {
        max-height: 100%;
      }
    }
    .heading-actions{
      height: 60px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 18px;
      margin-left: 1em;
    }
    .action-link{
      padding: 10px 0 15px 15px;
      cursor: pointer;
      filter: brightness(0.2);
      .action-label{
        margin-bottom: 0;
      }
    }
    .action-link:hover > .action-label{
      color: $dark-blue;
    }
    /**** END ****/

    .vdatetime-popup__header {
      background: $dark-blue !important;
    }
    .vdatetime-calendar__month__day--selected {
      &:hover {
        >span {
          >span {
            background: $dark-blue !important;
          }
        }
      }
      >span {
        >span {
          background: $dark-blue !important;
        }
      }
    }
    .vdatetime-popup__actions__button {
      color: #1b1f3c;
    }
    .jello-horizontal {
      -webkit-animation: jello-horizontal 0.3s both;
      animation: jello-horizontal 0.3s both;
    }
    .plugin-chosen {
      background: #eee;
      -webkit-animation: jello-horizontal 0.3s both;
      animation: jello-horizontal 0.3s both;
      cursor: grab;
    }
    .plugin-chosen-elt {
      background: #eee;
      cursor: grab;
    }
    .plugin-ghost {
      background: #eee;
      cursor: grab;
    }
    .plugin-drag {
      background: $dark-blue;
      color: white;
      cursor: grab;
    }
  }
</style>
