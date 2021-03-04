<template>
  <div id="BuilderViewer" class="BuilderViewer">
    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css"
    />
    <div v-if="object_json.show_page_heading"
      :class="object_json.show_page_heading.class"
      v-html="object_json.show_page_heading.page_heading"
    />
    <div class="d-flex header-form-page mb-1" v-if="eval == 0 && !updatePage">
      <h2 v-if="object_json.show_title" class="page_header mr-1" @click="enableUpdatingPage(object_json)" v-html="object_json.show_title.value" />
      <span @click="$emit('modalOpen');$modal.show('modalSide' + object.rgt)" :title="Edit" class="cta-block pointer" style="font-size: 16px">
        <em class="fas fa-pen" data-toggle="tooltip" data-placement="top"></em>
      </span>
    </div>
    <div style="width: max-content;margin-left: 20px" v-show="updatePage && indexPage == object_json.id">
      <div class="input-can-translate" style="margin-top: 40px">
        <input v-if="object_json.show_title" v-model="object_json.show_title.label[actualLanguage]" class="form__input field-general w-input" style="width: 400px;" :class="translate.label_page ? '' : 'mb-1'" @keyup.enter="updateLabelPage(object_json)" :id="'update_input_' + object_json.id"/>
        <button class="translate-icon" v-if="manyLanguages !== '0'" :class="translate.label_page ? 'translate-icon-selected': ' translate-builder'" type="button" @click="enableTranslationPage(object_json.id)"></button>
        <div class="d-flex actions-update-label" :class="manyLanguages !== '0' ? '' : 'ml-10px'" :style="translate.label_page ? 'margin-bottom: 6px' : 'margin-bottom: 12px'">
          <a @click="updateLabelPage(object_json)" :title="Validate">
            <em class="fas fa-check mr-1" data-toggle="tooltip" data-placement="top"></em>
          </a>
        </div>
      </div>
      <translation v-if="object_json.show_title"  :label="object_json.show_title.label" :actualLanguage="actualLanguage" v-if="translate.label_page"></translation>
    </div>

    <p v-if="eval == 0 && !updateIntroPage" class="introP" v-html="object_json.intro_value" @click="enableUpdatingPageIntro(object_json)" />
    <div style="width: max-content;margin-left: 20px" v-show="updateIntroPage && indexPage == object_json.id">
      <div class="input-can-translate" style="margin-top: 10px">
        <textarea v-if="object_json.intro" v-model="object_json.intro[actualLanguage]" class="form__input field-general w-input" style="width: 400px;" :class="translate.intro_page ? '' : 'mb-1'" :id="'update_intro_' + object_json.id"/>
        <button class="translate-icon" v-if="manyLanguages !== '0'" :class="translate.intro_page ? 'translate-icon-selected': ' translate-builder'" type="button" @click="enableTranslationPageIntro(object_json.id)"></button>
        <div class="d-flex actions-update-label" :class="manyLanguages !== '0' ? '' : 'ml-10px'" :style="translate.intro_page ? 'margin-bottom: 6px' : 'margin-bottom: 12px'">
          <a @click="updateIntroValuePage(object_json)" :title="Validate">
            <em class="fas fa-check mr-1" data-toggle="tooltip" data-placement="top"></em>
          </a>
        </div>
      </div>
      <translation v-if="object_json.intro"  :label="object_json.intro" :actualLanguage="actualLanguage" v-if="translate.intro_page"></translation>
    </div>

    <form method="post" v-on:submit.prevent object_json.attribs class="form-page" :id="'form_' + object_json.id" :style="eval == 1 ? 'margin-top: 30px' : ''">
      <div v-if="object_json.plugintop" v-html="object_json.plugintop"></div>
      <draggable
              handle=".handle"
              v-model="groups"
              @start="startGroupDrag"
              @end="SomethingChangeInGroup">
          <div v-for="(group,index_group) in orderedGroups"
               v-bind:key="group.index"
               @mouseover="enableGroupHover(group.group_id)"
               @mouseleave="disableGroupHover()">
            <fieldset :class="[group.group_class]" :id="'group_'+group.group_id" :style="group.group_css" style="background-size: 20px; width: 100%">
              <div class="d-flex justify-content-between" :class="updateGroup && indexGroup == group.group_id ? 'hidden' : ''" style="width: 100%">
                <div class="d-flex">
                  <span v-show="hoverGroup && indexGroup == group.group_id" class="icon-handle-group">
                    <em class="fas fa-grip-vertical handle"></em>
                  </span>
                  <legend
                    @click="enableUpdatingGroup(group)"
                    v-if="group.group_showLegend"
                    class="legend ViewerLegend">
                    {{group.group_showLegend}}
                  </legend>
                  <a @click="enableUpdatingGroup(group)" style="margin-left: 1em;font-size: 16px" :title="Edit" class="cta-block pointer">
                    <em class="fas fa-pen" data-toggle="tooltip" data-placement="top"></em>
                  </a>
                  <a :class="group.repeat_group ? 'active-repeat' : ''" class="group-repeat-icon ml-10px pointer" :title="RepeatedGroup" @click="enableRepatedGroup(group)">
                    <em class="fas fa-clone" data-toggle="tooltip" data-placement="top"></em>
                  </a>
                </div>
