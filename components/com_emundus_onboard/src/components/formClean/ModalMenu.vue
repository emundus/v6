<template>
  <!-- modalC -->
  <span :id="'modalMenu'">
    <modal
      :name="'modalMenu'"
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
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalMenu')">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{addMenu}}
          </h2>
        </div>

        <div class="form-group">
          <label>{{ChooseExistingPageModel}} :</label>
          <select v-model="model_id" class="dropdown-toggle">
            <option value="-1"></option>
            <option v-for="(model, index) in models" :value="model.form_id">{{model.label.fr}}</option>
          </select>
        </div>
        <div class="form-group" :class="{ 'mb-0': translate.label}">
          <label>{{Name}}* :</label>
          <div class="input-can-translate">
            <input v-model="label[actualLanguage]" type="text" maxlength="40" class="form__input field-general w-input" id="menu_label" style="margin: 0" :class="{ 'is-invalid': errors}"/>
            <button class="translate-icon" v-if="manyLanguages !== '0'" :class="{'translate-icon-selected': translate.label}" type="button" @click="translate.label = !translate.label"></button>
          </div>
        </div>
        <translation :label="label" :actualLanguage="actualLanguage" v-if="translate.label"></translation>
        <p v-if="errors && model_id == -1" class="error col-md-12 mb-2">
          <span class="error">{{LabelRequired}}</span>
        </p>
        <div class="form-group mt-1" :class="{'mb-0': translate.intro}">
          <label>{{Intro}} :</label>
          <div class="input-can-translate">
              <textarea v-model="intro[actualLanguage]" class="form__input field-general w-input" rows="3" maxlength="300" style="margin: 0"></textarea>
              <button class="translate-icon" v-if="manyLanguages !== '0'" :class="{'translate-icon-selected': translate.intro}" type="button" @click="translate.intro = !translate.intro"></button>
          </div>
        </div>
        <translation :label="intro" :actualLanguage="actualLanguage" v-if="translate.intro"></translation>
        <div class="col-md-12 d-flex" v-if="model_id == -1">
          <input type="checkbox" v-model="template">
          <label class="ml-10px">{{SaveAsTemplate}} :</label>
        </div>
      </div>
      <div class="col-md-12 mb-1">
        <a
          class="bouton-sauvergarder-et-continuer-3"
          @click.prevent="createMenu()"
        >{{ Continuer }}</a>
        <a
          class="bouton-sauvergarder-et-continuer-3 w-retour"
          @click.prevent="$modal.hide('modalMenu')"
        >{{Retour}}</a>
      </div>
      <div class="loading-form" style="top: 10vh" v-if="submitted">
        <Ring-Loader :color="'#de6339'" />
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");
import translation from "@/components/translation";

export default {
  name: "modalMenu",
  components: {
    translation
  },
  props: {
    profileId: Number,
    actualLanguage: String,
    manyLanguages: Number
  },
  data() {
    return {
      translate: {
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
      model_id: -1,
      template: false,
      models: [],
      errors: false,
      changes: false,
      submitted: false,
      Name: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIELD_NAME"),
      Intro: Joomla.JText._("COM_EMUNDUS_ONBOARD_FIELD_INTRO"),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      dataSaved: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_DATASAVED"),
      informations: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_INFORMATIONS"),
      addMenu: Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_ADDMENU"),
      LabelRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME"),
      ChooseExistingPageModel: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_CHOOSE_EXISTING_MODEL_PAGE"),
      TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
      SaveAsTemplate: Joomla.JText._("COM_EMUNDUS_ONBOARD_SAVE_AS_TEMPLATE"),
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
      this.changes = false;
    },
    beforeOpen(event) {
      this.getExistingPages();
    },
    getExistingPages() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=formbuilder&task=getPagesModel"
      }).then(response => {
        this.models = response.data;
      });
    },
    createMenu() {
      this.changes = true;

      if(this.label.fr != '' || this.model_id != -1) {
        if(this.label.en == ''){
          this.label.en = this.label.fr;
        }
        if(this.intro.en = ''){
          this.intro.en = this.intro.fr;
        }
        this.submitted = true;
        axios({
          method: "post",
          url:
                  "index.php?option=com_emundus_onboard&controller=formbuilder&task=createMenu",
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
</style>
