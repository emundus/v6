<template>
  <!-- modalC -->
  <span :id="'modalEditElement'">
    <modal
            :name="'modalEditElement' + ID"
            height="auto"
            transition="nice-modal-fade"
            :min-width="200"
            :min-height="200"
            :delay="100"
            :adaptive="true"
            :clickToClose="false"
            @closed="beforeClose"
            @before-open="beforeOpen"
    >
      <div class="modalC-content">
        <div class="update-field-header">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalEditElement' + ID)">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
            {{label.fr}}
          </h2>
        </div>
        <div class="form-group mb-2">
          <label>{{fieldType}} :</label>
          <select id="select_type" class="dropdown-toggle" v-model="plugin" @change="checkPlugin" :disabled="(files != 0 && element.plugin == 'birthday') || (files != 0 && element.params.password == 6)">
            <option v-for="(plugin, index) in plugins" :key="index" :value="plugin.value">
              {{plugin.name}}
            </option>
          </select>
        </div>
        <div class="col-md-12 separator-top top-responsive">
          <fieldF v-if="plugin == 'field'" :files="files" :element="element"></fieldF>
          <birthdayF v-if="plugin =='birthday'" :element="element"></birthdayF>
          <checkboxF v-if="plugin =='checkbox'" :element="element" :databases="databases"  @subOptions="subOptions"></checkboxF>
          <dropdownF v-if="plugin =='dropdown'" :element="element" :databases="databases" @subOptions="subOptions"></dropdownF>
          <radiobtnF v-if="plugin == 'radiobutton'" :element="element" @subOptions="subOptions"></radiobtnF>
          <textareaF v-if="plugin =='textarea'" :element="element"></textareaF>
          <displayF v-if="plugin =='display'" :element="element"></displayF>
        </div>
        <div class="loading-form" v-if="loading">
          <Ring-Loader :color="'#de6339'" />
        </div>
      </div>
      <div class="col-md-12 mb-1">
        <a
                class="bouton-sauvergarder-et-continuer-3"
                @click.prevent="UpdateParams"
        >{{ Continuer }}</a>
        <a
                class="bouton-sauvergarder-et-continuer-3 w-retour"
                @click.prevent="$modal.hide('modalEditElement' + ID)"
        >{{Retour}}</a>
      </div>
    </modal>
  </span>
</template>

