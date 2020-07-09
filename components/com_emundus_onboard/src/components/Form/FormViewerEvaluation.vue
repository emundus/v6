<template>
  <div id="FormViewerEvaluation" class="FormViewer container-fluid">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css"
    />
    <notifications
            group="foo-velocity"
            position="top right"
            animation-type="velocity"
            :speed="500"
    />
    <div
            v-if="object_json.show_page_heading"
            :class="object_json.show_page_heading.class"
            v-html="object_json.show_page_heading.page_heading"
    />

    <form method="post" v-on:submit.prevent object_json.attribs>
      <div v-if="object_json.plugintop" v-html="object_json.plugintop"></div>
      <draggable
              handle=".handle"
              v-model="groups"
              @start="draggable = true"
              @end="SomethingChangeInGroup">
        <div v-for="(group,index_group) in object_json.Groups"
             v-bind:key="group.index"
             @mouseover="enableGroupHover(group.group_id)"
             @mouseleave="disableGroupHover()">
          <div class="group-action-menu">
            <div class="second-group-menu">
              <a class="add-element" @click="createGroup()" :title="addGroup">
                <em class="add-group-icon"></em>
              </a>
              <a class="edit-group" :class="{ 'disable-element': elementDisabled}" @click="addingElement = !addingElement" :title="addItem">
                <em class="add-element-icon" :class="[{'disable-element': elementDisabled}, addingElement ? 'down-arrow-evaluation' : '']"></em>
              </a>
                <draggable
                        v-model="plugins"
                        v-bind="dragOptions"
                        v-if="addingElement"
                        handle=".handle"
                        @start="dragging = true;draggingIndex = index"
                        @end="addingNewElement($event)"
                        class="draggable-span"
                        style="padding-bottom: 5px">
                  <div class="d-flex plugin-link handle" v-for="(plugin,index) in plugins" :id="'plugin_' + plugin.value">
                    <em :class="plugin.icon"></em>
                  </div>
                </draggable>
            </div>
          </div>
          <fieldset :class="group.group_class" :id="'group_'+group.group_id" :style="group.group_css">
            <div class="d-flex" :class="updateGroup && indexGroup == group.group_id ? 'hidden' : ''">
              <span v-show="hoverGroup && indexGroup == group.group_id" class="icon-handle-group">
                <em class="fas fa-grip-vertical handle"></em>
              </span>
              <legend
                      v-if="group.group_showLegend"
                      class="legend ViewerLegend"
              >{{group.group_showLegend}}</legend>
              <a @click="enableUpdatingGroup(group)" style="margin-left: 1em">
                <em class="fas fa-pencil-alt" data-toggle="tooltip" data-placement="top" :title="sidemenuhelp"></em>
              </a>
            </div>
            <div style="width: max-content" v-show="updateGroup && indexGroup == group.group_id">
              <div class="input-can-translate">
                <input v-model="group.label_fr" class="form-control mb-1" @keyup.enter="updateLabelGroup(group)" :id="'update_input_' + group.group_id"/>
                <div class="d-flex actions-update-label" style="margin-bottom: 12px">
                  <a @click="deleteAGroup(group,index_group)" style="margin-left: 1em;color: black">
                    <em class="fas fa-trash-alt" data-toggle="tooltip" data-placement="top" :title="sidemenuhelp"></em>
                  </a>
                  <a @click="updateGroup = false">
                    <em class="fas fa-times ml-20px" data-toggle="tooltip" data-placement="top" :title="sidemenuhelp"></em>
                  </a>
                  <a @click="updateLabelGroup(group)">
                    <em class="fas fa-check ml-20px" data-toggle="tooltip" data-placement="top" :title="sidemenuhelp"></em>
                  </a>
                </div>
              </div>
            </div>
            <div v-if="group.group_intro" class="groupintro">{{group.group_intro}}</div>

            <div class="elements-block">
              <draggable
                      handle=".handle"
                      v-model="group.elts"
                      @start="draggable = true"
                      @end="SomethingChange"
                      group="items">
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
                          @reloadElement="reloadElement"
                          :id="element.id"
                  />
                  <div class="d-flex builder-item-element__properties">
                  <span :class="element.publish ? 'icon-handle' : 'icon-handle-unpublished'" v-show="hoverUpdating && indexHighlight == element.id">
                    <em class="fas fa-grip-vertical handle"></em>
                  </span>
                    <div class="w-100">
                      <div class="d-flex" style="align-items: baseline">
                        <span v-if="element.label" :class="clickUpdatingLabel && indexHighlight == element.id ? 'hidden' : ''" v-html="element.label" v-show="element.labelsAbove != 2"></span>
                        <a @click="enableLabelInput" :style="hoverUpdating && indexHighlight == element.id && !clickUpdatingLabel ? 'opacity: 1' : 'opacity: 0'">
                          <em class="fas fa-pencil-alt ml-10px" data-toggle="tooltip" data-placement="top" :title="sidemenuhelp"></em>
                        </a>
                      </div>
                      <div class="input-can-translate" v-show="clickUpdatingLabel && indexHighlight == element.id">
                        <input v-model="element.label_fr" class="form-control mb-1" @keyup.enter="updateLabelElement(element)"/>
                        <div class="d-flex actions-update-label" style="margin-bottom: 12px">
                          <a @click="clickUpdatingLabel = false">
                            <em class="fas fa-times ml-20px" data-toggle="tooltip" data-placement="top" :title="sidemenuhelp"></em>
                          </a>
                          <a @click="updateLabelElement(element)">
                            <em class="fas fa-check ml-20px" data-toggle="tooltip" data-placement="top" :title="sidemenuhelp"></em>
                          </a>
                        </div>
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
                    <a class="d-flex mr-2 text-orange" @click="$modal.show('modalEditElement' + element.id)">
                      <em class="fas fa-cog"></em>
                      <span class="ml-10px">{{Settings}}</span>
                    </a>
                    <a class="d-flex mr-2" style="color: black" @click="deleteCriteria(element,index)">
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
    <div class="loading-form" v-if="loading">
      <Ring-Loader :color="'#de6339'" />
    </div>
  </div>
