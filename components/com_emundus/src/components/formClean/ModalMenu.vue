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
      <div class="em-flex-row em-flex-space-between em-mb-16">
        <h4>
          {{ translations.addMenu }}
        </h4>
        <button class="em-pointer em-transparent-button" @click.prevent="$modal.hide('modalMenu')">
          <span class="material-icons-outlined">close</span>
        </button>
      </div>

      <div>
        <div class="em-mb-16">
          <label class="em-w-100">{{ translations.ChooseExistingPageModel }} :</label>
          <select v-model="model_id" class="em-w-100" :disabled="Object.keys(models).length <= 0">
            <option value="-1"></option>
            <option v-for="model in models" :key="model.form_id" :value="model.form_id"> {{
                model.label[actualLanguage]
              }}</option>
          </select>
        </div>

        <div class="em-mb-16">
          <label>{{ translations.Name }}* :</label>
          <input v-model="label[actualLanguage]" type="text" maxlength="40" id="menu_label" class="em-w-100"
                 :class="{ 'is-invalid': errors}"/>
        </div>
        <p v-if="errors && model_id == -1" class="em-red-500-color">
          <span class="em-red-500-color">{{ translations.LabelRequired }}</span>
        </p>

        <div class="em-mb-16">
          <label>{{ translations.Intro }}</label>
                <editor v-for="(language,index_group) in languages"
                        v-if="language.sef === selectedLanguage"
                        :height="'30em'"
                        :text="intro[language.sef]"
                        :lang="actualLanguage"
                        :enable_variables="false"
                        :id="'editor_' + language.sef"
                        :key="dynamicComponent"
                        v-model="intro[language.sef]">
                </editor>
        </div>
      </div>

      <div class="em-flex-row em-flex-space-between em-mb-16">
        <button
            type="button"
            class="em-secondary-button em-w-auto"
            @click.prevent="$modal.hide('modalMenu')">
          {{ translations.Retour }}
        </button>
        <button
            type="button"
            class="em-primary-button em-w-auto"
            @click.prevent="createMenu()">
          {{ translations.Add }}
        </button>
      </div>

      <div class="em-page-loader" v-if="loading"></div>
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

      if (this.label[this.actualLanguage] != '' || this.model_id != -1) {
        this.submitted = true;

        if (this.actualLanguage == 'fr' && this.label.en == '') {
          this.label.en = 'My new page'
        } else if (this.actualLanguage == 'en' && this.label.fr == '') {
          this.label.fr = 'Ma nouvelle page';
        }

        axios({
          method: 'post',
          url: 'index.php?option=com_emundus&controller=formbuilder&task=createMenu',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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
          this.$emit('AddMenu', result.data);
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
      if (value != -1) {
        Object.values(this.models).forEach(model => {
          if (model.form_id == this.model_id) {
            this.label.fr = model.label.fr;
            this.label.en = model.label.en;

            var divfr = document.createElement("div");
            var diven = document.createElement("div");
            divfr.innerHTML = model.intro.fr;
            diven.innerHTML = model.intro.en;
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
