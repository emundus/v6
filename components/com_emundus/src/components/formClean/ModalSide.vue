<template>
  <!-- modalC -->
  <span :id="'modalSide'">
    <modal
      :name="'modalSide' + ID"
      height="auto"
      transition="little-move-left"
      :min-width="200"
      :min-height="200"
      :delay="100"
      :adaptive="true"
      :clickToClose="true"
      @closed="beforeClose"
      @before-open="beforeOpen">
      <div class="fixed-header-modal">
        <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalSide' + ID)">
              <em class="fas fa-times"></em>
            </button>
          </div>
        <div class="update-field-header">
          <h2 class="update-title-header">
             {{translations.editMenu}}
          </h2>
        </div>
      </div>
      <div class="modalC-content">

        <div class="form-group" :class="{ 'mb-0': can_translate.label}">
            <label>{{translations.Name}} :</label>
          <div class="input-can-translate">
            <input v-model="label[actualLanguage]" type="text" maxlength="40" class="form__input field-general w-input" style="margin: 0" :class="{ 'is-invalid': errors}"/>
            <button class="translate-icon" :class="{'translate-icon-selected': can_translate.label}" type="button" v-if="manyLanguages !== '0'" @click="can_translate.label = !can_translate.label"></button>
          </div>
          <translation :label="label" :actualLanguage="actualLanguage" v-if="can_translate.label"></translation>
        </div>
        <p v-if="errors" class="error col-md-12 mb-2">
          <span class="error">{{translations.LabelRequired}}</span>
        </p>

        <div class="form-group mt-1" :class="{'mb-0': can_translate.intro}">
          <div class="em-flex-row">
            <label>{{translations.Intro}}</label>
            <button class="translate-icon" style="right: 0" v-if="manyLanguages !== '0'" :class="{'translate-icon-selected': can_translate.intro}" type="button" @click="can_translate.intro = !can_translate.intro"></button>
          </div>
          <div>
            <div class="em-flex-row" v-if="can_translate.intro">
              <span>{{translations.TranslateIn}} : </span>
              <select v-model="selectedLanguage" v-if="manyLanguages !== '0'" @change="dynamicComponent++" style="margin: 10px 0;">
                <option v-for="language in languages" :key="language.sef" :value="language.sef">{{language.title_native}}</option>
              </select>
            </div>
            <div class="input-can-translate">
                <editor
                  v-if="intro.hasOwnProperty(selectedLanguage)"
                  :height="'30em'"
                  :text="intro[selectedLanguage]"
                  :lang="actualLanguage"
                  :enable_variables="false"
                  :id="'editor_' + selectedLanguage"
                  :key="dynamicComponent"
                  v-model="intro[selectedLanguage]"></editor>
            </div>
          </div>
        </div>

        <div class="form-group em-flex-row mb-1" id="template_checkbox" style="align-items: center">
          <input type="checkbox" v-model="template">
          <label class="ml-10px mb-0">{{translations.SaveAsTemplate}}</label>
        </div>

        <div class="em-flex-row em-flex-space-between mb-1">
          <button
              class="bouton-sauvergarder-et-continuer w-retour"
              @click.prevent="$modal.hide('modalSide' + ID)">
            {{translations.Retour}}
          </button>
          <div class="em-flex-row">
          <button
              class="bouton-sauvergarder-et-continuer"
              @click.prevent="$modal.hide('modalSide' + ID) & UpdateParams()">
            {{translations.Continuer}}
          </button>
          </div>
        </div>
        <div class="form-group em-flex-row mb-1">
          <button class="bouton-sauvergarder-et-continuer w-delete"
                  @click.prevent="deleteMenu()"
                  v-if="menus.length > 1 && files == 0">
            {{translations.Delete}}
          </button>
        </div>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import Translation from "@/components/translation"
import Editor from "../editor";
import List from "../../views/list";

const qs = require("qs");

