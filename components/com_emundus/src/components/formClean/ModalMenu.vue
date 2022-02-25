<template>
  <!-- modalC -->
  <span :id="'modalMenu'">
    <modal
        :name="'modalMenu'"
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
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalMenu')">
              <em class="fas fa-times"></em>
            </button>
          </div>
        <div class="update-field-header">
          <h2 class="update-title-header">
             {{translations.addMenu}}
          </h2>
        </div>
            </div>
      <div class="modalC-content">


        <div class="form-group">
          <label>{{translations.ChooseExistingPageModel}} :</label>
          <select v-model="model_id" class="dropdown-toggle" :disabled="Object.keys(models).length <= 0">
            <option value="-1"></option>
            <option v-for="model in models" :key="model.form_id" :value="model.form_id"> {{ model.label[actualLanguage] }}</option>
          </select>
        </div>
        <div class="form-group" :class="{ 'mb-0': can_translate.label}">
          <label>{{translations.Name}}* :</label>
          <div class="input-can-translate">
            <input v-model="label[actualLanguage]" type="text" maxlength="40" class="form__input field-general w-input" id="menu_label" style="margin: 0" :class="{ 'is-invalid': errors}"/>
            <button class="translate-icon" v-if="manyLanguages !== '0'" :class="{'translate-icon-selected': can_translate.label}" type="button" @click="can_translate.label = !can_translate.label"></button>
          </div>
        </div>
        <translation :label="label" :actualLanguage="actualLanguage" v-if="can_translate.label"></translation>
        <p v-if="errors && model_id == -1" class="error col-md-12 mb-2">
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
                <editor v-for="(language,index_group) in languages"
                        v-if="language.sef === selectedLanguage"
                        :height="'30em'"
                        :text="intro[language.sef]"
                        :lang="actualLanguage"
                        :enable_variables="false"
                        :id="'editor_' + language.sef"
                        :key="dynamicComponent"
                        v-model="intro[language.sef]"></editor>
            </div>
          </div>
        </div>
      </div>
      <div class="em-flex-row em-flex-space-between mb-1">
        <button
            type="button"
            class="bouton-sauvergarder-et-continuer w-retour"
            @click.prevent="$modal.hide('modalMenu')">
          {{translations.Retour}}
        </button>
        <button
            type="button"
            class="bouton-sauvergarder-et-continuer"
            @click.prevent="createMenu()">
          {{ translations.Add }}
        </button>
      </div>
      <div class="loading-form" style="top: 10vh" v-if="submitted">
        <Ring-Loader :color="'#12db42'" />
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");
import translation from "@/components/translation";
import Editor from "../editor";

export default {
  name: "modalMenu",
  components: {
    Editor,
    translation
  },
  props: {
    profileId: String,
    actualLanguage: String,
    manyLanguages: String,
    languages: Array
  },
  data() {
    return {
      can_translate: {
        label: false,
        intro: false
      },
      label: {
        fr: '',
        en: ''
      },
      intro: {
        fr: '',
        en: ''
      },
      dynamicComponent: 0,
      model_id: -1,
      template: false,
      models: [],
      errors: false,
      changes: false,
      submitted: false,
      selectedLanguage: this.actualLanguage,
      translations: {
        Name: "COM_EMUNDUS_ONBOARD_FIELD_NAME",
        Intro: "COM_EMUNDUS_ONBOARD_FIELD_INTRO",
        Retour: "COM_EMUNDUS_ONBOARD_ADD_RETOUR",
        Add: "COM_EMUNDUS_ONBOARD_ADD",
        dataSaved: "COM_EMUNDUS_ONBOARD_BUILDER_DATASAVED",
        informations: "COM_EMUNDUS_ONBOARD_BUILDER_INFORMATIONS",
        addMenu: "COM_EMUNDUS_ONBOARD_BUILDER_ADDMENU",
        LabelRequired: "COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME",
        ChooseExistingPageModel: "COM_EMUNDUS_ONBOARD_FORM_CHOOSE_EXISTING_MODEL_PAGE",
        TranslateEnglish: "COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH",
        SaveAsTemplate: "COM_EMUNDUS_ONBOARD_SAVE_AS_TEMPLATE",
      }
    };
  },
  methods: {
    beforeClose(event) {
      if (this.changes === true) {
        this.$emit(
            "show",
            "foo-velocity",
            "warn",
            this.dataSaved,
            this.informations
        );
      }
      this.label = {
        fr: '',
        en: ''
      }
      this.intro = {
        fr: '',
        en: ''
      }
      this.$emit("modalClosed");
      this.changes = false;
    },
    beforeOpen(event) {
      this.getExistingPages();
    },
    getExistingPages() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=formbuilder&task=getPagesModel"
      }).then(response => {
        this.models = response.data;
      });
    },
    createMenu() {
      this.changes = true;

      if(this.label[this.actualLanguage] != '' || this.model_id != -1) {
        this.submitted = true;

        if(this.actualLanguage=='fr'&& this.label.en==''){

          this.label.en='My new page'

        } else if(this.actualLanguage=='en'&& this.label.fr==''){
          this.label.fr='Ma nouvelle page';
        } else {
          this.label.en=this.label.en;
          this.label.fr=this.label.fr;
        }

        axios({
          method: "post",
          url:
              "index.php?option=com_emundus&controller=formbuilder&task=createMenu",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            label: this.label,
            intro: this.intro,
            prid: this.profileId,
            modelid: this.model_id,
            template: this.template
          })
        }).then((result) => {
          this.submitted = false;
          this.$modal.hide('modalMenu');
          this.$emit('AddMenu',result.data);
        }).catch(e => {
          console.log(e);
        });
      } else {
        this.errors = true;
      }
    }
  },

  watch: {
    model_id: function (value) {
      if(value != -1){
        Object.values(this.models).forEach(model => {
          if(model.form_id == this.model_id){
            this.label.fr = model.label.fr;
            this.label.en = model.label.en;

            var divfr = document.createElement("div");
            var diven = document.createElement("div");
            divfr.innerHTML =  model.intro.fr;
            diven.innerHTML =  model.intro.en;
            this.intro.fr = divfr.innerText;
            this.intro.en = diven.innerText;
          }
        });
      } else {
        this.label.fr = '';
        this.intro.fr = '';
      }
    },
  }
};
</script>

<style scoped>
</style>