<!--                <div>
                  <div v-show="!openGroup[group.group_id]">
                    <em class="fas fa-chevron-right"></em>
                  </div>
                  <div v-show="openGroup[group.group_id]">
                    <em class="fas fa-chevron-down"></em>
                  </div>
                </div>-->
              </div>
              <div style="width: max-content" v-show="updateGroup && indexGroup == group.group_id">
                <div class="input-can-translate">
                  <input v-model="group.label[actualLanguage]" class="form__input field-general w-input" style="width: 400px;" :class="translate.label_group ? '' : 'mb-1'" @keyup.enter="updateLabelGroup(group)" :id="'update_input_' + group.group_id"/>
                  <button class="translate-icon" v-if="manyLanguages !== '0'" :class="translate.label_group ? 'translate-icon-selected': ' translate-builder'" type="button" @click="enableTranslationGroup(group.group_id)"></button>
                  <div class="d-flex actions-update-label" :class="manyLanguages !== '0' ? '' : 'ml-10px'" :style="translate.label_group ? 'margin-bottom: 6px' : 'margin-bottom: 12px'">
                    <a @click="updateLabelGroup(group)" :title="Validate">
                      <em class="fas fa-check mr-1" data-toggle="tooltip" data-placement="top"></em>
                    </a>
                    <a @click="enableRepatedGroup(group)" :class="group.repeat_group ? 'active-repeat' : ''" class="group-repeat-icon" :title="RepeatGroup" v-if="files == 0">
                      <em class="fas fa-clone" data-toggle="tooltip" data-placement="top"></em>
                    </a>
                    <a @click="deleteAGroup(group,index_group)" style="margin-left: 1em;color: black" v-if="files == 0" :title="Delete">
                      <em class="fas fa-trash-alt" data-toggle="tooltip" data-placement="top"></em>
                    </a>
                  </div>
                </div>
                <translation :label="group.label" :actualLanguage="actualLanguage" v-if="translate.label_group"></translation>
              </div>
              <div v-if="group.group_intro" class="groupintro">{{group.group_intro}}</div>

              <template v-if="typeof group.elts !== 'undefined'">
                <div v-if="group.elts.length == 0" class="no-elements-tip">{{ NoElementsTips }}</div>
              </template>

              <div class="elements-block" v-show="openGroup[group.group_id]">
                <draggable
                        handle=".handle"
                        v-model="group.elts"
                        @start="draggable = true"
                        @end="SomethingChange"
                        group="items"
                        class="draggable-span">
                  <transition-group :name="'slide-down'" type="transition">
                  <div v-for="(element,index) in group.elts"
                       v-bind:key="element.id"
                       v-show="element.hidden === false"
                       @mouseover="enableActionBar(element.id)"
                       @mouseleave="disableActionBar()"
                       class="builder-item-element">
                    <modalEditElement
                            :ID="element.id"
                            :gid="element.group_id"
                            :files="files"
                            :manyLanguages="manyLanguages"
                            :actualLanguage="actualLanguage"
                            @reloadElement="reloadElement(element)"
                            @publishUnpublishEvent="publishUnpublishEvent(element)"
                            @updateRequireEvent="updateRequireEvent(element)"
                            @modalClosed="$emit('modalClosed')"
                            @show="show"
                            :id="element.id"
                            :key="keyElements['element' + element.id]"
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
                    <div class="d-flex builder-item-element__properties" :class="{'element-updating': hoverUpdating && indexHighlight == element.id, 'unpublished': !element.publish, 'draggable-item': draggable && indexHighlight == element.id}">
                      <span :class="element.publish ? 'icon-handle' : 'icon-handle-unpublished'" v-show="hoverUpdating && indexHighlight == element.id && !clickUpdatingLabel">
                        <em class="fas fa-grip-vertical handle"></em>
                      </span>
                      <div class="w-100">
                        <div class="d-flex" style="align-items: baseline" :class="clickUpdatingLabel && indexHighlight == element.id ? 'hidden' : ''">
                          <span v-if="element.label_value" @click="enableLabelInput(element.id)" v-html="element.label_value" v-show="element.labelsAbove != 2"></span>
                          <a @click="enableLabelInput(element.id)" :style="hoverUpdating && indexHighlight == element.id && !clickUpdatingLabel ? 'opacity: 1' : 'opacity: 0'" :title="Edit" class="cta-block pointer" style="font-size: 16px">
                            <em class="fas fa-pen ml-10px" data-toggle="tooltip" data-placement="top"></em>
                          </a>
                        </div>
                        <div class="input-can-translate" v-show="clickUpdatingLabel && indexHighlight == element.id">
                          <input v-model="element.label[actualLanguage]" class="form__input field-general w-input" :class="translate.label ? '' : 'mb-1'" @keyup.enter="updateLabelElement(element)" :id="'label_' + element.id"/>
                          <button class="translate-icon" v-if="manyLanguages !== '0'" :class="translate.label ? 'translate-icon-selected': ' translate-builder'" type="button" @click="enableTranslationLabel(element.id)"></button>
                          <div class="d-flex actions-update-label" :class="manyLanguages !== '0' ? '' : 'ml-10px'" :style="translate.label ? 'margin-bottom: 6px' : 'margin-bottom: 12px'">
                            <a @click="updateLabelElement(element)" :title="Validate">
                              <em class="fas fa-check" data-toggle="tooltip" data-placement="top"></em>
                            </a>
                          </div>
                        </div>
                        <translation :label="element.label" :actualLanguage="actualLanguage"v-if="translate.label && clickUpdatingLabel && indexHighlight == element.id"></translation>
