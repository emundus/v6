<template>
  <div id="BuilderViewer" class="BuilderViewer">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css"
    />
    <div v-if="object_json.show_page_heading"
         :class="object_json.show_page_heading.class"
         v-html="object_json.show_page_heading.page_heading"
    />
    <div class="em-flex-row em-flex-space-between page-header" v-if="eval == 0 && !updatePage">
      <h3 v-if="object_json.show_title" class="em-mr-8" @click="enableUpdatingPage(object_json)" v-html="object_json.show_title.value" />
      <span @click="$emit('modalOpen');$modal.show('modalSide' + object.rgt)" :title="translations.Edit" class="material-icons-outlined em-pointer">edit</span>
    </div>

    <div v-show="updatePage && indexPage == object_json.id" class="em-flex-row page-header">
      <input style="margin-bottom: 0" v-if="object_json.show_title" v-model="object_json.show_title.label[actualLanguage]" @keyup.enter="updateLabelPage(object_json)" :id="'update_input_' + object_json.id"/>
      <span @click="updateLabelPage(object_json)" :title="translations.Validate" class="material-icons-outlined em-pointer">done</span>
    </div>

    <p v-if="eval == 0 && !updateIntroPage" class="em-mt-16" v-html="object_json.intro_value" />

    <button class="em-primary-button em-w-auto em-m-center" @click="$emit('createGroup')">{{ translate('COM_EMUNDUS_ADD_SECTION') }}</button>

    <form method="post" v-on:submit.prevent object_json.attribs class="fabrikForm" :id="'form_' + object_json.id" :style="eval == 1 ? 'margin-top: 30px' : ''">
      <div v-if="object_json.plugintop" v-html="object_json.plugintop"></div>

      <draggable
          handle=".handle"
          class="groups-block"
          v-model="groups"
          @start="startGroupDrag"
          @end="SomethingChangeInGroup">
        <div v-for="(group,index_group) in orderedGroups"
             v-bind:key="'group_' + group.group_id"
             class="group-item-block"
             @mouseover="enableGroupHover(group.group_id)"
             @mouseleave="disableGroupHover()">

          <fieldset :class="[group.group_class]" :id="'group_'+group.group_id" :style="group.hidden_group == -1 ? 'background: #e3e3e3;' : ''" style="background-size: 20px; width: 100%" class="fabrikGroup">
            <div class="hidden-notice em-flex-row" v-if="group.hidden_group == -1">
              <span class="material-icons-outlined">warning_amber</span>
              <span class="em-ml-8">{{translations.HiddenGroup}}</span>
            </div>

            <div class="em-flex-row em-flex-space-between em-w-100" :class="updateGroup && indexGroup == group.group_id ? 'hidden' : ''">
              <div class="em-flex-row em-flex-space-between em-w-100">
                <div class="em-flex-row">
                  <span v-show="hoverGroup && indexGroup == group.group_id" class="material-icons-outlined handle em-handle-group">drag_indicator</span>

                  <legend @click="enableUpdatingGroup(group)" class="legend">
                    <span
                        class="em-ml-32"
                        :class="{'em-opacity-low': !group.group_showLegend}"
                    >
                      {{ group.group_showLegend ? group.group_showLegend : translate('COM_EMUNDUS_FORM_BUILDER_ADD_TITLE')}}
                    </span>
                  </legend>
                </div>

                <div class="em-flex-row">
                  <span :class="group.repeat_group ? 'active-repeat' : ''" class="em-ml-8 em-pointer" :title="translations.RepeatedGroup" @click="enableRepatedGroup(group)">
                    <span class="material-icons-outlined">table_rows</span>
                  </span>

                  <v-popover :popoverArrowClass="'custom-popover-arrow'">
                    <span class="tooltip-target material-icons-outlined">more_vert</span>

                    <template slot="popover">
                      <div class="container-2 w-container" style="max-width: unset">
                        <transition :name="'slide-down'" type="transition">
                          <div>
                            <nav aria-label="action" class="em-flex-column em-align-start pointer">
                              <a v-on:click="enableUpdatingGroup(group)" class="em-p-8-0 em-neutral-700-color">
                                {{translations.EditName}}
                              </a>
                              <a v-on:click="displayHideGroup(group)" class="em-p-8-0 em-neutral-700-color">
                                {{translations.DisplayHide}}
                              </a>
                              <a @click="deleteAGroup(group,index_group)" class="action-submenu" v-if="files == 0 && !group.cannot_delete" :title="translations.Delete">
                                {{translations.Delete}}
                              </a>
                            </nav>
                          </div>
                        </transition>
                      </div>
                    </template>
                  </v-popover>

                </div>
              </div>
            </div>

            <div class="em-flex-row em-flex-space-between" v-show="updateGroup && indexGroup == group.group_id">
              <input v-model="group.label[actualLanguage]" :class="translate.label_group ? '' : 'mb-1'" @keyup.enter="updateLabelGroup(group)" :id="'update_input_' + group.group_id"/>
              <span @click="updateLabelGroup(group)" :title="translations.Validate" class="material-icons-outlined em-pointer">done</span>
            </div>
            <div v-if="group.group_intro" class="groupintro" v-html="group.group_intro"></div>

            <template v-if="group.elts !== undefined">
              <div v-if="group.elts.length == 0" class="em-no-elements-in-group">{{ translations.NoElementsTips }}</div>
            </template>

            <div v-show="openGroup[group.group_id]" class="em-mt-16">
              <draggable
                  handle=".handle"
                  v-model="group.elts"
                  @start="draggable = true"
                  @end="SomethingChange"
                  group="items">
                <transition-group :name="'slide-down'" type="transition" class="em-list-elements">
                  <div v-for="(element,index) in group.elts"
                       v-bind:key="element.id"
                       v-show="element.hidden === false"
                       @mouseover="enableActionBar(element.id)"
                       @mouseleave="disableActionBar()"
                       class="row-fluid">
                    <modalEditElement
                        :elementId="element.id"
                        :gid="element.group_id"
                        :files="files"
                        :manyLanguages="manyLanguages"
                        :actualLanguage="actualLanguage"
                        @reloadElement="reloadElement(element)"
                        @publishUnpublishEvent="publishUnpublishEvent(element)"
                        @updateRequireEvent="updateRequireEvent(element)"
                        @modalClosed="$emit('modalClosed')"
                        @show="show"
                        :key="keyElements['element' + element.id]"
                        :profileId="prid"
                    />
                    <modalDuplicateElement
                        :ID="element.id"
                        :currentGroup="group.group_id"
                        :currentPage="object_json.id"
                        :id="element.id"
                        :prid="prid"
                        @reloadElement="reloadElement(element)"
                        @modalClosed="$emit('modalClosed')"
                        :key="keyElements['element' + element.id]"
                    />
                    <div class="control-group fabrikElementContainer span12">
                      <div class="em-w-90 em-pointer em-p-8-12 em-transparent-border-2"
                           :class="{'element-updating': hoverUpdating && indexHighlight == element.id, 'unpublished': !element.publish, 'draggable-item': draggable && indexHighlight == element.id, 'handle': !clickUpdatingLabel}">
                        <div class="em-flex-row" :class="clickUpdatingLabel && indexHighlight == element.id ? 'hidden' : ''">
                          <span v-if="element.label_value" @click="enableLabelInput(element.id)" v-html="element.label_value" v-show="element.labelsAbove != 2"></span>
                        </div>

                        <div v-show="clickUpdatingLabel && indexHighlight == element.id" class="em-flex-row em-flex-space-between">
                          <input v-model="element.label[actualLanguage]" @keyup.enter="updateLabelElement(element)" :id="'label_' + element.id"/>
                          <span @click="updateLabelElement(element)" :title="translations.Validate" class="material-icons-outlined em-pointer">done</span>
                        </div>

                        <div v-if="element.labelsAbove == 0" class="controls">
                          <div v-if="element.element" :class="element.errorClass" v-html="element.element"></div>
                          <span v-if="element.tipSide" v-html="element.tipSide"></span>
                        </div>
                        <div v-else class="fabrikElement" :class="'plugin-'+element.plugin" v-html="element.element"></div>
                        <span v-if="element.tipSide" v-html="element.tipSide"></span>
                        <span v-if="element.tipBelow" v-html="element.tipBelow"></span>
                      </div>

                      <div class="em-w-90 em-mt-8 em-flex-row em-flex-space-between" :style="hoverUpdating && indexHighlight == element.id ? 'opacity: 1' : 'opacity: 0'">
                        <a class="em-flex-row em-mr-8" v-if="element.plugin != 'display'" :style="hoverUpdating && indexHighlight == element.id ? 'opacity: 1' : 'opacity: 0'">
                          <div class="em-toggle">
                            <input type="checkbox" class="em-toggle-check" v-model="element.FRequire" @click="updateRequireElement(element)"/>
                            <strong class="b em-toggle-switch"></strong>
                            <strong class="b em-toggle-track"></strong>
                          </div>
                          <span class="em-ml-8" style="color:black">{{translations.Required}} </span>
                        </a>

                        <div class="em-flex-row">
                          <div class="em-flex-row em-mr-8 em-pointer" @click="openParameters(element)" :title="translations.Edit">
                            <span class="material-icons-outlined">edit</span>
                          </div>
                          <div class="em-flex-row em-mr-8 em-pointer" @click="deleteElement(element,index)" :title="translations.Delete">
                            <span class="material-icons-outlined em-red-500-color">delete</span>
                          </div>
                          <a class="em-flex-row em-mr-8 em-pointer" target="_blank" :href="'/administrator/index.php?option=com_fabrik&view=element&layout=edit&id=' + element.id" v-if="sysaccess">
                            <span class="material-icons-outlined">link</span>
                          </a>
                        </div>
                      </div>


                    </div>
                  </div>
                </transition-group>
              </draggable>
            </div>
            <div class="groupoutro" v-if="group.group_outro" v-html="group_outro"></div>
          </fieldset>
        </div>
      </draggable>
      <div v-if="object_json.pluginbottom" v-html="object_json.pluginbottom"></div>
    </form>

    <button class="em-primary-button em-w-auto em-m-center" @click="$emit('createGroup')">{{ translate('COM_EMUNDUS_ADD_SECTION') }}</button>
  </div>
