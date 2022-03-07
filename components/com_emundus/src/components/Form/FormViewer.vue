<template>
  <div id="FormViewer" class="FormViewer container-fluid">
    <div
            v-if="object_json.show_page_heading"
            :class="object_json.show_page_heading.class"
            v-html="object_json.show_page_heading.page_heading"
    />
    <div class="em-flex-row em-flex-space-between">
      <h2 v-if="object_json.show_title" class="page_header" v-html="object_json.show_title.value" />
      <a @click="formbuilder" class="cta-block pointer">
        <em class="fas fa-pen"></em>
      </a>
    </div>

    <p v-if="object_json.intro_value" class="introP" style="margin-top: 2em" v-html="object_json.intro_value" />

    <form method="post" object_json.attribs class="form-page">
      <div v-if="object_json.plugintop" v-html="object_json.plugintop"></div>
      <div v-for="group in object_json.Groups" v-bind:key="group.index" :class="{'unpublished': group.visibility && visibility != null}" style="padding: 5px">
        <fieldset :class="group.group_class" :id="'group'+group.group_id" :style="group.group_css">
          <legend v-if="group.group_showLegend" class="legend ViewerLegend">
            {{group.group_showLegend}}
            <button v-if="visibility != null" @click="updateVisibility(group.group_id)" type="button" class="eye-button">
              <em :class="group.visibility ? 'eye-slash-icon' : 'eye-icon'"></em>
            </button>
          </legend>
          <div v-if="group.group_intro" class="groupintro" v-html="group.group_intro"></div>

          <div class="elements-block">
            <div
                    v-for="element in group.elements"
                    v-bind:key="element.index"
                    v-show="element.hidden === false"
                    class="builder-item-element"
            >
              <span v-if="element.label_value" v-html="element.label_value" v-show="element.labelsAbove != 2"></span>
              <div v-if="element.params.date_table_format">
                <date-picker v-model="date" :config="options"></date-picker>
              </div>
              <div v-else-if="element.labelsAbove == 0" class="controls">
                <div v-if="element.error" class="fabrikElement" v-html="element.error"></div>
                <div v-if="element.element" :class="element.errorClass" v-html="element.element"></div>
                <span v-if="element.tipSide" v-html="element.tipSide"></span>
              </div>
              <span v-else>
              <div v-if="element.element" class="fabrikElement" v-html="element.error"></div>
              <div v-if="element.element" :class="element.errorClass" v-html="element.element"></div>
              <span v-if="element.tipSide" v-html="element.tipSide"></span>
            </span>
              <span v-if="element.tipBelow" v-html="element.tipBelow"></span>
            </div>
          </div>
          <div class="groupoutro" v-if="group.group_outro" v-html="group_outro"></div>
        </fieldset>
      </div>
      <div v-if="object_json.pluginbottom" v-html="object_json.pluginbottom"></div>
    </form>
    <div class="em-page-loader" v-if="submitted"></div>
  </div>
</template>


<script>
  import _ from "lodash";
  import datePicker from "vue-bootstrap-datetimepicker";
  import axios from "axios";
  const qs = require("qs");
  export default {
    name: "FormViewer",
    props: {
      link: {
        type: Object,
        default: {
          link: "",
        }
      },
      visibility: Number
    },
    components: {
      datePicker
    },
    data() {
      return {
        object_json: "",
        date: new Date(),
        submitted: false,
        options: {
          format: "DD/MM/YYYY",
          useCurrent: false
        }
      };
    },
    methods: {
      splitProfileIdfromLabel(label){
        return (label.split(/-(.+)/))[1];
      },
      formbuilder() {
        this.$emit("editPage");
      },

      getDataObject: _.debounce(function() {
        this.submitted = true;
        let ellink = this.link.link.replace("fabrik","emundus");
        axios
                .get(ellink + "&format=vue_jsonclean")
                .then(response => {
                  this.object_json = response.data;
                  if(this.visibility != null){
                    axios({
                      method: "get",
                      url:
                              "index.php?option=com_emundus&controller=formbuilder&task=checkconstraintgroup",
                      params: {
                        cid: this.visibility
                      },
                      paramsSerializer: params => {
                        return qs.stringify(params);
                      }
                    }).then(constraint => {
                      Object.keys(this.object_json.Groups).forEach(group => {
                        if(constraint.data.data != null) {
                          axios({
                            method: "get",
                            url:
                                    "index.php?option=com_emundus&controller=formbuilder&task=checkvisibility",
                            params: {
                              group: this.object_json.Groups[group]['group_id'],
                              cid: this.visibility
                            },
                            paramsSerializer: params => {
                              return qs.stringify(params);
                            }
                          }).then(visibility => {
                            if (parseInt(visibility.data.data) > 0) {
                              this.object_json.Groups[group].visibility = true;
                            } else {
                              this.object_json.Groups[group].visibility = false;
                            }
                          });
                        } else {
                          this.object_json.Groups[group].visibility = true;
                        }
                      });
                      this.$forceUpdate();
                    });
                  }
                  this.submitted = false;
                })
                .catch(e => {
                  console.log(e);
                });
      }, 150),

      // Manage evaluators visibility
      updateVisibility(gid) {
        this.object_json.Groups['group_' + gid]['visibility'] = !this.object_json.Groups['group_' + gid]['visibility'];
        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=programme&task=updatevisibility",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            visibility: this.object_json.Groups['group_' + gid]['visibility'],
            gid: gid,
            cid: this.visibility
          })
        }).then(response => {
          this.$forceUpdate();
        })
      },
      //
    },
    created() {
      this.getDataObject();
    },
    watch: {
      link: function() {
        this.getDataObject();
      }
    }
  };
</script>

<style scoped>
  .dropdown-menu {
    width: 0 !important;
  }
  .hidden {
    display: none;
  }
  .FormViewer {
    padding: 0 1%;
    border-radius: 5px;
  }

  .loading-form{
    top: 10vh;
  }

  .eye-button{
    background: transparent;
  }
</style>