</template>


<script>
  import _ from "lodash";
  import datePicker from "vue-bootstrap-datetimepicker";
  import draggable from "vuedraggable";
  import axios from "axios";
  import modalEditElement from "../formClean/Modal";
  import Swal from "sweetalert2";
  const qs = require("qs");
  export default {
    name: "FormViewerEvaluation",
    props: {
      link: String,
      prog: Number
    },
    components: {
      datePicker,
      draggable,
      modalEditElement
    },
    data() {
      return {
        object_json: "",
        date: new Date(),
        hoverUpdating: false,
        hoverGroup: false,
        lastIndex: 0,
        indexHighlight: 0,
        indexGroup: -1,
        clickUpdatingLabel: false,
        updateGroup: false,
        draggable: false,
        fieldChanges: false,
        draggingIndex: -1,
        elementDisabled: false,
        addingElement: false,
        groups: [],
        loading: false,
        options: {
          format: "DD/MM/YYYY",
          useCurrent: false
        },
        plugins: {
          field: {
            id: 0,
            value: 'field',
            icon: 'fas fa-font',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_FIELD")
          },
          birthday: {
            id: 1,
            value: 'birthday',
            icon: 'far fa-calendar-alt',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_BIRTHDAY")
          },
          checkbox: {
            id: 2,
            value: 'checkbox',
            icon: 'far fa-check-square',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_CHECKBOX")
          },
          dropdown: {
            id: 3,
            value: 'dropdown',
            icon: 'fas fa-th-list',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_DROPDOWN")
          },
          radiobutton: {
            id: 4,
            value: 'radiobutton',
            icon: 'fas fa-list-ul',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_RADIOBUTTON")
          },
          textarea: {
            id: 5,
            value: 'textarea',
            icon: 'far fa-square',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_TEXTAREA")
          },
        },
        addGroup: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ADDGROUP"),
        addItem: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ADDITEM"),
        Unpublish: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH"),
        Publish: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTION_PUBLISH"),
        Required: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTIONS_REQUIRED"),
        Settings: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTIONS_SETTINGS"),
        Delete: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTION_DELETE"),
        sidemenuhelp: Joomla.JText._("COM_EMUNDUS_ONBOARD_SIDEMENUHELP"),
        update: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATE"),
        updating: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATING"),
        updateSuccess: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATESUCESS"),
        orderSuccess: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ORDERSUCESS"),
        orderFailed: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ORDERFAILED"),
        updateFailed: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATEFAILED"),
      };
    },
    methods: {
      getDataObject: _.debounce(function() {
        this.loading = true;
        let ellink = this.link.link.replace("fabrik","emundus_onboard");
        axios
                .get(ellink + "&format=vue_jsonclean")
                .then(response => {
                  this.object_json = response.data;
                  this.convertGroupElementsToArray();
                  this.loading = false;
                })
                .catch(e => {
                  console.log(e);
                });
      }, 150),

      /**
       * ** Methods for notify
       */
      show(group, type = "", text = "", title = "Information") {
        this.$notify({
          group,
          title: `${title}`,
          text,
          type
        });
      },
      clean(group) {
        this.$notify({ group, clean: true });
      },

      convertGroupElementsToArray(){
        Object.keys(this.object_json.Groups).forEach(group => {
          this.groups.push(this.object_json.Groups[group]);
          this.object_json.Groups[group].elts = [];
          Object.keys(this.object_json.Groups[group].elements).forEach(element => {
            this.object_json.Groups[group].elts.push(this.object_json.Groups[group].elements[element]);
          });
        });
      },

      UpdateGroupName(group, label) {
        this.object_json.Groups[group].group_showLegend = label;
      },

      UpdateLabel(element, label) {
        element.label_raw = label;
      },

      // Group methods
      createGroup() {
        this.loading = true;
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=createsimplegroup",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            fid: this.object_json.id
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
            this.loading = false;
            this.pushGroup(result.data);
          });
        });
      },

      updateLabelGroup(group) {
        let labels = {
          fr: group.label_fr,
          en: group.label_fr
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
          this.show("foo-velocity",
                  "success",
                  this.updateSuccess,
                  this.update
          );
          group.group_showLegend = group.label_fr;
          this.updateGroup = false;
        }).catch(e => {
          this.show(
                  "foo-velocity",
                  "error",
                  this.updateFailed,
                  this.updating
          );
          console.log(e);
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
          this.show(
                  "foo-velocity",
                  "success",
                  this.orderSuccess,
                  this.update
          );
        }).catch(e => {
          this.show(
                  "foo-velocity",
                  "error",
                  this.orderFailed,
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
              axios({
                method: "post",
                url:
                        "index.php?option=com_emundus_onboard&controller=program&task=deletegroupfromprogram",
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded"
                },
                data: qs.stringify({
                  group: group.group_id,
                  pid: this.prog
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
                  this.$forceUpdate();
                });
              });
            }).catch(e => {
              console.log(e);
            });
          }
        });
      },

      pushGroup(group) {
        this.object_json['Groups']['group_'+group.group_id] = {
          elements: {},
          group_id: group.group_id,
          group_showLegend: group.group_showLegend,
          label_fr: group.label_fr,
          label_en: group.label_en,
          group_tag: group.group_tag,
          ordering: group.ordering
        };
        this.$forceUpdate();
        setTimeout(() => {
          window.scrollTo(0,document.body.scrollHeight);
        }, 200);
      },
      //

      // Elements methods
      createElement(gid,plugin,order) {
        this.loading = true;
        //let gid = this.formObjectArray[this.indexHighlight].object.Groups[Object.keys(this.formObjectArray[this.indexHighlight].object.Groups).sort().pop()].group_id;
        axios({
          method: "post",
          url:
                  "index.php?option=com_emundus_onboard&controller=formbuilder&task=createcriteria",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            gid: gid,
            plugin: plugin
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
            this.object_json.Groups['group_'+gid].elements['element'+response.data.id] = response.data;
            this.object_json.Groups['group_'+gid].elts.splice(order,0,response.data);
            this.updateElementsOrder(gid,this.object_json.Groups['group_'+gid].elts);
            this.loading = false;
          });
        });
      },
      addingNewElement: function(evt) {
        this.dragging = false;
        this.draggingIndex = -1;
        let plugin = evt.clone.id.split('_')[1];
        let gid = evt.to.parentElement.parentElement.parentElement.id.split('_')[1];
        if(typeof gid != 'undefined'){
          this.createElement(gid,plugin,evt.newIndex)
        }
      },
      updateLabelElement(element) {
        let labels = {
          fr: element.label_fr,
          en: element.label_fr
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
            this.show(
                    "foo-velocity",
                    "success",
                    this.updateSuccess,
                    this.update
            );
            this.clickUpdatingLabel = false;
          });
        }).catch(e => {
          this.show(
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
          element.element = response.data.element;
          element = response.data;
        }).catch(e => {
          this.show(
                  "foo-velocity",
                  "error",
                  this.updateFailed,
                  this.updating
          );
          console.log(e);
        });
      },

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
          this.show(
                  "foo-velocity",
                  "success",
                  this.orderSuccess,
                  this.update
          );
          let ellink = this.link.link.replace("fabrik","emundus_onboard");
          axios.get(ellink + "&format=vue_jsonclean").then(r => {
            this.groups.forEach(grp => {
              this.object_json.Groups['group_' + grp.group_id].elements = r.data.Groups['group_' + grp.group_id].elements;
            });
          });
        }).catch(e => {
          this.show(
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
              this.show(
                      "foo-velocity",
                      "success",
                      this.updateSuccess,
                      this.update
              );
            });
          }).catch(e => {
            this.show(
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
          this.show(
                  "foo-velocity",
                  "success",
                  this.updateSuccess,
                  this.update
          );
        }).catch(e => {
          this.show(
                  "foo-velocity",
                  "error",
                  this.updateFailed,
                  this.updating
          );
          console.log(e);
        });
      },

      deleteCriteria(element,index){
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
      //

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
      },
      //
    },
    created() {
      this.getDataObject();
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
    },

    watch: {
      link: function() {
        this.getDataObject();
      }
    }
  };
</script>

<style scoped>
  .hidden {
    display: none;
  }
  .FormViewer {
    padding: 0 1%;
    border-radius: 5px;
    margin-top: 5em;
  }

  .loading-form{
    top: 10vh;
  }

  .group-action-menu{
    position: fixed;
    margin-left: -5%;
    border: 1px solid #cecece;
    border-radius: 25px;
    padding: 5px;
    text-align: center;
    font-size: 20px;
    width: min-content;
  }

  .add-element,.edit-group,.delete-group{
    height: 18px;
    width: 18px;
    margin: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
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
    left: 10px;
    margin-bottom: 0;
  }
  .icon-handle-unpublished{
    color: #cecece;
    position: absolute;
    cursor: grab;
    left: 1em;
    margin-bottom: 10px;
  }
  .fa-pencil-alt{
    margin-top: 0.5em;
    color: #de6339;
    cursor: pointer;
  }
  .plugin-link{
    padding: 10px;
  }
</style>
