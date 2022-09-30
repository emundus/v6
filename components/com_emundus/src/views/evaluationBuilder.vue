<template>
  <div id="evaluation-builder" class="container-fluid">
    <notifications
            group="foo-velocity"
            animation-type="velocity"
            :speed="500"
            position="bottom left"
            :classes="'vue-notification-custom'"
    />
    <div class="row">
      <div class="sidebar-formbuilder" :style="actions_menu ? 'width: 250px' : ''">
        <transition name="move-right">
          <div class="actions-menu menu-block">
            <a class="d-flex back-button-action pointer" style="padding: 0 15px" :title="translations.Back">
              {{ translations.BuildYourForm }}
            </a>
            <hr style="width: 80%;margin: 10px auto;">
            <div>
              <div class="action-links">
                <a class="d-flex action-link" style="padding-top: 2em" @click="$modal.show('modalMenu')" :title="translations.addMenu">
                  <em class="add-page-icon"></em>
                  <label class="action-label col-md-offset-1 col-sm-offset-1" v-show="actions_menu">{{translations.addMenu}}</label>
                </a>
                <a class="d-flex action-link" @click="createGroup()" :title="translations.addGroup">
                  <em class="add-group-icon"></em>
                  <label class="action-label col-md-offset-1 col-sm-offset-1" v-show="actions_menu">{{translations.addGroup}}</label>
                </a>
                <a class="d-flex action-link" :class="{ 'disable-element': elementDisabled}" @click="showElements" :title="translations.addItem">
                  <em class="add-element-icon"></em>
                  <label class="action-label col-md-offset-1 col-sm-offset-1" v-show="actions_menu" :class="[{'disable-element': elementDisabled}, addingElement ? 'down-arrow' : 'right-arrow']">{{translations.addItem}}</label>
                </a>
                <transition :name="'slide-right'" type="transition">
                  <div class="plugins-list" v-if="addingElement">
                    <a class="d-flex col-md-offset-1 back-button-action pointer" style="padding: 0 15px" @click="addingElement = !addingElement" :title="translations.Back">
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
                      <div class="d-flex plugin-link col-md-offset-1 col-sm-offset-1 handle" v-for="(plugin,index) in plugins" :id="'plugin_' + plugin.value" @dblclick="addingNewElementByDblClick(plugin.value)" :title="plugin.name">
                        <em :class="plugin.icon"></em>
                        <span class="ml-10px">{{plugin.name}}</span>
                      </div>
                    </draggable>
                  </div>
                </transition>
              </div>
            </div>
          </div>
        </transition>
      </div>
      <div :class="actions_menu ? 'col-md-8 col-md-offset-4 col-sm-9 col-sm-offset-3' : ''" class="menu-block">
        <div class="heading-block" :class="addingElement || actions_menu ? 'col-md-offset-2 col-md-9' : 'col-md-12'">
          <h2 class="form-title" style="padding: 0; margin: 0">
            <img src="/images/emundus/menus/form.png" alt="Formulaire" class="em-mr-4">Evaluation</h2>
          <div class="d-flex">
            <button class="bouton-sauvergarder-et-continuer bouton-sauvergarder-et-continuer-green mt-1" @click="sendForm" style="margin-left: 10px" :title="translations.Validate">{{translations.Validate}}</button>
            <button class="bouton-sauvergarder-et-continuer mt-1" @click="sendForm" style="margin-left: 10px" :title="translations.ExitFormbuilder">{{translations.ExitFormbuilder}}</button>
          </div>
        </div>
        <div class="form-viewer-builder" :class="[addingElement || actions_menu ? 'col-sm-offset-5 col-md-offset-4 col-lg-offset-1 col-sm-7' : 'col-md-10',optionsModal ? 'col-sm-5 col-md-6' : 'col-md-10']">
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
                  :eval="1"
                  :files="files"
                  :actualLanguage="actualLanguage"
                  :manyLanguages="manyLanguages"
                  ref="builder"
          />
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

