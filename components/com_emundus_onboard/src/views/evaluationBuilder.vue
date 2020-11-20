<template>
  <div class="container-fluid">
    <notifications
            group="foo-velocity"
            animation-type="velocity"
            :speed="500"
            position="bottom left"
            :classes="'vue-notification-custom'"
    />
    <div class="row form-builder">
      <div class="actions-menu menu-block">
        <div>
          <div class="heading-actions">
            <label class="form-title" style="padding: 0; margin: 0">{{Actions}}</label>
          </div>
          <div class="action-links">
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
      </div>
      <div class="col-md-8 col-sm-9 col-md-offset-4 menu-block">
        <div class="heading-block">
          <h1 class="form-title" style="padding: 0; margin: 0">Evaluation</h1>
        </div>
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
                  :eval="1"
                  :files="files"
                  :actualLanguage="actualLanguage"
                  :manyLanguages="manyLanguages"
                  ref="builder"
          />
        </div>
      </div>
    </div>
    <div class="loading-form" v-if="loading">
      <Ring-Loader :color="'#12DB42'" />
    </div>
  </div>
  <tasks></tasks>
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
  import List from "./list";
  import Tasks from "@/views/tasks";

  const qs = require("qs");

  export default {
    name: "EvaluationBuilder",
    props: {
      prid: String,
      index: Number,
      cid: Number,
      eval: Number,
      actualLanguage: String,
      manyLanguages: Number
    },
    components: {
      Tasks,
      List,
      ModalAffectCampaign,
      Builder,
      ModalSide,
      ModalMenu,
      draggable
    },
    data() {
      return {
        // UX variables
        UpdateUx: false,
        indexHighlight: 0,
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
        rgt: 0,
        builderKey: 0,
        files: 0,
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
        addGroup: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ADDGROUP"),
        addItem: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ADDITEM"),
        Actions: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTIONS"),
        sendFormButton: Joomla.JText._("COM_EMUNDUS_ONBOARD_SEND_FORM"),
        Edit: Joomla.JText._("COM_EMUNDUS_ONBOARD_MODIFY"),
        FormPage: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_PAGE"),
        SubmitPage: Joomla.JText._("COM_EMUNDUS_ONBOARD_SUBMIT_PAGE"),
        groupCreated: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_CREATEDGROUPSUCCES"),
        update: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATE"),
      };
    },

    methods: {
      createElement(gid,plugin,order) {
        if(!_.isEmpty(this.formObjectArray[this.indexHighlight].object.Groups)){
          this.loading = true;
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
      addingNewElement: function(evt) {
        this.dragging = false;
        this.draggingIndex = -1;
        let plugin = evt.clone.id.split('_')[1];
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
              url: "index.php?option=com_emundus_onboard&controller=program&task=affectgrouptoprogram",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({
                group: result.data.group_id,
                pid: this.cid
              })
            }).then((result) => {
              this.show("foo-velocity",
                      "success",
                      this.groupCreated,
                      this.update
              );
            });
            this.loading = false;
            this.pushGroup(result.data);
          });
        });
      },

      // Update component dynamically
      UpdateName(index, label) {
        this.formObjectArray[index].object.show_title.value = label;
      },
      UpdateUXT() {
        this.UpdateUx = true;
      },
      pushGroup(group) {
            this.formObjectArray[this.indexHighlight]['object']['Groups']['group_'+group.group_id] = {
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
        this.elementDisabled = false;
        this.$refs.builder.getDataObject();
        this.$refs.builder.$refs.builder_viewer.openGroup[group.group_id] = true;
        setTimeout(() => {
          window.scrollTo(0,document.body.scrollHeight);
        }, 200);
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

      getDataObject() {
        this.link = 'index.php?option=com_fabrik&view=form&formid=' + this.eval;
        let ellink = this.link.replace("fabrik","emundus_onboard");
        axios.get(ellink + "&format=vue_jsonclean")
                .then(response => {
                  this.formObjectArray.push({
                    object: response.data,
                    link: this.link
                  });
                }).then(r => {
          this.loading = false;
          this.elementDisabled = _.isEmpty(this.formObjectArray[this.indexHighlight].object.Groups);
        }).catch(e => {
          console.log(e);
        });
      },

      sendForm() {
        this.redirectJRoute('index.php?option=com_emundus_onboard&view=program&layout=advancedsettings&pid=' + this.cid);
      },

      redirectJRoute(link) {
        axios({
          method: "get",
          url: "index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute",
          params: {
            link: link,
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
          window.location.href = window.location.pathname + response.data.data;
        });
      },

      // Triggers
      showElements() {
        if(this.elementDisabled){
          this.addingElement = false;
        } else {
          this.addingElement = !this.addingElement;
        }
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


  .draggables-list{
    display: flex;
    flex-direction: row;
    align-self: baseline;
  }
  .divider-menu{
    width: 100%;
    margin: 0em;
  }
  .heading-block{
    text-align: center;
    margin-bottom: 1em;
    margin-top: 2em;
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