<!--                        <div v-if="element.params.date_table_format">-->
<!--                          <date-picker v-model="date" :config="options"></date-picker>-->
<!--                        </div>-->
                        <div v-else-if="element.labelsAbove == 0" class="controls">
                          <div v-if="element.error" class="fabrikElement" v-html="element.error"></div>
                          <div v-if="element.element" :class="element.errorClass" v-html="element.element"></div>
                          <span v-if="element.tipSide" v-html="element.tipSide"></span>
                        </div>
                        <span v-else class="d-flex w-100">
                          <div v-if="element.element" class="fabrikElement" v-html="element.error"></div>
                          <div v-if="element.element" :class="element.errorClass" v-html="element.element" class="w-100"></div>
                          <span v-if="element.tipSide" v-html="element.tipSide"></span>
                        </span>
                        <span v-if="element.tipBelow" v-html="element.tipBelow"></span>
                      </div>
                      <div class="actions-item-bar" :style="hoverUpdating && indexHighlight == element.id ? 'opacity: 1' : 'opacity: 0'">
                        <!--                      <a class="d-flex mr-2" v-if="element.plugin != 'display'">
                                                <div class="toggle">
                                                  <input type="checkbox" class="check" v-model="element.FRequire" @click="updateRequireElement(element)"/>
                                                  <strong class="b switch"></strong>
                                                  <strong class="b track"></strong>
                                                </div>
                                                <span class="ml-10px">{{Required}}</span>
                                              </a>-->
                        <a class="d-flex mr-2 mb-1" v-if="element.plugin != 'calc'" @click="openParameters(element)" :title="Settings">
                          <em class="fas fa-cog settings-elt"></em>
                        </a>
                        <!--                      <a class="d-flex mr-2" v-if="element.plugin != 'calc'" @click="openDuplicate(element)">
                                                <em class="fas fa-copy"></em>
                                                <span class="ml-10px">{{Duplicate}}</span>
                                              </a>-->
                        <a class="d-flex mr-2" style="color: red" @click="deleteElement(element,index)" v-if="files == 0" :title="Delete">
                          <em class="fas fa-trash-alt delete-icon-elt"></em>
                        </a>
                        <a class="d-flex mr-2 mt-1" target="_blank" :href="'/administrator/index.php?option=com_fabrik&view=element&layout=edit&id=' + element.id" v-if="sysaccess">
                          <em class="fas fa-link settings-elt"></em>
                        </a>
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
    manyLanguages: Number
  },
  components: {
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
      translate: {
        label: false,
        label_group: false,
        label_page: false,
        intro_page: false,
      },

      // TRANSLATIONS
      update: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATE"),
      updating: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATING"),
      updateSuccess: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATESUCESS"),
      orderSuccess: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ORDERSUCESS"),
      orderFailed: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ORDERFAILED"),
      updateFailed: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATEFAILED"),
      sidemenuhelp: Joomla.JText._("COM_EMUNDUS_ONBOARD_SIDEMENUHELP"),
      TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
      Unpublish: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH"),
      Publish: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTION_PUBLISH"),
      Required: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTIONS_REQUIRED"),
      Settings: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTIONS_SETTINGS"),
      Delete: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTION_DELETE"),
      Edit: Joomla.JText._("COM_EMUNDUS_ONBOARD_MODIFY"),
      Cancel: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL"),
      Validate: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
      RepeatGroup: Joomla.JText._("COM_EMUNDUS_ONBOARD_REPEAT_GROUP"),
      RepeatedGroup: Joomla.JText._("COM_EMUNDUS_ONBOARD_REPEATED_GROUP"),
      Duplicate: Joomla.JText._("COM_EMUNDUS_ONBOARD_DUPLICATE"),
      NoElementsTips: Joomla.JText._("COM_EMUNDUS_ONBOARD_NO_ELEMENTS_TIPS"),
    };
  },
  methods: {
    // Elements update
    async updateElementsOrder(group, list, elt) {
      var elements = list.map((element, index) => {
        return { id: element.id, order: index + 1 };
      });
      axios({
        method: "post",
        url:
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=updateOrder",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          elements: elements,
          group_id: group
        })
      }).then(response => {
        this.$emit(
                "show",
                "foo-velocity",
                "success",
                this.orderSuccess,
                this.update
        );
        let ellink = this.object.link.replace("fabrik","emundus_onboard");
        axios.get(ellink + "&format=vue_jsonclean").then(r => {
          this.groups.forEach(grp => {
              this.$set(this.object_json.Groups['group_' + grp.group_id], 'elements', r.data.Groups['group_' + grp.group_id].elements)
          });
        });
        elt.group_id = group;
      }).catch(e => {
        /*this.$emit(
                "show",
                "foo-velocity",
                "error",
                this.orderFailed,
                this.updating
        );
        console.log(e);*/
      });
    },

    updateRequireEvent(element) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getElement",
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
            this.updateSuccess,
            this.update
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

    deleteElement(element,index) {
      if(this.clickUpdatingLabel) {
          this.updateLabelElement(element);
      }
      Swal.fire({
        title: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DELETEELEMENT"),
        text: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANT_REVERT"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#de6339',
        confirmButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          axios({
            method: "post",
            url:
                    "index.php?option=com_emundus_onboard&controller=formbuilder&task=deleteElement",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              element: element.id,
            })
          }).then(() => {
            Swal.fire({
              title: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ELEMENTDELETED"),
              type: "success",
              showConfirmButton: false,
              timer: 2000
            }).then(() => {
              this.object_json.Groups['group_' + element.group_id].elts.splice(index,1);
              delete this.object_json.Groups['group_' + element.group_id].elements['element' + element.id];
              this.$forceUpdate();
            });
          }).catch(e => {
            console.log(e);
          });
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
      if(this.clickUpdatingLabel) {
        this.updateLabelElement(element);
      }
      this.repeat = false;
      this.$emit('modalOpen')
      this.$modal.show('modalEditElement' + element.id)
    },

    updateLabelElement(element) {
      let labels = {
        fr: element.label.fr,
        en: element.label.en
      }
      if(labels.en === 'Unnamed item'){
        labels.en = labels.fr;
        element.label.en = labels.fr;
      }
      axios({
        method: "post",
        url:
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=formsTrad",
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
            url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=updateelementlabelwithouttranslation",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              eid: element.id,
              label: element.label.fr
            })
          }).then(() => {
            axios({
              method: "get",
              url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getElement",
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
                  this.updateSuccess,
                  this.update
              );
            });
          });
        } else {
          axios({
            method: "get",
            url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getElement",
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
                this.updateSuccess,
                this.update
            );
            this.translate.label = false;
          });
        }
        this.clickUpdatingLabel = false;
      }).catch(e => {
        this.$emit(
                "show",
                "foo-velocity",
                "error",
                this.updateFailed,
                this.updating
        );
        console.log(e);
      });
    },

    reloadElement(element){
       axios({
          method: "get",
          url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getElement",
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
          // //
          // else if(response.data.plugin === 'date' && this.repeat === false) {
          //   this.repeat = true;
          //   this.reloadElement(element);
          // }

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
                  this.updateFailed,
                  this.updating
          );
        console.log(e);
      });
    },
    //

    // Group Update
    updateLabelGroup(group) {
      let labels = {
        fr: group.label.fr,
        en: group.label.en
      }
      axios({
        method: "post",
        url:
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=formsTrad",
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
            url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=updategrouplabelwithouttranslation",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              gid: group.group_id,
              label: group.label.fr
            })
          });
        }

        this.$emit(
                "show",
                "foo-velocity",
                "success",
                this.updateSuccess,
                this.update
        );
        group.group_showLegend = group.label[this.actualLanguage];
        this.translate.label_group = false;
        this.updateGroup = false;
      }).catch(e => {
        this.$emit(
                "show",
                "foo-velocity",
                "error",
                this.updateFailed,
                this.updating
        );
        console.log(e);
      });
    },

    deleteAGroup(group,index){
      Swal.fire({
        title: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DELETEGROUP"),
        text: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DELETEGROUPWARNING"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#de6339',
        confirmButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          axios({
            method: "post",
            url:
                    "index.php?option=com_emundus_onboard&controller=formbuilder&task=deleteGroup",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              gid: group.group_id,
            })
          }).then(() => {
            Swal.fire({
              title: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_GROUPDELETED"),
              type: "success",
              showConfirmButton: false,
              timer: 2000
            }).then(() => {
              this.groups.splice(index,1);
              delete this.object_json.Groups['group_' + group.group_id];
              this.updateGroup = false;
              this.$forceUpdate();
            });
          }).catch(e => {
            console.log(e);
          });
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
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=reordergroups",
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
                this.orderSuccess,
                this.update
        );
      }).catch(e => {
        this.$emit(
                "show",
                "foo-velocity",
                "error",
                this.orderFailed,
                this.updating
        );
        console.log(e);
      });
    },

    enableRepatedGroup(group){
      if(!group.repeat_group) {
        Swal.fire({
          title: Joomla.JText._("COM_EMUNDUS_ONBOARD_REPEAT_GROUP"),
          text: Joomla.JText._("COM_EMUNDUS_ONBOARD_REPEAT_GROUP_MESSAGE"),
          type: "info",
          showCancelButton: true,
          confirmButtonColor: '#de6339',
          confirmButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
          cancelButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL"),
          reverseButtons: true
        }).then(result => {
          if(result.value) {
            axios({
              method: "POST",
              url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=enablegrouprepeat",
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
                        this.updateSuccess,
                        this.update
                );
                group.group_showLegend = group.label[this.actualLanguage];
                this.translate.label_group = false;
                this.updateGroup = false;
              }
            });
          }
        });
      } else {
        Swal.fire({
          title: Joomla.JText._("COM_EMUNDUS_ONBOARD_REPEAT_GROUP_DISABLE"),
          text: Joomla.JText._("COM_EMUNDUS_ONBOARD_REPEAT_GROUP_MESSAGE_DISABLE"),
          type: "info",
          showCancelButton: true,
          confirmButtonColor: '#de6339',
          confirmButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
          cancelButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL"),
          reverseButtons: true
        }).then(result => {
          if(result.value) {
            axios({
              method: "POST",
              url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=disablegrouprepeat",
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
                        this.updateSuccess,
                        this.update
                );
                group.group_showLegend = group.label[this.actualLanguage];
                this.translate.label_group = false;
                this.updateGroup = false;
              }
            });
          }
        });
      }
    },
    //

    // Page trigger
    updateLabelPage(page) {
      let labels = {
        fr: page.show_title.label.fr,
        en: page.show_title.label.en
      }
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus_onboard&controller=formbuilder&task=formsTrad",
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
            url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=updatepagelabelwithouttranslation",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              pid: page.id,
              label: page.show_title.label.fr
            })
          });
        }
            axios({
              method: "post",
              url:
                  "index.php?option=com_emundus_onboard&controller=formbuilder&task=updatemenulabel",
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
                  this.updateSuccess,
                  this.update
              );
              page.show_title.value = page.show_title.label[this.actualLanguage];
              this.updatePage = false;
            });
      }).catch(e => {
        this.$emit(
            "show",
            "foo-velocity",
            "error",
            this.updateFailed,
            this.updating
        );
        console.log(e);
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
            "index.php?option=com_emundus_onboard&controller=formbuilder&task=formsTrad",
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
            url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=updatepageintrowithouttranslation",
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
            this.updateSuccess,
            this.update
        );
        page.intro_value = page.intro[this.actualLanguage];
        this.updateIntroPage = false;
      }).catch(e => {
        this.$emit(
            "show",
            "foo-velocity",
            "error",
            this.updateFailed,
            this.updating
        );
        console.log(e);
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
        this.updating,
        this.update
      );
      let ellink = this.object.link.replace("fabrik","emundus_onboard");
      axios.get(ellink + "&format=vue_jsonclean").then(r => {
        this.object_json = r.data;
        this.$emit("UpdateUxf");
        this.$emit(
          "show",
          "foo-velocity",
          "success",
          this.updateSuccess,
          this.update
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
        this.translate.label = false;
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
      this.translate.label = !this.translate.label;
      if(this.translate.label) {
        /*setTimeout(() => {
          document.getElementById('label_en_' + eid).focus();
        },100);*/
      } else {
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
      this.translate.label_page = !this.translate.label_page;
      if(this.translate.label_page) {
        /*setTimeout(() => {
          document.getElementById('label_page_en_' + pid).focus();
        },100);*/
      } else {
        setTimeout(() => {
          document.getElementById('update_input_' + pid).focus();
        },100);
      }
    },
    enableTranslationPageIntro(pid) {
      this.translate.intro_page = !this.translate.intro_page;
      if(this.translate.intro_page) {
        /*setTimeout(() => {
          document.getElementById('label_page_en_' + pid).focus();
        },100);*/
      } else {
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
      this.translate.label_group = !this.translate.label_group;
      if(this.translate.label_group) {
        /*setTimeout(() => {
          document.getElementById('label_group_en_' + gid).focus();
        },100);*/
      } else {
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
      /*Object.keys(this.openGroup).forEach((group,key) => {
        this.openGroup[group] = false;
      });*/
      this.draggable = true;
    },
    //

    // Draggable trigger
    SomethingChange: function(evt) {
      let elt_id = evt.item.childNodes[0].id
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
        url: "index.php?option=com_emundus_onboard&controller=form&task=getAccess",
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

<style scoped lang="scss">
@import "../../assets/css/variables";

  .hidden {
    display: none;
  }
  .BuilderViewer {
    padding: 0 1%;
    border-radius: 5px;
  }
  .fa-pencil-alt{
    margin-top: 0.5em;
    color: #de6339;
    cursor: pointer;
  }
  .toggle{
    width: 30px;
    height: 17px;
    background-color: #fff;
    box-shadow: 0 0.9px 9.6px rgba(0, 0, 0, 0.02), 0 3.9px 22.8px rgba(0, 0, 0, 0.028), 0 9.9px 38.4px rgba(0, 0, 0, 0.035), 0 21.6px 54.2px rgba(0, 0, 0, 0.042), 0 45px 68.4px rgba(0, 0, 0, 0.05), 0 100px 80px rgba(0, 0, 0, 0.07);
  }
  .switch{
    width: 13px;
    background-color: #de6339;
  }
  .check:checked ~ .switch{
    left: 15px;
    background-color: #fff;
  }
  .dropdown-toggle-plugin{
    width: 30%;
    margin-left: 2em;
    height: 33px;
  }
  .icon-handle{
    color: #cecece;
    position: absolute;
    cursor: grab;
    left: auto;
    right: 50px;
  }
  .icon-handle-group{
    color: #cecece;
    position: absolute;
    cursor: grab;
    left: 15px;
  }
  .icon-handle-unpublished{
    color: #cecece;
    position: absolute;
    cursor: grab;
    margin-bottom: 30px;
    right: 50px;
  }
  .hidden{
    display: none;
  }
  .translate-icon-selected{
    top: -5px;
  }
</style>