import _ from 'lodash';
import ModalAffectCampaign from "../components/formClean/ModalAffectCampaign";
import List from "./list";
import {global} from "../store/global";

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
      actions_menu: true,
      optionsModal: false,
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
      first_loading: false,
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
          name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_FIELD")
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
      },
      translations: {
        addMenu: "COM_EMUNDUS_ONBOARD_BUILDER_ADDMENU",
        addGroup: "COM_EMUNDUS_ONBOARD_BUILDER_ADDGROUP",
        addItem: "COM_EMUNDUS_ONBOARD_BUILDER_ADDITEM",
        groupCreated: "COM_EMUNDUS_ONBOARD_BUILDER_CREATEDGROUPSUCCES",
        update: "COM_EMUNDUS_ONBOARD_BUILDER_UPDATE",
        Back: "COM_EMUNDUS_ONBOARD_ADD_RETOUR",
        Validate: "COM_EMUNDUS_ONBOARD_OK",
        ExitFormbuilder: "COM_EMUNDUS_ONBOARD_BUILDER_EXIT_FORMBUILDER",
        BuildYourForm: "COM_EMUNDUS_ONBOARD_BUILDER_BUILD_YOUR_FORM",
      }
    };
  },
  created() {
    this.$props.actualLanguage = this.$store.getters['global/shortLang'];
    this.$props.manyLanguages = this.$store.getters['global/manyLanguages'];
    this.$props.index = this.$store.getters['global/datas'].index.value;
    this.$props.prid = this.$store.getters['global/datas'].prid.value;
    this.$props.cid = this.$store.getters['global/datas'].cid.value;
    this.$props.eval = this.$store.getters['global/datas'].eval.value;
    this.link = 'index.php?option=com_fabrik&view=form&formid=' + this.eval;
    this.getDataObject();
  },
  methods: {
    createElement(gid,plugin,order) {
      if(!_.isEmpty(this.formObjectArray[this.indexHighlight].object.Groups)){
        this.loading = true;
        axios({
          method: "post",
          url:
                    "index.php?option=com_emundus&controller=formbuilder&task=createcriteria",
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
            url: "index.php?option=com_emundus&controller=formbuilder&task=getElement",
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
      if(typeof document.getElementsByClassName('no-elements-tip')[0] != 'undefined') {
        document.getElementsByClassName('no-elements-tip')[0].style.background = '#e4e4e9';
        document.getElementsByClassName('no-elements-tip')[0].style.border = '2px dashed #c3c3ce';
        document.getElementsByClassName('no-elements-tip')[0].innerHTML = this.translate("COM_EMUNDUS_ONBOARD_NO_ELEMENTS_TIPS");
      }
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
        url: "index.php?option=com_emundus&controller=formbuilder&task=createsimplegroup",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          fid: this.formObjectArray[this.indexHighlight].object.id
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
            url: "index.php?option=com_emundus&controller=programme&task=affectgrouptoprogram",
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
              this.translations.groupCreated,
              this.translations.update
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
      let ellink = this.link.replace("fabrik","emundus");
      await axios.get(ellink + "&format=vue_jsonclean")
        .then(response => {
          this.formObjectArray.push({
            object: response.data,
            link: this.link
          });
          this.loading = false;
          this.indexHighlight = 0;
          this.elementDisabled = _.isEmpty(this.formObjectArray[this.indexHighlight].object.Groups);
        });
    },

    sendForm() {
      this.redirectJRoute('index.php?option=com_emundus&view=form');
    },

    redirectJRoute(link) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=settings&task=redirectjroute",
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
    startDragging(){
      if(typeof document.getElementsByClassName('no-elements-tip')[0] != 'undefined'){
        document.getElementsByClassName('no-elements-tip')[0].style.background = '#fff';
        document.getElementsByClassName('no-elements-tip')[0].style.border = '2px dashed #16afe1';
        document.getElementsByClassName('no-elements-tip')[0].innerHTML = '';
      }
    }
    //
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
  .heading-block{
    order: 1;
  }
  .form-builder{
    order: 3;
    margin-left: 25px;
  }
}
</style>
