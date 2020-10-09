<template>
  <div class="container-fluid">
    <notifications
            group="foo-velocity"
            animation-type="velocity"
            :speed="500"
            position="bottom left"
            :classes="'vue-notification-custom'"
    />
    <ModalAffectCampaign
            :prid="prid"
            :testing="testing"
    />
    <ModalTestingForm
        v-if="formObjectArray[indexHighlight]"
        :profileId="prid"
        :actualLanguage="actualLanguage"
        :campaigns="campaignsAffected"
        :currentForm="formObjectArray[indexHighlight].object.id"
        :currentMenu="formObjectArray[indexHighlight].object.menu_id"
        @testForm="testForm"
    />
    <ModalMenu
            :profileId="prid"
            :actualLanguage="actualLanguage"
            :manyLanguages="manyLanguages"
            @AddMenu="pushMenu"
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
            @show="show"
            @UpdateUx="UpdateUXT"
            @UpdateName="UpdateName"
            @UpdateIntro="UpdateIntro"
            @UpdateVue="updateFormObjectAndComponent"
            @removeMenu="removeMenu"
    />
    <div class="row form-builder">
      <div class="actions-menu menu-block">
        <div class="heading-block">
          <h1 class="form-title" style="padding: 0; margin: 0">{{profileLabel}}</h1>
          <a :href="'index.php?option=com_emundus_onboard&view=form&layout=add&pid=' + this.prid" style="margin-left: 1em" :title="Edit">
            <em class="fas fa-pencil-alt" data-toggle="tooltip" data-placement="top"></em>
          </a>
        </div>
        <div>
          <div class="heading-actions">
            <label class="form-title" style="padding: 0; margin: 0">{{Actions}}</label>
          </div>
          <div class="action-links">
              <a class="d-flex action-link" style="padding-top: 2em" @click="$modal.show('modalMenu')">
                <em class="add-page-icon col-md-offset-1 col-sm-offset-1"></em>
                <label class="action-label col-md-offset-2 col-sm-offset-1">{{addMenu}}</label>
              </a>
              <a class="d-flex action-link" @click="createGroup()">
                <em class="add-group-icon col-md-offset-1 col-sm-offset-1"></em>
                <label class="action-label col-md-offset-2 col-sm-offset-1">{{addGroup}}</label>
              </a>
              <a class="d-flex action-link" :class="{ 'disable-element': elementDisabled}" @click="showElements">
                <em class="add-element-icon col-md-offset-1 col-sm-offset-1"></em>
                <label class="action-label col-md-offset-2 col-sm-offset-1" :class="[{'disable-element': elementDisabled}, addingElement ? 'down-arrow' : 'right-arrow']">{{addItem}}</label>
              </a>
            <transition :name="'slide-down'" type="transition">
              <draggable
                      v-model="plugins"
                      v-bind="dragOptions"
                      v-if="addingElement"
                      handle=".handle"
                      @start="dragging = true;draggingIndex = index"
                      @end="addingNewElement($event)"
                      drag-class="plugin-drag"
                      chosen-class="plugin-chosen"
                      ghost-class="plugin-ghost"
                      style="padding-bottom: 2em">
                  <div class="d-flex plugin-link col-md-offset-3 col-sm-offset-2 handle" v-for="(plugin,index) in plugins" :id="'plugin_' + plugin.value" @dblclick="addingNewElementByDblClick(plugin.value)" :title="plugin.name">
                    <em :class="plugin.icon"></em>
                    <span class="ml-10px">{{plugin.name}}</span>
                  </div>
              </draggable>
            </transition>
          </div>
        </div>
        <a class="send-form-button" @click="sendForm">
          <label style="cursor: pointer">{{sendFormButton}}</label>
          <em class="fas fa-paper-plane" style="font-size: 20px"></em>
        </a>
        <a class="send-form-button test-form-button" style="margin-top: 1em" @click="testForm">
          <label style="cursor: pointer">{{testingForm}}</label>
          <em class="fas fa-vial" style="font-size: 20px"></em>
        </a>
      </div>
      <div class="col-md-8 col-sm-9 col-md-offset-4 col-sm-offset-4 menu-block">
        <ul class="form-section">
          <li>
            <a :class="menuHighlight === 0 ? 'form-section__current' : ''" @click="menuHighlight = 0;indexHighlight = 0">{{FormPage}}</a>
          </li>
          <li>
            <a :class="menuHighlight === 1 ? 'form-section__current' : ''" @click="menuHighlight = 1;indexHighlight = 0">{{SubmitPage}}</a>
          </li>
        </ul>
        <div v-if="menuHighlight === 0">
          <ul class="menus-row">
            <draggable
                    handle=".handle"
                    v-model="formObjectArray"
                    :class="'draggables-list'"
                    @end="SomethingChange"
            >
              <li v-for="(value, index) in formObjectArray" :key="index" class="MenuForm" @mouseover="enableGrab(index)" @mouseleave="disableGrab()">
                <span class="icon-handle" :style="grab && indexGrab == index ? 'opacity: 1' : 'opacity: 0'">
                  <em class="fas fa-grip-vertical handle"></em>
                </span>
                <a @click="changeGroup(index,value.rgt)"
                   class="MenuFormItem"
                   :class="indexHighlight == index ? 'MenuFormItem_current' : ''">
                  {{value.object.show_title.value}}
                </a>
              </li>
            </draggable>
          </ul>
          <div class="col-md-12 form-viewer-builder">
            <Builder
                    :object="formObjectArray[indexHighlight]"
                    v-if="formObjectArray[indexHighlight]"
                    :UpdateUx="UpdateUx"
                    @show="show"
                    @UpdateFormBuilder="updateFormObjectAndComponent"
                    @removeGroup="removeGroup"
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
          <div class="col-md-12 form-viewer-builder">
            <Builder
                    :object="submittionPages[indexHighlight]"
                    v-if="submittionPages[indexHighlight]"
                    :UpdateUx="UpdateUx"
                    @show="show"
                    @UpdateFormBuilder="updateFormObjectAndComponent"
                    @removeGroup="removeGroup"
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
    </div>
    <div class="loading-form" v-if="loading">
      <Ring-Loader :color="'#de6339'" />
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
  import ModalElement from "../components/formClean/ModalElement";
  import ModalAffectCampaign from "../components/formClean/ModalAffectCampaign";
  import List from "./list";
  import ModalTestingForm from "@/components/formClean/ModalTestingForm";

  const qs = require("qs");

  export default {
    name: "FormBuilder",
    props: {
      prid: String,
      index: Number,
      cid: Number,
      actualLanguage: String,
      manyLanguages: Number
    },
    components: {
      ModalTestingForm,
      List,
      ModalAffectCampaign,
      ModalElement,
      Builder,
      ModalSide,
      ModalMenu,
      draggable
    },
    data() {
      return {
        // UX variables
        UpdateUx: false,
        menuHighlight: 0,
        indexHighlight: "0",
        indexGrab: "0",
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
        //

        // Forms variables
        formObjectArray: [],
        submittionPages: [],
        formList: "",
        profileLabel: "",
        id: 0,
        grab: 0,
        rgt: 0,
        builderKey: 0,
        builderSubmitKey: 0,
        files: 0,
        //

        // Testing
        campaignsAffected: {},
        testing: false,
        //

        link: '',

        // Draggabbles variables
        dragging: false,
        draggingIndex: -1,
        elementDisabled: false,
        addingElement: false,
        plugins: {
          field: {
            id: 0,
            value: 'field',
            icon: 'fas fa-font',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_FIELD")
          },
          textarea: {
            id: 5,
            value: 'textarea',
            icon: 'far fa-square',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_TEXTAREA")
          },
          checkbox: {
            id: 2,
            value: 'checkbox',
            icon: 'far fa-check-square',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_CHECKBOX")
          },
          radiobutton: {
            id: 4,
            value: 'radiobutton',
            icon: 'fas fa-list-ul',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_RADIOBUTTON")
          },
          dropdown: {
            id: 3,
            value: 'dropdown',
            icon: 'fas fa-th-list',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_DROPDOWN")
          },
          birthday: {
            id: 1,
            value: 'birthday',
            icon: 'far fa-calendar-alt',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_BIRTHDAY")
          },
          display: {
            id: 6,
            value: 'display',
            icon: 'fas fa-paragraph',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_DISPLAY")
          },
        },
        addMenu: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ADDMENU"),
        addGroup: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ADDGROUP"),
        addItem: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ADDITEM"),
        Actions: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTIONS"),
        sendFormButton: Joomla.JText._("COM_EMUNDUS_ONBOARD_SEND_FORM"),
        Edit: Joomla.JText._("COM_EMUNDUS_ONBOARD_MODIFY"),
        FormPage: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_PAGE"),
        SubmitPage: Joomla.JText._("COM_EMUNDUS_ONBOARD_SUBMIT_PAGE"),
        testingForm: Joomla.JText._("COM_EMUNDUS_ONBOARD_TESTING_FORM"),
      };
    },

    methods: {
      createElement(gid,plugin,order) {
        if(!_.isEmpty(this.formObjectArray[this.indexHighlight].object.Groups)){
          this.loading = true;
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
              this.$set(this.formObjectArray[this.indexHighlight].object.Groups['group_'+gid], 'elements[element' + response.data.id + ']', response.data)
              this.formObjectArray[this.indexHighlight].object.Groups['group_'+gid].elts.splice(order,0,response.data);
              this.$refs.builder.updateOrder(gid,this.formObjectArray[this.indexHighlight].object.Groups['group_'+gid].elts);
              this.$refs.builder.$refs.builder_viewer.keyElements['element' + response.data.id] = 0;
              this.$refs.builder.$refs.builder_viewer.enableActionBar(response.data.id);
              this.$refs.builder.$refs.builder_viewer.enableLabelInput(response.data.id);
              this.loading = false;
            });
          });
        }
      },
      createSubmittionElement(gid,plugin,order){
        if(!_.isEmpty(this.submittionPages[this.indexHighlight].object.Groups)){
          this.loading = true;
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
              this.$set(this.submittionPages[this.indexHighlight].object.Groups['group_'+gid], 'elements[element' + response.data.id + ']', response.data)
              this.submittionPages[this.indexHighlight].object.Groups['group_'+gid].elts.splice(order,0,response.data);
              this.$refs.builder_submit.updateOrder(gid,this.submittionPages[this.indexHighlight].object.Groups['group_'+gid].elts);
              this.$refs.builder_submit.$refs.builder_viewer.keyElements['element' + response.data.id] = 0;
              this.loading = false;
            });
          });
        }
      },
      addingNewElement: function(evt) {
        this.dragging = false;
        this.draggingIndex = -1;
        let plugin = evt.clone.id.split('_')[1];
        let gid = evt.to.parentElement.parentElement.parentElement.id.split('_')[1];
        if(typeof gid != 'undefined'){
          if(this.menuHighlight === 0) {
            this.createElement(gid, plugin, evt.newIndex);
          } else {
            this.createSubmittionElement(gid, plugin, evt.newIndex);
          }
        }
      },
      addingNewElementByDblClick: _.debounce(function(plugin) {
        let gid = Object.keys(this.formObjectArray[this.indexHighlight].object.Groups)[Object.keys(this.formObjectArray[this.indexHighlight].object.Groups).length-1].split('_')[1];
        let index = this.formObjectArray[this.indexHighlight].object.Groups['group_' + gid].elts.length;
        if(typeof gid != 'undefined'){
          this.createElement(gid,plugin,index)
        }
      }, 250, { 'maxWait': 1000 }),
      createGroup() {
        this.loading = true;
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=createsimplegroup",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            fid: this.formObjectArray[this.indexHighlight].object.id
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
              this.loading = false;
              this.pushGroup(result.data);
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
      pushGroup(group) {
        this.formObjectArray.forEach((form, index) => {
          if(form.object.id == group.formid){
            this.formObjectArray[index]['object']['Groups']['group_'+group.group_id] = {
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
        this.elementDisabled = false;
        this.$refs.builder.getDataObject();
        this.$refs.builder.$refs.builder_viewer.openGroup[group.group_id] = true;
        setTimeout(() => {
          window.scrollTo(0,document.body.scrollHeight);
        }, 200);
      },
      pushMenu(menu){
        let menulist = {
          link: menu.link,
          rgt: menu.rgt
        }
        this.formList.push(menulist);
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
          if(form.object.id == form_id){
            this.formObjectArray.splice(index,1);
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
        this.getDataObject();
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
                Joomla.JText._("COM_EMUNDUS_ONBOARD_UPDATEFORMTIP") + '<br/>' + Joomla.JText._("COM_EMUNDUS_ONBOARD_UPDATEFORMTIP1") + '<br/>' + Joomla.JText._("COM_EMUNDUS_ONBOARD_UPDATEFORMTIP2"),
                Joomla.JText._("COM_EMUNDUS_ONBOARD_TIP"),
        );
      },

      show(group, type = "", text = "", title = "Information") {
        this.$notify({
          group,
          title: `${title}`,
          text,
          type
        });
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
              }).then(r => {
                this.formObjectArray.sort((a, b) => a.rgt - b.rgt);
              }).catch(e => {
                console.log(e);
              });
        });
        this.loading = false;
        if(this.getCookie('page') !== '') {
          this.indexHighlight = this.getCookie('page');
        } else {
          this.indexHighlight = 0;
        }
        this.elementDisabled = _.isEmpty(this.formObjectArray[this.indexHighlight].object.Groups);
        this.rgt = this.formObjectArray[this.indexHighlight].rgt;
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
            this.getDataObject();
            this.getProfileLabel(this.prid);
          },100);
        }).catch(e => {
          console.log(e);
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

      sendForm() {
        if(this.cid != 0){
          axios({
            method: "get",
            url: "index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute",
            params: {
              link: 'index.php?option=com_emundus_onboard&view=form&layout=addnextcampaign&cid=' + this.cid + '&index=1',
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
          //history.go(-1);
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
              }).then(response => {
                this.$modal.show('modalAffectCampaign');
              });
            }
        });
      },

      // Triggers
      changeGroup(index,rgt){
        this.indexHighlight = index;
        this.rgt = rgt;
        this.elementDisabled = _.isEmpty(this.formObjectArray[this.indexHighlight].object.Groups);
        document.cookie = 'page='+index+'; expires=Session; path=/'
      },
      SomethingChange: function(e) {
        this.dragging = true;
        let rgts = [];
        this.formList.forEach((menu, index) => {
          rgts.push(menu.rgt);
        });
        this.formObjectArray.forEach((item, index) => {
          item.rgt = rgts[index];
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
      //

      // Draggable pages
      reorderItems(){
        this.formObjectArray.forEach(item => {
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
      //
    },
    created() {
      this.getForms();
      this.getSubmittionPage();
      this.getFilesByForm();
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

<style scoped lang="scss">
  .fa-li {
    left: -0.45em;
  }

  .full-width {
    width: 100vw;
    position: relative;
    margin-left: -50vw !important;
    left: 50%;
    margin-top: -4.2%;
  }
  .container {
    margin-bottom: 5%;
  }
  h1 {
    margin: 20px;
    line-height: 20px;
    font-family: "Open Sans", sans-serif;
    box-sizing: border-box;
  }
  .sidebar {
    padding-top: 20px;
    background-color: #f0f0f0;
    height: 100%;
    width: 16.9%;
  }
  body {
    background-color: #fafafa;
  }
  .Topbar {
    text-align: center;
    font-family: "Open Sans", sans-serif;
    padding: 25px 0;
    background-color: #f0f0f0;
    height: 150px;
  }
  .separator {
    border-right: 1px solid hsla(0, 0%, 81%, 0.5);
  }

  .btnreturn {
    position: relative;
    left: 37%;
    top: 5%;
    background-color: #1b1f3c;
    border-radius: 28px;
    border: 1px solid #1b1f3c;
    display: inline-block;
    cursor: pointer;
    color: #ffffff;
    font-family: Arial;
    font-size: 17px;
    padding: 12px 27px;
    text-decoration: none;
  }
  .btnreturn:hover {
    background-color: #ef6d3b;
    border: 1px solid #ef6d3b;
  }

  .form-builder{
    margin-top: 6em;
    padding: 1em;
    min-height: 50em;
  }

  .form-title{
    text-align: center;
    padding: 1em;
  }

  @media (max-width: 768px) {
    .form-title{
      max-width: 250px;
    }
    .form-builder{
      margin-top: 0;
    }
  }
  .select-form{
    display: flex;
  }
  .select-form select{
    width: 75%;
    margin-left: 1em;
  }

  .add-menu{
    display: flex;
    justify-content: center;
    align-items: center;
    border: unset;
    cursor: pointer;
    align-self: baseline;
  }

  .add-menu:hover > .btnPM {
    background-color: #1b1f3c;
    color: white;
  }

  .dropdown-toggle{
    height: auto;
    background: white;
  }
  .divider-menu{
    width: 100%;
    margin: 0em;
  }
  .heading-block{
    text-align: center;
    margin-bottom: 1em;
    margin-top: 4em;
    width: 75%;
  }
  .edit-icon{
    align-items: center;
    display: flex;
    justify-content: center;
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
  .heading-actions{
    background: #1b1f3c;
    height: 60px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 18px;
    color: #fff;
  }
  .action-link{
    padding: 1em 10px 10px 5px;
    cursor: pointer;
  }
  .action-link:hover > .action-label{
    color: #de6339;
  }
  .action-links{
    background: #fafafa;
  }
  .form-viewer-builder{
    background: #fafafa;
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
</style>
