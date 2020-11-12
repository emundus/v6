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
            {{label[actualLanguage]}}
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
          <videoM v-if="plugin =='video'" :element="element"></videoM>
          <imageM v-if="plugin =='image'" :element="element"></imageM>
        </div>
      </div>
      <div class="col-md-12 mb-1">
        <button type="button"
                class="bouton-sauvergarder-et-continuer"
                @click.prevent="UpdateParams"
        >{{ Continuer }}</button>
        <button type="button"
                class="bouton-sauvergarder-et-continuer w-retour"
                @click.prevent="$modal.hide('modalEditElement' + ID)"
        >{{Retour}}</button>
      </div>
      <div class="loading-form" v-if="loading">
        <Ring-Loader :color="'#de6339'" />
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
  import videoM from "./Plugin/video";
  import imageM from "./Plugin/image";
  const qs = require("qs");

  export default {
    name: "modalEditElement",
    props: { ID: Number, gid: Number, files: Number, manyLanguages: Number, actualLanguage: String },
    components: {
      fieldF,
      birthdayF,
      checkboxF,
      dropdownF,
      radiobtnF,
      textareaF,
      displayF,
      videoM,
      imageM
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
        element: null,
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
          video: {
            value: 'video',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_VIDEO")
          },
          image: {
            value: 'image',
            name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_IMAGE")
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
        this.loading = true;
        axios({
          method: "get",
          url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getElement",
          params: {
            element: this.ID,
            gid: this.gid
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
          this.element = response.data;

          if(this.element.plugin == 'databasejoin'){
            this.plugin = this.element.params.database_join_display_type;
          } else if (this.element.plugin == 'date' || this.element.plugin == 'years') {
            this.plugin = 'birthday';
          } else {
            this.plugin = this.element.plugin;
          }

          this.axiostrad(this.element.label_tag)
              .then(rep => {
                this.label.fr = rep.data.fr;
                this.label.en = rep.data.en;
              });

          this.loading = false;
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
        this.getElement();
        this.getDatabases();
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
        if (this.element.plugin !== 'databasejoin' && this.element.plugin !== 'date' && this.element.plugin !== 'years') {
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
</style>
