<template>
  <!-- modalC -->
  <span :id="'modalEditElement'">
    <modal
            :name="'modalEditElement' + ID"
            height="auto"
            transition="little-move-left"
            :min-width="200"
            :min-height="200"
            :delay="100"
            :adaptive="true"
            :clickToClose="true"
            @closed="beforeClose"
            @before-open="beforeOpen"
    >
      <div class="fixed-header-modal">
            <div class="topright">
              <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalEditElement' + ID)">
                <em class="fas fa-times"></em>
              </button>
            </div>
          <div class="update-field-header">
            <h2 class="update-title-header">
              {{ElementOptions}}
            </h2>
          </div>
        </div>
      <div v-if="element != null" class="modalC-content">
        <div class="mb-1">
          <a class="em-flex-row tool-icon mb-1" @click="publishUnpublishElement()">
            <em :class="[element.publish ? 'fa-eye-slash' : 'fa-eye','far']" style="width: 45px" :id="'publish_icon_' + element.id"></em>
            <span class="ml-10px" v-if="element.publish">{{Unpublish}}</span>
            <span class="ml-10px" v-if="!element.publish">{{Publish}}</span>
          </a>
          <a class="em-flex-row tool-icon" v-if="plugin != 'display'">
            <div class="toggle">
              <input type="checkbox" class="check" v-model="element.FRequire" @click="updateRequireElement()"/>
              <strong class="b switch"></strong>
              <strong class="b track"></strong>
            </div>
            <span class="ml-10px">{{Required}}</span>
          </a>
        </div>
        <div class="form-group mb-2">
          <label>{{fieldType}} :</label>
          <select id="select_type" class="dropdown-toggle" v-model="plugin" :disabled="(files != 0 && element.plugin == 'birthday') || (files != 0 && element.params.password == 6)">
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
          <radiobtnF v-if="plugin == 'radiobutton'" :element="element" :databases="databases"  @subOptions="subOptions"></radiobtnF>
          <textareaF v-if="plugin =='textarea'" :element="element"></textareaF>
          <displayF v-if="plugin =='display'" :element="element"></displayF>
          <fileF v-if="plugin =='emundus_fileupload'" :element="element" :prid="profileId"></fileF>
          <yesnoF v-if="plugin=='yesno'" :element="element"></yesnoF>
        </div>
      </div>
      <div class="em-flex-row em-flex-space-between mb-1">
        <button type="button"
                class="bouton-sauvergarder-et-continuer w-retour"
                @click.prevent="$modal.hide('modalEditElement' + ID)">
          {{Retour}}
        </button>
        <button type="button"
                class="bouton-sauvergarder-et-continuer"
                @click.prevent="UpdateParams"
        >{{ Continuer }}</button>
      </div>
      <div class="em-page-loader" v-if="loading"></div>
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
  import fileF from "./Plugin/fileupload";
  import yesnoF from './Plugin/yesno';


  const qs = require("qs");

  export default {
    name: "modalEditElement",
    props: {
      elementId: {
        type: Number,
        required: true
      },
      gid: Number,
      files: Number,
      manyLanguages: Number,
      actualLanguage: String,
      profileId:Number
    },
    components: {
      fieldF,
      birthdayF,
      checkboxF,
      dropdownF,
      radiobtnF,
      textareaF,
      displayF,
      fileF,
      yesnoF,
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
        // Plugins
        plugins: {
          field: {
            value: 'field',
            name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_FIELD")
          },
          birthday: {
            value: 'birthday',
            name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_BIRTHDAY")
          },
          checkbox: {
            value: 'checkbox',
            name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_CHECKBOX")
          },
          dropdown: {
            value: 'dropdown',
            name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_DROPDOWN")
          },
          radiobutton: {
            value: 'radiobutton',
            name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_RADIOBUTTON")
          },
          textarea: {
            value: 'textarea',
            name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_TEXTAREA")
          },
          display: {
            value: 'display',
            name: this.translate("COM_EMUNDUS_ONBOARD_TYPE_DISPLAY")
          },
          fileupload: {
            value: 'emundus_fileupload',
            name:  this.translate("COM_EMUNDUS_ONBOARD_TYPE_FILE")
          },
          yesno: {
            value: 'yesno',
            name:  'yesno'
            /*this.translate("COM_EMUNDUS_ONBOARD_TYPE_YESNO")*/
          }
        },
        databases: [],
        // Translations
        Name: this.translate("COM_EMUNDUS_ONBOARD_FIELD_NAME"),
        Require: this.translate("COM_EMUNDUS_ONBOARD_FIELD_REQUIRED"),
        Retour: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
        Continuer: this.translate("COM_EMUNDUS_ONBOARD_SAVE"),
        dataSaved: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DATASAVED"),
        informations: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_INFORMATIONS"),
        fieldType: this.translate("COM_EMUNDUS_ONBOARD_FIELD_TYPE"),
        Delete: this.translate("COM_EMUNDUS_ONBOARD_ACTION_DELETE"),
        LabelRequired: this.translate("COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME"),
        TranslateEnglish: this.translate("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
        ElementOptions: this.translate("COM_EMUNDUS_ONBOARD_ELEMENT_OPTIONS"),
        Unpublish: this.translate("COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH"),
        Publish: this.translate("COM_EMUNDUS_ONBOARD_ACTION_PUBLISH"),
        Required: this.translate("COM_EMUNDUS_ONBOARD_ACTIONS_REQUIRED"),
        update: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_UPDATE"),
        updating: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_UPDATING"),
        updateSuccess: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_UPDATESUCESS"),
        updateFailed: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_UPDATEFAILED"),
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
                  "index.php?option=com_emundus&controller=formbuilder&task=updateparams",
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
          this.$modal.hide('modalEditElement' + this.elementId);
        }).catch(e => {
          console.log(e);
        });
      },
      axiostrad: function(totrad) {
        return axios({
          method: "post",
          url:
                  "index.php?option=com_emundus&controller=formbuilder&task=getalltranslations",
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
          url: "index.php?option=com_emundus&controller=formbuilder&task=getElement",
          params: {
            element: this.elementId,
            gid: this.gid
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
          this.element = response.data;

          if(this.element.plugin == 'databasejoin'){
            this.element.params.database_join_display_type =='radio'?  this.plugin ='radiobutton':  this.plugin =this.element.params.database_join_display_type;

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

          if(this.files != 0 && this.element.plugin != 'birthday'){
            delete this.plugins.birthday;
          }

          this.loading = false;
        });
      },
      getDatabases(){
        axios({
          method: "get",
          url: "index.php?option=com_emundus&controller=formbuilder&task=getdatabasesjoin",
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
        this.$emit("modalClosed");
      },
      beforeOpen(event) {
        this.initialisation();
      },
      initialisation() {
        this.getElement();
        this.getDatabases();
      },

      publishUnpublishElement() {
        axios({
          method: "post",
          url:
              "index.php?option=com_emundus&controller=formbuilder&task=publishunpublishelement",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            element: this.element.id,
          })
        }).then(response => {
          this.element.publish = !this.element.publish;
          this.$emit("publishUnpublishEvent");
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

      updateRequireElement() {
        setTimeout(() => {
          axios({
            method: "post",
            url:
                "index.php?option=com_emundus&controller=formbuilder&task=changerequire",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              element: this.element
            })
          }).then(() => {
            this.$emit("updateRequireEvent");
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
        switch (value){
          case 'dropdown':

            if(this.element.plugin !== 'databasejoin'){
              this.element.plugin = value;
            }
            break;
          case 'radiobutton':

            if(this.element.plugin !== 'databasejoin'){
              this.element.plugin = value;
            }
            break;
          case 'birthday':
            if(this.element.plugin !== 'date' && this.element.plugin !== 'years'){
              this.element.plugin = value;
            }
            break;
          default:
            this.element.plugin = value;
        }
      }
    },
  };
</script>

<style scoped>
  .check{
    width: 100%;
  }
</style>