<script>
  import axios from "axios";
  import fieldF from "./Plugin/field";
  import birthdayF from "./Plugin/birthday";
  import checkboxF from "./Plugin/checkbox";
  import dropdownF from "./Plugin/dropdown";
  import radiobtnF from "./Plugin/radiobtn";
  import textareaF from "./Plugin/textarea";
  import displayF from "./Plugin/display";
  const qs = require("qs");

  export default {
    name: "modalEditElement",
    props: { ID: Number, element: Object, files: Number },
    components: {
      fieldF,
      birthdayF,
      checkboxF,
      dropdownF,
      radiobtnF,
      textareaF,
      displayF
    },
    data() {
      return {
        label: {
          fr: '',
          en: ''
        },
        done: false,
        changes: false,
        loading: false,
        sublabel: "",
        plugin: '',
        translate: {
          label: false,
        },
        // Plugins
        plugins: {
          field: {
            value: 'field',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_FIELD")
          },
          birthday: {
            value: 'birthday',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_BIRTHDAY")
          },
          checkbox: {
            value: 'checkbox',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_CHECKBOX")
          },
          dropdown: {
            value: 'dropdown',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_DROPDOWN")
          },
          radiobutton: {
            value: 'radiobutton',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_RADIOBUTTON")
          },
          textarea: {
            value: 'textarea',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_TEXTAREA")
          },
          display: {
            value: 'display',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_DISPLAY")
          },
        },
        databases: [],
        // Translations
        Name: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIELD_NAME"),
        Require: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIELD_REQUIRED"),
        Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
        Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
        dataSaved: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DATASAVED"),
        informations: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_INFORMATIONS"),
        fieldType: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIELD_TYPE"),
        Delete: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTION_DELETE"),
        LabelRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME"),
        TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
        //
      };
    },
    methods: {
      subOptions(sO) {
        this.sublabel = sO;
      },
      UpdateParams() {
        this.changes = true;
        if(typeof this.element.params.sub_options !== 'undefined') {
          this.element.params.sub_options.sub_values = this.sublabel;
        }
        axios({
          method: "post",
          url:
                  "index.php?option=com_emundus_onboard&controller=formbuilder&task=updateparams",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            element: this.element,
          })
        }).then((response) => {
          setTimeout(() => {
            this.$emit("reloadElement")
          },200);
          this.$modal.hide('modalEditElement' + this.ID);
        }).catch(e => {
          console.log(e);
        });
      },
      axiostrad: function(totrad) {
        return axios({
          method: "post",
          url:
                  "index.php?option=com_emundus_onboard&controller=formbuilder&task=getalltranslations",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            toJTEXT: totrad
          })
        });
      },
      getElement() {
        axios({
          method: "get",
          url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getElement",
          params: {
            element: this.element.id,
            gid: this.element.group_id
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
          this.element = response.data;
          if(this.element.plugin == 'databasejoin'){
            this.plugin = this.element.params.database_join_display_type;
          } else if (this.element.plugin == 'date') {
            this.plugin = 'birthday';
          } else {
            this.plugin = this.element.plugin;
          }
        });
      },
      getDatabases(){
        axios({
          method: "get",
          url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getdatabasesjoin",
        }).then(response => {
          this.databases = response.data.data;
        });
      },
      beforeClose(event) {
        if (this.changes === true) {
          this.changes = false;
          this.$emit(
                  "show",
                  "foo-velocity",
                  "warn",
                  this.dataSaved,
                  this.informations
          );
        }
      },
      beforeOpen(event) {
        this.initialisation();
      },
      initialisation() {
        this.loading = true;
        this.getElement();
        this.axiostrad(this.element.label_tag)
                .then(response => {
                  this.label.fr = response.data.fr;
                  this.label.en = response.data.en;
                })
                .catch(function(response) {
                  console.log(response);
                });
        this.getDatabases();
        this.loading = false;
      },
      checkPlugin(){
        if(this.element.plugin === 'databasejoin'){
          this.plugin = this.element.params.database_join_display_type;
        }
      }
    },
    computed: {
      getlabel: function() {
        return this.tempEl.label_raw;
      }
    },
    watch: {
      element: function() {
        this.tempEl = JSON.parse(JSON.stringify(this.element));
      },
      plugin: function(value) {
        if (this.element.plugin !== 'databasejoin') {
          this.element.plugin = value;
        }
      }
    },
    created: function() {
      if(this.files != 0 && this.element.plugin != 'birthday'){
        delete this.plugins.birthday;
      }
    }
  };
</script>

<style scoped>
  .modalC-content {
    height: 100%;
    box-sizing: border-box;
    padding: 10px;
    font-size: 15px;
    overflow: auto;
  }

  .b {
    display: block;
  }

  .toggle {
    vertical-align: middle;
    position: relative;

    left: 20px;
    width: 45px;
    border-radius: 100px;
    background-color: #ddd;
    overflow: hidden;
    box-shadow: inset 0 0 2px 1px rgba(0, 0, 0, 0.05);
  }

  .check {
    position: absolute;
    display: block;
    cursor: pointer;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    z-index: 6;
  }

  .check:checked ~ .track {
    box-shadow: inset 0 0 0 20px #4bd863;
  }

  .check:checked ~ .switch {
    right: 2px;
    left: 22px;
    transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
    transition-property: left, right;
    transition-delay: 0.05s, 0s;
  }

  .switch {
    position: absolute;
    left: 2px;
    top: 2px;
    bottom: 2px;
    right: 22px;
    background-color: #fff;
    border-radius: 36px;
    z-index: 1;
    transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
    transition-property: left, right;
    transition-delay: 0s, 0.05s;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
  }

  .track {
    position: absolute;
    left: 0;
    top: 0;
    right: 0;
    bottom: 0;
    transition: 0.35s cubic-bezier(0.785, 0.135, 0.15, 0.86);
    box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.05);
    border-radius: 40px;
  }
  .inlineflex {
    display: flex;
    align-content: center;
    align-items: center;
    height: 30px;
  }
  .titleType {
    font-size: 45%;
    margin-left: 1em;
  }
  .topright {
    font-size: 25px;
    float: right;
  }
  .btnCloseModal {
    background-color: inherit;
  }
  .update-field-header{
    margin-bottom: 1em;
  }

  .update-title-header{
    margin-top: 0;
    display: flex;
    align-items: center;
  }

  label{
    color: #000 !important;
  }

  @media (max-width: 991px) {
    .top-responsive {
      margin-top: 5em;
    }
  }
</style>
