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
            <a class="d-flex back-button-action pointer" style="padding: 0 15px" :title="translations.Back">
              {{ translations.BuildYourForm }}
            </a>
            <hr style="width: 80%;margin: 10px auto;">
            <div>
              <div class="action-links">
                  <a class="d-flex action-link" style="padding-top: 2em" @click="$modal.show('modalMenu')" :title="translations.addMenu">
                    <em class="add-page-icon"></em>
                    <label class="action-label col-md-offset-1 col-sm-offset-1">{{translations.addMenu}}</label>
                  </a>
                  <!--<a class="d-flex action-link" @click="createGroup()" :title="translations.addGroup">-->
                <a class="d-flex action-link" @click="createGroup([],'')" :title="translations.addGroup">
                    <em class="add-group-icon"></em>
                    <label class="action-label col-md-offset-1 col-sm-offset-1">{{translations.addGroup}}</label>
                  </a>
                  <a class="d-flex action-link" :class="{ 'disable-element': elementDisabled}" @click="showElements" :title="translations.addItem">
                    <em class="add-element-icon"></em>
                    <label class="action-label col-md-offset-1 col-sm-offset-1" :class="[{'disable-element': elementDisabled}, addingElement ? 'down-arrow' : 'right-arrow']">{{translations.addItem}}</label>
                  </a>
                <transition :name="'slide-right'" type="transition">
                  <div class="plugins-list" v-if="addingElement">
                    <a class="d-flex col-md-offset-1 back-button-action pointer" style="padding: 0 15px" @click="addingElement = !addingElement" :title="Back">
                      <em class="fas fa-arrow-left mr-1"></em>
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
                        <div class="d-flex plugin-link col-md-offset-1 col-sm-offset-1 handle" v-for="(plugin,index) in plugins" :key="'plugin_' + index" :id="'plugin_' + plugin.value" @dblclick="addingNewElementByDblClick(plugin.value)" :title="plugin.name">
                          <em :class="plugin.icon"></em>
                          <span class="ml-10px">{{plugin.name}}</span>
                        </div>
                    </draggable>
                  </div>
                </transition>

                <transition :name="'slide-right'" type="transition">
                  <div class="plugins-list" v-if="addingSection">
                    <a class="d-flex col-md-offset-1 back-button-action pointer" style="padding: 0 15px" @click="addingSection = !addingSection" :title="Back">
                      <em class="fas fa-arrow-left mr-1"></em>
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
                      <div class="d-flex plugin-link col-md-offset-1 col-sm-offset-1 " v-for="(section,index) in sections" :id="'section_' + section.value" @click="createGroup(section.value,section.label)" :title="section.name" style="cursor: default" >
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
          <div class="d-flex form-title-block" v-show="!updateFormLabel">
            <h2 class="form-title" @click="enableUpdatingForm" style="padding: 0; margin: 0">{{profileLabel}}</h2>
            <a @click="enableUpdatingForm" style="margin-left: 1em" :title="translations.Edit" class="cta-block pointer">
              <em class="fas fa-pen" data-toggle="tooltip" data-placement="top"></em>
            </a>
          </div>
          <div style="width: max-content;margin-left: 20px" v-show="updateFormLabel">
            <div class="input-can-translate">
              <input v-model="profileLabel" class="form__input field-general w-input" style="width: 400px;" @keyup.enter="updateLabelForm()" :id="'update_label_form_' + prid"/>
              <div class="d-flex actions-update-label ml-10px">
                <a @click="updateLabelForm()" :title="translations.Validate">
                  <em class="fas fa-check mr-1" data-toggle="tooltip" data-placement="top"></em>
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
          <div class="d-flex justify-content-between mb-1">
            <h3 class="mb-0" style="padding: 0;">{{ translations.FormPage }}</h3>
            <label class="saving-at">{{ translations.Savingat }} {{lastUpdate}}<em class="fas fa-sync ml-10px"></em></label>
          </div>
          <div class="form-pages">
            <h4 class="ml-10px form-title" style="margin-bottom: 0;padding: 0"><img src="/images/emundus/menus/form.png" class="mr-1" :alt="translations.Form">{{ translations.Form }}</h4>
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
            <h4 class="ml-10px form-title" style="margin-bottom: 10px;padding: 0"><em class="far fa-folder-open mr-1" :alt="translations.Documents"></em>{{ translations.Documents }}</h4>
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
            <h4 class="ml-10px form-title" style="margin-bottom: 10px;padding: 0"><img src="/images/emundus/menus/confirmation.png" class="mr-1" :alt="translations.SubmitPage">{{translations.SubmitPage}}</h4>
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
          <div class="d-flex">
            <button class="bouton-sauvergarder-et-continuer bouton-sauvergarder-et-continuer-green mt-1" @click="sendForm" style="margin-left: 10px" :title="translations.Validate">{{translations.Validate}}</button>
            <button class="bouton-sauvergarder-et-continuer mt-1" @click="exitForm" style="margin-left: 10px" :title="translations.Validate">{{translations.ExitFormbuilder}}</button>
          </div>
        </ul>
      </div>
    </div>
    <div class="loading-form" v-if="loading">
      <Ring-Loader :color="'#12DB42'" />
    </div>
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
                  "index.php?option=com_emundus_onboard&controller=formbuilder&task=createsimpleelement",
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
          url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getElement",
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
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=updateparams",
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
          url: "index.php?option=com_emundus_onboard&controller=campaign&task=updatedocument",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify(params)
        }).then((rep) => {

          this.$emit("UpdateDocuments");
          axios({
            method: "post",
            url:
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=createsimpleelement",
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
              url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getElement",
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
              "index.php?option=com_emundus_onboard&controller=formbuilder&task=createsectionsimpleelements",
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
          url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=createsimplegroup",
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
            url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getJTEXT",
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
              url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getalltranslations",
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
          url: "index.php?option=com_emundus_onboard&controller=form&task=updateformlabel",
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
        axios.get("index.php?option=com_emundus_onboard&view=form&formid=" + menu.id + "&format=vue_jsonclean")
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
          url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getElement",
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
          let ellink = element.link.replace("fabrik","emundus_onboard");
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
          let ellink = this.formList[index].link.replace("fabrik", "emundus_onboard");
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
        axios.get("index.php?option=com_emundus_onboard&controller=form&task=getfilesbyform&pid=" + this.prid).then(response => {
          this.files = response.data.data;
          if(this.files != 0){
            this.tip();
          }
        });
      },

      getSubmittionPage() {
        axios({
          method: "GET",
          url: "index.php?option=com_emundus_onboard&controller=form&task=getsubmittionpage",
          params: {
            prid: this.prid,
          },
          paramsSerializer: params => {
             return qs.stringify(params);
          }
        }).then(response => {
          let ellink = response.data.link.replace("fabrik","emundus_onboard");
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
                  "index.php?option=com_emundus_onboard&controller=form&task=getFormsByProfileId",
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
          url: "index.php?option=com_emundus_onboard&controller=form&task=getDocuments",
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
          url: "index.php?option=com_emundus_onboard&controller=form&task=removeDocumentFromProfile",
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
                  "index.php?option=com_emundus_onboard&controller=form&task=getProfileLabelByProfileId",
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
        axios({
          method: "get",
          url: "index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute",
          params: {
            link: 'index.php?option=com_emundus_onboard&view=form',
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
          window.location.href = window.location.pathname + response.data.data;
        });
      },

      sendForm() {
        if(this.formList.length>0){
        if(this.cid != 0){
          axios({
            method: "get",
            url: "index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute",
            params: {
              link: 'index.php?option=com_emundus_onboard&view=form&layout=addnextcampaign&cid=' + this.cid + '&index=4',
            },
            paramsSerializer: params => {
              return qs.stringify(params);
            }
          }).then(response => {
            window.location.href = window.location.pathname + response.data.data;
          });
        } else {
          axios({
            method: "get",
            url:
                    "index.php?option=com_emundus_onboard&controller=form&task=getassociatedcampaign",
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
          url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=gettestingparams",
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
                  url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=createtestingfile",
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
                    "index.php?option=com_emundus_onboard&controller=form&task=getassociatedcampaign",
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
          url: "index.php?option=com_emundus_onboard&controller=form&task=reorderDocuments",
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
                    "index.php?option=com_emundus_onboard&controller=formbuilder&task=reordermenu",
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
          url: "index.php?option=com_emundus_onboard&controller=settings&task=getactivelanguages",
        }).then(response => {
            this.languages = response.data.data;
        });
      }
      //
    },
    created() {
      // Get datas that we need with store
      this.actualLanguage = global.getters.actualLanguage;
      this.manyLanguages = global.getters.manyLanguages;
      this.index = global.getters.datas.index.value;
      this.prid = global.getters.datas.prid.value;
      this.cid = global.getters.datas.cid.value;
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
</style>