export default {
  name: "modalSide",
  components: {
    List,
    Editor,
    Translation
  },
  props: { ID: String, element: Object, index: Number, menus: Array, files: Number, link: String, manyLanguages: String, actualLanguage: String,  languages: Array },
  data() {
    return {
      tempEl: [],
      can_translate: {
        label: false,
        intro: false
      },
      label: {},
      intro: {},
      dynamicComponent: 0,
      template: false,
      errors: false,
      changes: false,
      selectedLanguage: this.actualLanguage,
      translations: {
        Retour: "COM_EMUNDUS_ONBOARD_ADD_RETOUR",
        Continuer: "COM_EMUNDUS_ONBOARD_ADD_CONTINUER",
        dataSaved: "COM_EMUNDUS_ONBOARD_BUILDER_DATASAVED",
        informations: "COM_EMUNDUS_ONBOARD_BUILDER_INFORMATIONS",
        orderingMenu: "COM_EMUNDUS_ONBOARD_BUILDER_MENUORDERING",
        editMenu: "COM_EMUNDUS_ONBOARD_BUILDER_EDITMENU",
        Name: "COM_EMUNDUS_ONBOARD_FIELD_NAME",
        Intro: "COM_EMUNDUS_ONBOARD_FIELD_INTRO",
        Delete: "COM_EMUNDUS_ONBOARD_ACTION_DELETE",
        TranslateEnglish: "COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH",
        LabelRequired: "COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME",
        SaveAsTemplate: "COM_EMUNDUS_ONBOARD_SAVE_AS_TEMPLATE",
        TranslateIn: "COM_EMUNDUS_ONBOARD_TRANSLATE_IN",
      }
    };
  },
  methods: {
    async UpdateParams() {
      this.changes = true;

      await this.axioschange(this.intro, this.tempEl.intro_raw);
      await this.axioschange(this.label, this.tempEl.show_title.titleraw);
      await this.updatefalang(this.label);

      await this.saveAsTemplate();

      this.element = JSON.parse(JSON.stringify(this.tempEl));
      this.$emit("UpdateName", this.index, this.label[this.actualLanguage]);
      this.$emit("UpdateIntro", this.index, this.intro[this.actualLanguage]);
    },

    /*
      Events
     */
    beforeClose(event) {
      if (this.changes !== false) {
        this.$emit(
          "show",
          "foo-velocity",
          "success",
          this.dataSaved,
          this.informations
        );
        this.changes = false;
      }
      this.$emit("modalClosed");
    },
    beforeOpen(event) {
      this.initialisation();
    },

    /*
      Update methods
     */
    axioschange(label, labelraw) {
      return new Promise(resolve => {
          axios({
          method: "post",
          url:
            "index.php?option=com_emundus&controller=formbuilder&task=formsTrad",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            labelTofind: labelraw,
            NewSubLabel: label
          })
        }).then((response) => {
          resolve(response.data.status);
        }).catch(e => {
          console.log(e);
        });
    })
    },
    updatefalang(label){
      return new Promise(resolve => {
        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=formbuilder&task=updatemenulabel",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            label: label,
            pid: this.element.id
          })
        }).then((result) => {

          resolve(result.data.status);
        });
      })
    },
    saveAsTemplate() {
      return new Promise(resolve => {
        axios({
          method: "post",
          url:
              "index.php?option=com_emundus&controller=formbuilder&task=savemenuastemplate",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            menu: this.element,
            template: this.template,
          })
        }).then((response) => {

          resolve(response.data.scalar)
        });
      })
    },
    //

    /*
      Get translations
     */
    async axiostrad(totrad) {
      return await axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=formbuilder&task=getalltranslations",
        params: {
          toJTEXT: totrad ,
        },
        paramsSerializer: params => {
           return qs.stringify(params);
        }
      }).then((rep) => {
        return rep.data;
      });
    },
    //

    deleteMenu() {
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_DELETEMENU"),
        text: this.translate("COM_EMUNDUS_ONBOARD_CANT_REVERT"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#de6339',
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          axios({
            method: "post",
            url:
                    "index.php?option=com_emundus&controller=formbuilder&task=deletemenu",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              mid: this.element.id,
            })
          }).then((response) => {
            this.$emit('removeMenu', this.element.id);
            this.$modal.hide('modalSide' + this.ID);
          });
        }
      });
    },

    checkIfTemplate(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=formbuilder&task=getPagesModel"
      }).then(response => {

        var BreakException = {};
        try {
          Object.values(response.data).forEach((model, index) => {
            if (model.form_id == this.element.id) {
              this.template = true;
              throw BreakException;
            }
          });
        } catch (e) {
          if (e !== BreakException) throw this.template = false;
        }
      });
    },

    initialisation() {
      this.tempEl = JSON.parse(JSON.stringify(this.element));
      this.axiostrad(this.tempEl.intro_raw)
        .then((intro_translated) => {
          this.intro = intro_translated
        })
        .catch(function(intro_translated) {
          console.log(intro_translated);
        });
      this.axiostrad(this.tempEl.show_title.titleraw)
        .then((label_translated) => {
          this.label = label_translated
        })
        .catch(function(label_translated) {
          console.log(label_translated);
        });
      this.checkIfTemplate();
    },
  },
  watch: {
    element: function() {
      this.tempEl = JSON.parse(JSON.stringify(this.element));
    }
  },
  created: function() {}
};
</script>

<style scoped>
#template_checkbox input{
  margin: 0 !important;
}
</style>
