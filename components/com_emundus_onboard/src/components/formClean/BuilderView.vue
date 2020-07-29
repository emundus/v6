<template>
  <div id="BuilderViewer" class="BuilderViewer">
    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css"
    />
    <div v-if="object_json.show_page_heading"
      :class="object_json.show_page_heading.class"
      v-html="object_json.show_page_heading.page_heading"
    />
    <div class="d-flex">
      <h2 v-if="object_json.show_title" class="page_header" v-html="object_json.show_title.value" />
      <span @click="$modal.show('modalSide' + object.rgt)" :title="Edit">
        <em class="fas fa-pencil-alt" data-toggle="tooltip" data-placement="top"></em>
      </span>
    </div>
    <p v-if="object_json.intro" class="introP" v-html="object_json.intro" />

    <form method="post" v-on:submit.prevent object_json.attribs class="form-page">
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
              <div class="d-flex justify-content-between" :class="updateGroup && indexGroup == group.group_id ? 'hidden' : ''" style="width: 100%" @click="handleGroup(group.group_id)">
                <div class="d-flex">
                  <span v-show="hoverGroup && indexGroup == group.group_id" class="icon-handle-group">
                    <em class="fas fa-grip-vertical handle"></em>
                  </span>
                  <legend
                    v-if="group.group_showLegend"
                    class="legend ViewerLegend">
                    {{group.group_showLegend}}
                  </legend>
                  <a @click="enableUpdatingGroup(group)" style="margin-left: 1em" :title="Edit">
                    <em class="fas fa-pencil-alt" data-toggle="tooltip" data-placement="top"></em>
                  </a>
                </div>
                <div>
                  <div v-show="!openGroup[group.group_id]">
                    <em class="fas fa-chevron-right"></em>
                  </div>
                  <div v-show="openGroup[group.group_id]">
                    <em class="fas fa-chevron-down"></em>
                  </div>
                </div>
              </div>
              <div style="width: max-content" v-show="updateGroup && indexGroup == group.group_id">
                <div class="input-can-translate">
                  <input v-model="group.label_fr" class="form-control" style="width: 400px;" :class="translate.label_group ? '' : 'mb-1'" @keyup.enter="updateLabelGroup(group)" :id="'update_input_' + group.group_id"/>
                  <button class="translate-icon" :class="translate.label_group ? 'translate-icon-selected': ' translate-builder'" type="button" @click="translate.label_group = !translate.label_group"></button>
                  <div class="d-flex actions-update-label" :style="translate.label_group ? 'margin-bottom: 6px' : 'margin-bottom: 12px'">
                    <a @click="updateGroup = false;translate.label_group = false" :title="Cancel">
                      <em class="fas fa-times ml-10px" data-toggle="tooltip" data-placement="top"></em>
                    </a>
                    <a @click="updateLabelGroup(group)" :title="Validate">
                      <em class="fas fa-check ml-20px mr-1" data-toggle="tooltip" data-placement="top"></em>
                    </a>
                    <a @click="enableRepatedGroup(group)" :class="group.repeat_group ? 'active-repeat' : ''" class="group-repeat-icon" :title="RepeatGroup">
                      <em class="fas fa-clone" data-toggle="tooltip" data-placement="top"></em>
                    </a>
                    <a @click="deleteAGroup(group,index_group)" style="margin-left: 1em;color: black" v-if="files == 0" :title="Delete">
                      <em class="fas fa-trash-alt" data-toggle="tooltip" data-placement="top"></em>
                    </a>
                  </div>
                </div>
                <div class="inlineflex" v-if="translate.label_group">
                  <label class="translate-label">
                    {{TranslateEnglish}}
                  </label>
                  <em class="fas fa-sort-down"></em>
                </div>
                <div class="form-group mb-1" v-if="translate.label_group">
                  <input v-model="group.label_en" type="text" maxlength="40" class="form-control"/>
                </div>
              </div>
              <div v-if="group.group_intro" class="groupintro">{{group.group_intro}}</div>

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
                       class="builder-item-element"
                       :class="{'element-updating': hoverUpdating && indexHighlight == element.id, 'unpublished': !element.publish, 'draggable-item': draggable && indexHighlight == element.id}">
                    <modalEditElement
                            :ID="element.id"
                            :element="element"
                            :files="files"
                            @reloadElement="reloadElement(element)"
                            :id="element.id"
                    />
                    <div class="d-flex builder-item-element__properties">
                      <span :class="element.publish ? 'icon-handle' : 'icon-handle-unpublished'" v-show="hoverUpdating && indexHighlight == element.id">
                        <em class="fas fa-grip-vertical handle"></em>
                      </span>
                      <div class="w-100">
                        <div class="d-flex" style="align-items: baseline">
                          <span v-if="element.label" :class="clickUpdatingLabel && indexHighlight == element.id ? 'hidden' : ''" v-html="element.label" v-show="element.labelsAbove != 2"></span>
                          <a @click="enableLabelInput" :style="hoverUpdating && indexHighlight == element.id && !clickUpdatingLabel ? 'opacity: 1' : 'opacity: 0'" :title="Edit">
                            <em class="fas fa-pencil-alt ml-10px" data-toggle="tooltip" data-placement="top"></em>
                          </a>
                        </div>
                        <div class="input-can-translate" v-show="clickUpdatingLabel && indexHighlight == element.id">
                          <input v-model="element.label_fr" class="form-control" :class="translate.label ? '' : 'mb-1'" @keyup.enter="updateLabelElement(element)"/>
                          <button class="translate-icon" :class="translate.label ? 'translate-icon-selected': ' translate-builder'" type="button" @click="translate.label = !translate.label"></button>
                          <div class="d-flex actions-update-label" :style="translate.label ? 'margin-bottom: 6px' : 'margin-bottom: 12px'">
                            <a @click="clickUpdatingLabel = false;translate.label = false" :title="Cancel">
                              <em class="fas fa-times ml-20px" data-toggle="tooltip" data-placement="top"></em>
                            </a>
                            <a @click="updateLabelElement(element)" :title="Validate">
                              <em class="fas fa-check ml-20px" data-toggle="tooltip" data-placement="top"></em>
                            </a>
                          </div>
                        </div>
                        <div class="inlineflex" v-if="translate.label && clickUpdatingLabel && indexHighlight == element.id">
                          <label class="translate-label">
                            {{TranslateEnglish}}
                          </label>
                          <em class="fas fa-sort-down"></em>
                        </div>
                        <div class="form-group mb-1" v-if="translate.label && clickUpdatingLabel && indexHighlight == element.id">
                          <input v-model="element.label_en" type="text" maxlength="40" class="form__input field-general w-input"/>
                        </div>
                        <div v-if="element.params.date_table_format">
                          <date-picker v-model="date" :config="options"></date-picker>
                        </div>
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
                    </div>
                    <div class="actions-item-bar d-flex" :style="hoverUpdating && indexHighlight == element.id ? 'opacity: 1' : 'opacity: 0'">
                      <a class="d-flex mr-2 text-orange" @click="publishUnpublishElement(element)">
                        <em :class="[element.publish ? 'fa-eye-slash' : 'fa-eye','far']" :id="'publish_icon_' + element.id"></em>
                        <span class="ml-10px" v-if="element.publish">{{Unpublish}}</span>
                        <span class="ml-10px" v-if="!element.publish">{{Publish}}</span>
                      </a>
                      <a class="d-flex mr-2 text-orange">
                        <div class="toggle">
                          <input type="checkbox" class="check" v-model="element.FRequire" @click="updateRequireElement(element)"/>
                          <strong class="b switch"></strong>
                          <strong class="b track"></strong>
                        </div>
                        <span class="ml-10px">{{Required}}</span>
                      </a>
                      <a class="d-flex mr-2 text-orange" @click="repeat = false;$modal.show('modalEditElement' + element.id)">
                        <em class="fas fa-cog"></em>
                        <span class="ml-10px">{{Settings}}</span>
                      </a>
                      <a class="d-flex mr-2" style="color: black" @click="deleteElement(element,index)" v-if="files == 0">
                        <em class="fas fa-trash-alt"></em>
                        <span class="ml-10px">{{Delete}}</span>
                      </a>
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
    files: Number
  },
  components: {
    datePicker,
    draggable,
    modalEditElement
  },
  data() {
    return {
      object_json: "",
      openGroup: {},
      hoverUpdating: false,
      hoverGroup: false,
      lastIndex: 0,
      indexHighlight: 0,
      indexGroup: -1,
      clickUpdatingLabel: false,
      updateGroup: false,
      draggable: false,
      fieldChanges: false,
      repeat: false,
      date: new Date(),
      options: {
        format: "DD/MM/YYYY",
        useCurrent: false
      },
      translate: {
        label: false,
        label_group: false
      },
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
    };
  },
  methods: {
    // Elements update
    async updateElementsOrder(group, list) {
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
            //this.object_json.Groups['group_' + grp.group_id].elements = r.data.Groups['group_' + grp.group_id].elements;
          });
        });
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

    updateRequireElement(element) {
      setTimeout(() => {
        axios({
          method: "post",
          url:
                  "index.php?option=com_emundus_onboard&controller=formbuilder&task=changerequire",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            element: element
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
            element.label = response.data.label;
            this.$emit(
                    "show",
                    "foo-velocity",
                    "success",
                    this.updateSuccess,
                    this.update
            );
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
      }, 300);
    },

    publishUnpublishElement(element) {
      axios({
        method: "post",
        url:
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=publishunpublishelement",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          element: element.id,
        })
      }).then(response => {
        element.publish = !element.publish;
        if(element.publish){
          document.getElementById('publish_icon_' + element.id).classList.remove('fa-eye');
          document.getElementById('publish_icon_' + element.id).classList.add('fa-eye-slash');
        } else {
          document.getElementById('publish_icon_' + element.id).classList.add('fa-eye');
          document.getElementById('publish_icon_' + element.id).classList.remove('fa-eye-slash');
        }
        this.$emit(
                "show",
                "foo-velocity",
                "success",
                this.updateSuccess,
                this.update
        );
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

    deleteElement(element,index) {
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

    updateLabelElement(element) {
      let labels = {
        fr: element.label_fr,
        en: element.label_en
      }
      if(labels.en === 'Unnamed item'){
        labels.en = labels.fr;
        element.label_en = labels.fr;
      }
      axios({
        method: "post",
        url:
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=formsTrad",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          labelTofind: element.label_tag,
          NewSubLabel: labels
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
          element.label = response.data.label;
          this.$emit(
                  "show",
                  "foo-velocity",
                  "success",
                  this.updateSuccess,
                  this.update
          );
          this.translate.label = false;
          this.clickUpdatingLabel = false;
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
            this.repeat = true;
            this.reloadElement(element)
          } else{
            element.element = response.data.element;
            element = response.data;
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
        fr: group.label_fr,
        en: group.label_en
      }
      axios({
        method: "post",
        url:
                "index.php?option=com_emundus_onboard&controller=formbuilder&task=formsTrad",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          labelTofind: group.group_tag,
          NewSubLabel: labels
        })
      }).then(() => {
        this.$emit(
                "show",
                "foo-velocity",
                "success",
                this.updateSuccess,
                this.update
        );
        group.group_showLegend = group.label_fr;
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
              }
            });
          }
        });
      }
    },
    //

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
        });
      });
    },

    // Dynamic actions
    enableActionBar(index) {
      this.hoverUpdating = true;
      this.indexHighlight = index;
      this.lastIndex = index;
    },
    disableActionBar() {
      if(!this.clickUpdatingLabel) {
        this.hoverUpdating = false;
        this.clickUpdatingLabel = false;
        this.indexHighlight = 0;
        this.translate.label = false;
      }
    },
    enableLabelInput() {
      this.clickUpdatingLabel = true;
    },
    enableUpdatingGroup(group) {
      this.updateGroup = true;
      this.indexGroup = group.group_id;
      setTimeout(() => {
        document.getElementById('update_input_' + group.group_id).focus();
      }, 200);
    },
    enableGroupHover(group) {
      this.hoverGroup = true;
      this.indexGroup = group;
    },
    disableGroupHover() {
      if(!this.updateGroup) {
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
      Object.keys(this.openGroup).forEach((group,key) => {
        this.openGroup[group] = false;
      });
      this.draggable = true;
    },
    //

    // Draggable trigger
    SomethingChange: function(evt) {
      let elt_id = evt.item.childNodes[0].id
      this.groups.forEach(group => {
        group.elts.forEach(element => {
          if(element.id == elt_id){
            this.updateElementsOrder(group.group_id,group.elts);
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
  },
  created() {
    this.getDataObject();
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

<style scoped>
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
  .check:checked ~ .track{
    box-shadow: inset 0 0 0 20px #de6339;
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
    left: 3em;
    margin-bottom: 10px;
  }
  .icon-handle-group{
    color: #cecece;
    position: absolute;
    cursor: grab;
    left: 1em;
    margin-bottom: 0;
  }
  .icon-handle-unpublished{
    color: #cecece;
    position: absolute;
    cursor: grab;
    left: 1em;
    margin-bottom: 10px;
  }
  .hidden{
    display: none;
  }
  .active-repeat{
    background: #de6339;
    color: white !important;
  }
  .group-repeat-icon{
    padding: 2px;
    border-radius: 5px;
    color: #1b1f3c;
  }
</style>