</template>


<script>
import _ from "lodash";
import axios from "axios";
import datePicker from "vue-bootstrap-datetimepicker";
import draggable from "vuedraggable";
import modalEditElement from "./Modal";
import modalDuplicateElement from "./ModalDuplicateElement";
import Translation from "@/components/translation";

const qs = require("qs");

import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/js/all.js";
import Swal from "sweetalert2";
import Editor from "../editor";

export default {
  name: "BuilderViewer",
  props: {
    object: Object,
    groups: Array,
    change: Boolean,
    changedElement: Array,
    changedGroup: String,
    UpdateUx: Boolean,
    files: Number,
    eval: Number,
    prid: String,
    actualLanguage: String,
    manyLanguages: String,
    pid: Number
  },
  components: {
    Editor,
    datePicker,
    draggable,
    modalEditElement,
    modalDuplicateElement,
    Translation
  },
  data() {
    return {
      object_json: "",
      sysaccess: false,

      // Page trigger
      updatePage: false,
      updateIntroPage: false,
      indexPage: -1,

      // Groups trigger
      openGroup: {},
      hoverGroup: false,
      indexGroup: -1,
      updateGroup: false,

      // Elements trigger
      hoverUpdating: false,
      indexHighlight: 0,
      clickUpdatingLabel: false,
      draggable: false,
      fieldChanges: false,
      repeat: false,
      keyElements: {},

      date: new Date(),
      options: {
        format: "DD/MM/YYYY",
        useCurrent: false
      },
      can_translate: {
        label: false,
        label_group: false,
        label_page: false,
        intro_page: false,
      },
      elementAssociateDocUpdateForm: {
        name: {
          fr: '',
          en: ''
        },
        description: {
          fr: '',
          en: ''
        },
        nbmax: 1,
        selectedTypes: {
          pdf: false,
          'jpg;png;gif': false,
          'doc;docx;odt': false,
          'xls;xlsx;odf': false,
        },
      },

      // TRANSLATIONS
      translations: {
        update: "COM_EMUNDUS_ONBOARD_BUILDER_UPDATE",
        updating: "COM_EMUNDUS_ONBOARD_BUILDER_UPDATING",
        updateSuccess: "COM_EMUNDUS_ONBOARD_BUILDER_UPDATESUCESS",
        orderSuccess: "COM_EMUNDUS_ONBOARD_BUILDER_ORDERSUCESS",
        orderFailed: "COM_EMUNDUS_ONBOARD_BUILDER_ORDERFAILED",
        updateFailed: "COM_EMUNDUS_ONBOARD_BUILDER_UPDATEFAILED",
        sidemenuhelp: "COM_EMUNDUS_ONBOARD_SIDEMENUHELP",
        TranslateEnglish: "COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH",
        Unpublish: "COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH",
        Publish: "COM_EMUNDUS_ONBOARD_ACTION_PUBLISH",
        Required: "COM_EMUNDUS_ONBOARD_ACTIONS_REQUIRED",
        Settings: "COM_EMUNDUS_ONBOARD_ACTIONS_SETTINGS",
        Delete: "COM_EMUNDUS_ONBOARD_ACTION_DELETE",
        Edit: "COM_EMUNDUS_ONBOARD_MODIFY",
        Cancel: "COM_EMUNDUS_ONBOARD_CANCEL",
        Validate: "COM_EMUNDUS_ONBOARD_OK",
        RepeatGroup: "COM_EMUNDUS_ONBOARD_REPEAT_GROUP",
        RepeatedGroup: "COM_EMUNDUS_ONBOARD_REPEATED_GROUP",
        Duplicate: "COM_EMUNDUS_ONBOARD_DUPLICATE",
        NoElementsTips: "COM_EMUNDUS_ONBOARD_NO_ELEMENTS_TIPS",
        EditName: "COM_EMUNDUS_ONBOARD_BUILDER_EDIT_NAME",
        EditIntro: "COM_EMUNDUS_ONBOARD_BUILDER_EDIT_INTRO",
        DisplayHide: "COM_EMUNDUS_ONBOARD_BUILDER_DISPLAY_HIDE",
        HiddenGroup: "COM_EMUNDUS_ONBOARD_BUILDER_HIDDEN_GROUP",
      }
    };
  },
  methods: {
    // Elements update
    splitProfileIdFromLabel(label){
      return (label.split(/-(.+)/))[1];
    },

    async updateElementsOrder(group, list, elt) {
      var elements = list.map((element, index) => {
        return { id: element.id, order: index + 1 };
      });
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus&controller=formbuilder&task=updateOrder",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          elements: elements,
          group_id: group,
          moved_el: elt,
        })
      }).then(response => {
        this.$emit(
            "show",
            "foo-velocity",
            "success",
            this.translations.orderSuccess,
            this.translations.update
        );
        let ellink = this.object.link.replace("fabrik","emundus");
        axios.get(ellink + "&format=vue_jsonclean").then(r => {
          this.groups.forEach(grp => {
            this.$set(this.object_json.Groups['group_' + grp.group_id], 'elements', r.data.Groups['group_' + grp.group_id].elements)
          });
        });
        elt.group_id = group;
      }).catch(e => {});
    },

    updateRequireElement(element) {
      if(this.clickUpdatingLabel) {
        this.updateLabelElement(element);
      }
      setTimeout(() => {
        axios({
          method: "post",
          url:
              "index.php?option=com_emundus&controller=formbuilder&task=changerequire",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            element: element
          })
        }).then(() => {
          this.updateRequireEvent(element);
        }).catch(e => {
          this.$emit(
              "show",
              "foo-velocity",
              "error",
              this.translations.updateFailed,
              this.translations.updating
          );
        });
      }, 300);
    },


    updateRequireEvent(element) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=formbuilder&task=getElement",
        params: {
          element: element.id,
          gid: element.group_id
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        element.label_value = response.data.label_value;
        this.$emit(
            "show",
            "foo-velocity",
            "success",
            this.translations.updateSuccess,
            this.translations.update
        );
      });
    },

    publishUnpublishEvent(element) {
      element.publish = !element.publish;
      if(element.publish){
        document.getElementById('publish_icon_' + element.id).classList.remove('fa-eye');
        document.getElementById('publish_icon_' + element.id).classList.add('fa-eye-slash');
      } else {
        document.getElementById('publish_icon_' + element.id).classList.add('fa-eye');
        document.getElementById('publish_icon_' + element.id).classList.remove('fa-eye-slash');
      }
    },
    deleteAssociateElementDoc(docid){

      //delete doc drop files
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus&controller=campaign&task=deletedocumentdropfile",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          did: docid,
        })
      }).then((rep) => {});

      // delete document form profile
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus&controller=form&task=removeDocumentFromProfile",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          did: docid,
        })
      }).then((rep) => {});

      // remove document
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus&controller=form&task=removeDocument",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          did: docid,
          prid: this.prid,
          cid: this.cid
        })
      }).then((rep) => {});
    },

    deleteElement(element,index) {
      if(this.clickUpdatingLabel) {
        this.updateLabelElement(element);
      }
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEELEMENT"),
        text: this.translate("COM_EMUNDUS_ONBOARD_CANT_REVERT"),
        type: "warning",
        showCancelButton: true,
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
      }).then(result => {
        if (result.value) {

          if(element.plugin=="emundus_fileupload") {
            this.deleteAssociateElementDoc(element.params.attachmentId);
          }
          axios({
            method: "post",
            url:
                "index.php?option=com_emundus&controller=formbuilder&task=deleteElement",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              element: element.id,
            })
          }).then(() => {
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_ELEMENTDELETED"),
              type: "success",
              showConfirmButton: false,
              timer: 2000
            }).then(() => {
              this.object_json.Groups['group_' + element.group_id].elts.splice(index,1);
              delete this.object_json.Groups['group_' + element.group_id].elements['element' + element.id];
              this.$forceUpdate();
            });
          }).catch(e => {});
        }
      });
    },

    openDuplicate(element){
      if(this.clickUpdatingLabel) {
        this.updateLabelElement(element);
      }
      this.$emit('modalOpen')
      this.$modal.show('modalDuplicateElement' + element.id)
    },

    openParameters(element){
      /*if(this.clickUpdatingLabel) {
        this.updateLabelElement(element);
      }*/
      this.repeat = false;
      //this.$emit('modalOpen')
      this.$modal.show('modalEditElement' + element.id)
    },
    retrieveAssociateElementDoc(docid){
      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=formbuilder&task=retriveElementFormAssociatedDoc',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          gid:2,
          docid:docid
        })
      }).then((result)=>{
        this.elementAssociateDocUpdateForm.description[this.actualLanguage]=result.data.description;

        if (result.data.allowed_types.includes('pdf')) {
          this.elementAssociateDocUpdateForm.selectedTypes.pdf = true;
        } else {
          this.elementAssociateDocUpdateForm.selectedTypes.pdf = false;
        }
        if (result.data.allowed_types.includes('jpg') || result.data.allowed_types.includes('png') || result.data.allowed_types.includes('gif')) {
          this.elementAssociateDocUpdateForm.selectedTypes['jpg;png;gif'] = true;
        } else {
          this.elementAssociateDocUpdateForm.selectedTypes['jpg;png;gif'] = false;
        }
        if (result.data.allowed_types.includes('xls') || result.data.allowed_types.includes('xlsx') || result.data.allowed_types.includes('odf')) {
          this.elementAssociateDocUpdateForm.selectedTypes['xls;xlsx;odf'] = true;
        } else {
          this.elementAssociateDocUpdateForm.selectedTypes['xls;xlsx;odf'] = false;
        }


        this.elementAssociateDocUpdateForm.nbmax=result.data.nbmax;
      })
    },

    updateAssociateDocElement(params){
      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=campaign&task=updatedocument',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify(params)
      }).then((rep) => {
        axios({
          method: "post",
          url: 'index.php?option=com_emundus&controller=campaign&task=updateDocumentFalang',
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify(params)
        }).then((rep)=>{})
      });
    },

    updateLabelElement(element) {
      if(element.plugin=="emundus_fileupload") {
        this.retrieveAssociateElementDoc(element.params.attachmentId);
      }

      let labels = element.label;
      if(labels.en === 'Unnamed item'){
        labels.en = labels.fr;
        element.label.en = labels.fr;
      }
      if(element.plugin=="emundus_fileupload") {
        this.elementAssociateDocUpdateForm.name.en=labels.en;
        this.elementAssociateDocUpdateForm.name.fr=labels.fr
      }

      axios({
        method: "post",
        url:
            "index.php?option=com_emundus&controller=formbuilder&task=formsTrad",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          element: element.id,
          labelTofind: element.label_tag,
          NewSubLabel: labels
        })
      }).then((rep) => {
        if(rep.data.status == 0){
          axios({
            method: "post",
            url: "index.php?option=com_emundus&controller=formbuilder&task=updateelementlabelwithouttranslation",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              eid: element.id,
              label: element.label[this.actualLanguage]
            })
          }).then(() => {
            axios({
              method: "get",
              url: "index.php?option=com_emundus&controller=formbuilder&task=getElement",
              params: {
                element: element.id,
                gid: element.group_id
              },
              paramsSerializer: params => {
                return qs.stringify(params);
              }
            }).then(response => {
              element.label_value = response.data.label_value;
              this.$emit(
                  "show",
                  "foo-velocity",
                  "success",
                  this.translations.updateSuccess,
                  this.translations.update
              );
            });
          });
        } else {

          if(element.plugin=="emundus_fileupload") {
            let types = [];
            Object.keys(this.elementAssociateDocUpdateForm.selectedTypes).forEach(key => {
              if (this.elementAssociateDocUpdateForm.selectedTypes[key] == true) {
                types.push(key);
              }
            });

            let updateparams = {
              document: this.elementAssociateDocUpdateForm,
              types: types,
              cid: this.cid,
              pid: this.prid,
              did: element.params.attachmentId,
              text_fr: labels.fr,
              text_en: labels.en
            };
            this.updateAssociateDocElement(updateparams);
          }
          axios({
            method: "get",
            url: "index.php?option=com_emundus&controller=formbuilder&task=getElement",
            params: {
              element: element.id,
              gid: element.group_id
            },
            paramsSerializer: params => {
              return qs.stringify(params);
            }
          }).then(response => {
            this.$set(element,'element',response.data.element);
            element.label_value = response.data.label_value;
            this.$emit(
                "show",
                "foo-velocity",
                "success",
                this.translations.updateSuccess,
                this.translations.update
            );
            this.can_translate.label = false;
          });
        }
        this.clickUpdatingLabel = false;
      }).catch(e => {
        this.$emit(
            "show",
            "foo-velocity",
            "error",
            this.translations.updateFailed,
            this.translations.updating
        );
      });
    },

    reloadElement(element){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=formbuilder&task=getElement",
        params: {
          element: element.id,
          gid: element.group_id
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        if(response.data.plugin === 'databasejoin' && this.repeat === false){
          // Check variables
          this.repeat = true;
          this.reloadElement(element);
        }

        else{
          this.$set(element,'element',response.data.element);
          element = response.data;
          this.$set(this.keyElements,'element' + element.id,this.keyElements['element' + element.id] + 1)
        }
      }).catch(e => {
        this.$emit(
            "show",
            "foo-velocity",
            "error",
            this.translations.updateFailed,
            this.translations.updating
        );
      });
    },
    //

    // Group Update
    updateLabelGroup(group) {
      let labels = group.label;
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus&controller=formbuilder&task=formsTrad",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          group: group.group_id,
          labelTofind: group.group_tag,
          NewSubLabel: labels
        })
      }).then((rep) => {
        if(rep.data.status == 0){
          axios({
            method: "post",
            url: "index.php?option=com_emundus&controller=formbuilder&task=updategrouplabelwithouttranslation",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              gid: group.group_id,
              label: group.label[this.actualLanguage]
            })
          });
        }

        this.$emit(
            "show",
            "foo-velocity",
            "success",
            this.translations.updateSuccess,
            this.translations.update
        );
        group.group_showLegend = group.label[this.actualLanguage];
        this.can_translate.label_group = false;
        this.updateGroup = false;
      }).catch(e => {
        this.$emit(
            "show",
            "foo-velocity",
            "error",
            this.translations.updateFailed,
            this.translations.updating
        );
      });
    },

    deleteAGroup(group,index){
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEGROUP"),
        text: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEGROUPWARNING"),
        type: "warning",
        showCancelButton: true,
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
      }).then(result => {
        if (result.value) {
          axios({
            method: "post",
            url:
                "index.php?option=com_emundus&controller=formbuilder&task=deleteGroup",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              gid: group.group_id,
            })
          }).then(() => {
            Swal.fire({
              title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_GROUPDELETED"),
              type: "success",
              showConfirmButton: false,
              timer: 2000
            }).then(() => {
              this.groups.splice(index,1);
              delete this.object_json.Groups['group_' + group.group_id];
              this.updateGroup = false;
              this.$forceUpdate();
            });
          }).catch(e => {});
        }
      });
    },

    updateGroupsOrder() {
      var groups = this.groups.map((group, index) => {
        group.ordering = index + 2;
        return { id: group.group_id, order: index + 2 };
      });


      axios({
        method: "post",
        url:
            "index.php?option=com_emundus&controller=formbuilder&task=reordergroups",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          groups: groups,
          fid: this.object_json.id
        })
      }).then(response => {
        this.$emit(
            "show",
            "foo-velocity",
            "success",
            this.translations.orderSuccess,
            this.translations.update
        );
      }).catch(e => {
        this.$emit(
            "show",
            "foo-velocity",
            "error",
            this.translations.orderFailed,
            this.translations.updating
        );
      });
    },

    enableRepatedGroup(group){
      if(!group.repeat_group) {
        Swal.fire({
          title: this.translate("COM_EMUNDUS_ONBOARD_REPEAT_GROUP"),
          text: this.translate("COM_EMUNDUS_ONBOARD_REPEAT_GROUP_MESSAGE"),
          type: "info",
          showCancelButton: true,
          confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
          cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
          reverseButtons: true,
          customClass: {
            title: 'em-swal-title',
            cancelButton: 'em-swal-cancel-button',
            confirmButton: 'em-swal-confirm-button',
          },
        }).then(result => {
          if(result.value) {
            axios({
              method: "POST",
              url: "index.php?option=com_emundus&controller=formbuilder&task=enablegrouprepeat",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({
                gid: group.group_id,
              })
            }).then((result) => {
              if(result.data.status == true){
                group.repeat_group = 1;
                this.$emit(
                    "show",
                    "foo-velocity",
                    "success",
                    this.translations.updateSuccess,
                    this.translations.update
                );
                group.group_showLegend = group.label[this.actualLanguage];
                this.can_translate.label_group = false;
                this.updateGroup = false;
              }
            });
          }
        });
      } else {
        Swal.fire({
          title: this.translate("COM_EMUNDUS_ONBOARD_REPEAT_GROUP_DISABLE"),
          text: this.translate("COM_EMUNDUS_ONBOARD_REPEAT_GROUP_MESSAGE_DISABLE"),
          type: "info",
          showCancelButton: true,
          confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
          cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
          reverseButtons: true,
          customClass: {
            title: 'em-swal-title',
            cancelButton: 'em-swal-cancel-button',
            confirmButton: 'em-swal-confirm-button',
          },
        }).then(result => {
          if(result.value) {
            axios({
              method: "POST",
              url: "index.php?option=com_emundus&controller=formbuilder&task=disablegrouprepeat",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({
                gid: group.group_id,
              })
            }).then((result) => {
              if(result.data.status == true){
                group.repeat_group = 0;
                this.$emit(
                    "show",
                    "foo-velocity",
                    "success",
                    this.translations.updateSuccess,
                    this.translations.update
                );
                group.group_showLegend = group.label[this.actualLanguage];
                this.can_translate.label_group = false;
                this.updateGroup = false;
              }
            });
          }
        });
      }
    },
    //

    // Display/Hide group
    displayHideGroup(group){
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DISPLAY_HIDE"),
        text: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DISPLAY_HIDE_MESSAGE"),
        type: "info",
        showCancelButton: true,
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
      }).then(result => {
        if(result.value) {
          axios({
            method: "POST",
            url: "index.php?option=com_emundus&controller=formbuilder&task=displayhidegroup",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              gid: group.group_id,
            })
          }).then((result) => {
            group.hidden_group = result.data.status;
            this.$emit(
                "show",
                "foo-velocity",
                "success",
                this.translations.updateSuccess,
                this.translations.update
            );
          });
        }
      });
    },
    //

    // Page trigger
    updateLabelPage(page) {
      let labels = page.show_title.label;
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus&controller=formbuilder&task=formsTrad",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          page: page.id,
          labelTofind: page.show_title.titleraw,
          NewSubLabel: labels
        })
      }).then((rep) => {
        if(rep.data.status == 0){
          axios({
            method: "post",
            url: "index.php?option=com_emundus&controller=formbuilder&task=updatepagelabelwithouttranslation",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              pid: page.id,
              label: page.show_title.label[this.actualLanguage]
            })
          });
        }
        axios({
          method: "post",
          url:
              "index.php?option=com_emundus&controller=formbuilder&task=updatemenulabel",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            pid: page.id,
            label: labels
          })
        }).then(() => {
          this.$emit(
              "show",
              "foo-velocity",
              "success",
              this.translations.updateSuccess,
              this.translations.update
          );
          page.show_title.value = page.show_title.label[this.actualLanguage];
          page.label = page.show_title.label[this.actualLanguage];
          this.updatePage = false;
        });
      }).catch(e => {
        this.$emit(
            "show",
            "foo-velocity",
            "error",
            this.translations.updateFailed,
            this.translations.updating
        );
      });
    },

    updateIntroValuePage(page) {
      let intros = {
        fr: page.intro.fr,
        en: page.intro.en
      }
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus&controller=formbuilder&task=formsTrad",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          labelTofind: page.intro_raw,
          NewSubLabel: intros
        })
      }).then((rep) => {
        if(rep.data.status == 0){
          axios({
            method: "post",
            url: "index.php?option=com_emundus&controller=formbuilder&task=updatepageintrowithouttranslation",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              pid: page.id,
              intro: page.intro.fr
            })
          });
        }
        this.$emit(
            "show",
            "foo-velocity",
            "success",
            this.translations.updateSuccess,
            this.translations.update
        );
        page.intro_value = page.intro[this.actualLanguage];
        this.updateIntroPage = false;
      }).catch(e => {
        this.$emit(
            "show",
            "foo-velocity",
            "error",
            this.translations.updateFailed,
            this.translations.updating
        );
      });
    },

    getDataObject: _.debounce(function() {
      this.object_json = this.object.object;
      this.getElementsArray();
    }, 500),
    getApiData: _.debounce(function() {
      this.$emit(
          "show",
          "foo-velocity",
          "",
          this.translations.updating,
          this.translations.update
      );
      let ellink = this.object.link.replace("fabrik","emundus");
      axios.get(ellink + "&format=vue_jsonclean").then(r => {
        this.object_json = r.data;
        this.$emit("UpdateUxf");
        this.$emit(
            "show",
            "foo-velocity",
            "success",
            this.translations.updateSuccess,
            this.translations.update
        );
      });
    }, 1000),

    getElementsArray(){
      Object.keys(this.object_json.Groups).forEach(group => {
        this.object_json.Groups[group].elts = [];
        this.openGroup[this.object_json.Groups[group].group_id] = true;
        Object.keys(this.object_json.Groups[group].elements).forEach(element => {
          this.object_json.Groups[group].elts.push(this.object_json.Groups[group].elements[element]);
          this.keyElements[element] = 0;
        });
      });
    },

    // Dynamic actions
    enableActionBar(index) {
      if(!this.clickUpdatingLabel && !this.updateGroup && !this.updateIntroPage && !this.updatePage) {
        this.hoverUpdating = true;
        this.indexHighlight = index;
      }
    },
    disableActionBar() {
      if(!this.clickUpdatingLabel && !this.updateGroup && !this.updateIntroPage && !this.updatePage) {
        this.hoverUpdating = false;
        this.clickUpdatingLabel = false;
        this.indexHighlight = 0;
        this.can_translate.label = false;
      }
    },
    enableLabelInput(eid) {
      if(!this.updateGroup && !this.updateIntroPage && !this.updatePage) {
        this.clickUpdatingLabel = true;
        setTimeout(() => {
          document.getElementById('label_' + eid).focus();
        }, 100);
      }
    },
    enableTranslationLabel(eid) {
      this.can_translate.label = !this.can_translate.label;
      if(!this.can_translate.label) {
        setTimeout(() => {
          document.getElementById('label_' + eid).focus();
        },100);
      }
    },
    enableUpdatingPage(page) {
      if(!this.clickUpdatingLabel && !this.updateGroup && !this.updateIntroPage) {
        this.updatePage = true;
        this.indexPage = page.id;
        setTimeout(() => {
          document.getElementById('update_input_' + page.id).focus();
        }, 100);
      }
    },
    enableUpdatingPageIntro(page) {
      if(!this.clickUpdatingLabel && !this.updateGroup && !this.updatePage) {
        this.updateIntroPage = true;
        this.indexPage = page.id;
        setTimeout(() => {
          document.getElementById('update_intro_' + page.id).focus();
        }, 100);
      }
    },
    enableTranslationPage(pid) {
      this.can_translate.label_page = !this.can_translate.label_page;
      if(!this.can_translate.label_page) {
        setTimeout(() => {
          document.getElementById('update_input_' + pid).focus();
        },100);
      }
    },
    enableTranslationPageIntro(pid) {
      this.can_translate.intro_page = !this.can_translate.intro_page;
      if(!this.can_translate.intro_page) {
        setTimeout(() => {
          document.getElementById('update_intro_' + pid).focus();
        },100);
      }
    },
    enableUpdatingGroup(group) {
      if(!this.clickUpdatingLabel && !this.updateIntroPage && !this.updatePage) {
        this.updateGroup = true;
        this.indexGroup = group.group_id;
        setTimeout(() => {
          document.getElementById('update_input_' + group.group_id).focus();
        }, 100);
      }
    },
    enableTranslationGroup(gid) {
      this.can_translate.label_group = !this.can_translate.label_group;
      if(!this.can_translate.label_group) {
        setTimeout(() => {
          document.getElementById('update_input_' + gid).focus();
        },100);
      }
    },
    enableGroupHover(group) {
      if(!this.clickUpdatingLabel && !this.updateGroup && !this.updateIntroPage && !this.updatePage) {
        this.hoverGroup = true;
        this.indexGroup = group;
      }
    },
    disableGroupHover() {
      if(!this.clickUpdatingLabel && !this.updateGroup && !this.updateIntroPage && !this.updatePage) {
        this.hoverGroup = false;
        this.updateGroup = false;
        this.indexGroup = -1;
      }
    },
    handleGroup(gid){
      if(!this.updateGroup) {
        this.openGroup[gid] ? this.$set(this.openGroup,gid,false) : this.$set(this.openGroup,gid,true)
      }
    },
    startGroupDrag() {
      this.draggable = true;
    },
    //

    // Draggable trigger
    SomethingChange: function(evt) {
      console.log(evt);
      let elt_id = evt.item.childNodes[1].id
      this.groups.forEach(group => {
        group.elts.forEach(element => {
          if(element.id == elt_id){
            this.updateElementsOrder(group.group_id,group.elts, element);
          }
        })
      });
      this.draggable = false;
    },
    SomethingChangeInGroup: function() {
      this.draggable = false;
      this.updateGroupsOrder();
      Object.keys(this.openGroup).forEach((group,key) => {
        this.openGroup[group] = true;
      });
    },
    //

    getAccess(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=form&task=getAccess",
      }).then(response => {
        this.sysaccess = response.data.access;
      });
    },

    show(group, type, text, title) {
      this.$emit("show", group, type, text, title);
    },
  },
  created() {
    if(!_.isEmpty(this.object.object)) {
      this.getDataObject();
      this.getAccess();
    }
  },
  watch: {
    object: function() {
      this.getDataObject();
    },
    UpdateUx: function() {
      if (this.UpdateUx === true) {
        this.getApiData();
      }
    }
  },
  computed: {
    orderedGroups: function () {
      return _.orderBy(this.groups, 'ordering')
    },
  }
};
</script>

<style lang="scss">
.em-handle-group{
  position: absolute;
}
.element-updating{
  border: solid 2px var(--main-500) !important;
  border-radius: 4px;
}
.em-transparent-border-2{
  border: solid 2px transparent;
}
.em-no-elements-in-group{
  text-align: center;
  margin-top: 16px;
  padding: 24px 0;
  border: dashed 2px #919191;
  border-radius: 4px;
}
.em-list-elements{
  min-height: 100px;
  display: block;
  width: 100%;
}
.unpublished {
  background: #C5C8CE;
  border-radius: 5px;
}

.radio.btn-radio.btn-group label span{
  margin-top: 0 !important;
}

.fabrikElement.plugin-display .fabrikinput {
  height: auto !important;
  border: unset !important;
}
</style>
