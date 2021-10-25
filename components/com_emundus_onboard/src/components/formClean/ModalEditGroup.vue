<template>
  <!-- modalC -->
  <span :id="'modalEditGroup'">
    <modal
        :name="'modalEditGroup' + ID"
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
              <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalEditGroup' + ID)">
                <em class="fas fa-times"></em>
              </button>
            </div>
          <div class="update-field-header">
            <h2 class="update-title-header">
              Edition de l'introduction
            </h2>
          </div>
        </div>

      <div class="modalC-content">
        <label>Introduction :</label>

          <div class="container-evaluation">
        <ul class="menus-home-row" v-if="manyLanguages !== '0'">
            <li v-for="(value, index) in languages" :key="index" class="MenuFormHome">
                <a class="MenuFormItemHome"
                   @click="changeTranslation(index)"
                   :class="indexHighlight == index ? 'MenuFormItemHome_current' : ''">
                    {{value}}
                </a>
            </li>
        </ul>
        <div class="form-group controls" style="margin-top: 5em" v-if="indexHighlight == 0 && this.intros.fr != null">
            <editor :height="'30em'" :text="intros.fr" :lang="actualLanguage" :enable_variables="false" :id="'editor_fr'" :key="dynamicComponent" v-model="intros.fr"></editor>
        </div>
        <div class="form-group controls" style="margin-top: 5em" v-if="indexHighlight == 1 && this.intros.en != null">
            <editor :height="'30em'" :text="intros.en" :lang="actualLanguage" :enable_variables="false" :id="'editor_en'" :key="dynamicComponent" v-model="intros.en"></editor>
        </div>
    </div>
       <!-- <editor :height="'50em'" :width="'100'" :text="group_intro" :lang="'fr'" :enable_variables="false" v-model="group_intro"
        :id="'element_' + gid"></editor>-->


      <div class="d-flex justify-content-between mb-1">
        <button type="button"
                class="bouton-sauvergarder-et-continuer w-retour"
                @click.prevent="$modal.hide('modalEditGroup' + ID)">
          {{ Retour }}
        </button>
        <button type="button"
                class="bouton-sauvergarder-et-continuer"
                @click.prevent="UpdateParams"
        >{{ Continuer }}</button>
      </div>

      <div class="loading-form" v-if="loading">
        <Ring-Loader :color="'#12DB42'"/>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
import Editor from "../editor";


const qs = require("qs");

export default {
  name: "modalEditGroup",
  props: {ID: Number, gid: Number, files: Number, manyLanguages: Number, actualLanguage: String, profileId: Number,intros: Object,intro_tag:String,group:Object},
  components: {
    Editor
    /*fieldF,
    birthdayF,
    checkboxF,
    dropdownF,
    radiobtnF,
    textareaF,
    displayF,
    fileF,
    yesnoF,*/
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
      dynamicComponent: 0,
      indexHighlight: 0,
      plugin: '',
      element: null,
      group_intro:"",
      group_params:null,
      group:null,
      translate: {
        label: false,
      },
      languages: [
        "Français",
        "Anglais"
      ],
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
        fileupload: {
          value: 'emundus_fileupload',
          name: Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_FILE")
        },
        yesno: {
          value: 'yesno',
          name: 'yesno'
          /*Joomla.JText._("COM_EMUNDUS_ONBOARD_TYPE_YESNO")*/
        }
      },
      databases: [],
      // Translations
      Name: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIELD_NAME"),
      Require: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIELD_REQUIRED"),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_SAVE"),
      dataSaved: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DATASAVED"),
      informations: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_INFORMATIONS"),
      fieldType: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIELD_TYPE"),
      Delete: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTION_DELETE"),
      LabelRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME"),
      TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
      ElementOptions: Joomla.JText._("COM_EMUNDUS_ONBOARD_ELEMENT_OPTIONS"),
      Unpublish: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTION_UNPUBLISH"),
      Publish: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTION_PUBLISH"),
      Required: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTIONS_REQUIRED"),
      update: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATE"),
      updating: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATING"),
      updateSuccess: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATESUCESS"),
      updateFailed: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATEFAILED"),
      //
    };
  },
  methods: {
    subOptions(sO) {
      this.sublabel = sO;
    },
    changeTranslation(index) {
      this.indexHighlight = index;
      this.dynamicComponent++;
    },
    UpdateParams() {
      this.changes = true;
      this.group_params.intro = this.intros[this.actualLanguage];

      axios({
        method: "post",
        url:
            "index.php?option=com_emundus_onboard&controller=formbuilder&task=formsTrad",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          group: this.gid,
          labelTofind: this.intro_tag,
          NewSubLabel: this.intros,
          intro: "true",
        })
      }).then((resp) => {
        if (resp.data.status == 0 ) {
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=updategroupintrowithouttranslation",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            data: qs.stringify({
              gid: this.gid,
              group_intro: JSON.stringify(this.group_params),
            }),
          }).then((response) => {

            this.$modal.hide('modalEditGroup' + this.ID);

          }).catch(e => {
        console.log(e);
          })}

        this.group.intros=this.intros;
        this.group.group_intro=this.intros[this.actualLanguage];
        setTimeout(() => {
          this.$emit("reloadGroup",this.group);
        }, 200);
        this.$modal.hide('modalEditGroup' + this.ID);
      });
    },
    getElement() {
      this.loading = true;
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=retrieveGroup",
        params: {
          gid: this.gid,
        },
        paramsSerializer: (params) => {
          return qs.stringify(params);
        }
      }).then( response => {

        this.group = response.data;
        this.group_params = JSON.parse(response.data.params);
        this.loading = false;
      });
    },
    beforeClose(event) {
      /* if (this.changes === true) {
         this.changes = false;
         this.$emit(
             "show",
             "foo-velocity",
             "warn",
             this.dataSaved,
             this.informations
         );
       }*/
      this.$emit("modalClosed");
    },
    beforeOpen(event) {
      this.initialisation();
    },
    initialisation() {
      this.getElement();
    },

    publishUnpublishElement() {
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus_onboard&controller=formbuilder&task=publishunpublishelement",
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
              "index.php?option=com_emundus_onboard&controller=formbuilder&task=changerequire",
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
    getlabel: function () {
      return this.tempEl.label_raw;
    }
  },
  watch: {
    element: function () {
      this.tempEl = JSON.parse(JSON.stringify(this.element));
    },
    plugin: function (value) {
      switch (value) {
        case 'dropdown':
          if (this.element.plugin !== 'databasejoin') {
            this.element.plugin = value;
          }
          break;
        case 'birthday':
          if (this.element.plugin !== 'date' && this.element.plugin !== 'years') {
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
.check {
  width: 100%;
}
</style>
