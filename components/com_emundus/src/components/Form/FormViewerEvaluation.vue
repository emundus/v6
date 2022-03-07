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
        <div v-for="(group,index_group) in object_json.Groups"
             v-bind:key="group.index">
          <fieldset :class="group.group_class" :id="'group_'+group.group_id" :style="group.group_css">
            <div class="em-flex-row">
              <legend
                      v-if="group.group_showLegend"
                      class="legend ViewerLegend"
              >{{group.group_showLegend}}</legend>
            </div>
            <div v-if="group.group_intro" class="groupintro" v-html="group.group_intro"></div>

            <div class="elements-block">
                <transition-group :name="'slide-down'" type="transition">
                <div v-for="(element,index) in group.elts"
                     v-bind:key="element.id"
                     v-show="element.hidden === false"
                     class="builder-item-element"
                     :class="{'unpublished': !element.publish}">
                  <div class="em-flex-row builder-item-element__properties">
                    <div class="w-100">
                      <div class="em-flex-row" style="align-items: baseline">
                        <span v-if="element.label_value" v-html="element.label_value" v-show="element.labelsAbove != 2"></span>
                      </div>
                      <div v-if="element.params.date_table_format">
                        <date-picker v-model="date" :config="options"></date-picker>
                      </div>
                      <div v-else-if="element.labelsAbove == 0" class="controls">
                        <div v-if="element.error" class="fabrikElement" v-html="element.error"></div>
                        <div v-if="element.element" :class="element.errorClass" v-html="element.element"></div>
                        <span v-if="element.tipSide" v-html="element.tipSide"></span>
                      </div>
                      <span v-else class="em-flex-row w-100">
                      <div v-if="element.element" class="fabrikElement" v-html="element.error"></div>
                      <div v-if="element.element" :class="element.errorClass" v-html="element.element" class="w-100"></div>
                      <span v-if="element.tipSide" v-html="element.tipSide"></span>
                    </span>
                      <span v-if="element.tipBelow" v-html="element.tipBelow"></span>
                    </div>
                  </div>
                </div>
                </transition-group>
            </div>
            <div class="groupoutro" v-if="group.group_outro" v-html="group_outro"></div>
          </fieldset>
        </div>
      <div v-if="object_json.pluginbottom" v-html="object_json.pluginbottom"></div>
    </form>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>


<script>
  import _ from "lodash";
  import datePicker from "vue-bootstrap-datetimepicker";
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
      modalEditElement
    },
    data() {
      return {
        object_json: "",
        date: new Date(),
        groups: [],
        loading: false,
        options: {
          format: "DD/MM/YYYY",
          useCurrent: false
        },
      };
    },
    methods: {
      getDataObject: _.debounce(function() {
        this.loading = true;
        let ellink = this.link.link.replace("fabrik","emundus");
        axios.get(ellink + "&format=vue_jsonclean")
              .then(response => {
                this.object_json = response.data;
                this.convertGroupElementsToArray();
                this.loading = false;
              }).catch(e => {
                this.loading = false;
                console.log(e);
              });
      }, 150),

      convertGroupElementsToArray(){
        Object.keys(this.object_json.Groups).forEach(group => {
          this.groups.push(this.object_json.Groups[group]);
          this.object_json.Groups[group].elts = [];
          Object.keys(this.object_json.Groups[group].elements).forEach(element => {
            this.object_json.Groups[group].elts.push(this.object_json.Groups[group].elements[element]);
          });
        });
      },

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
