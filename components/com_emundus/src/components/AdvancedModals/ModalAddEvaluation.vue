<template>
  <!-- modalC -->
  <span :id="'modalAddEvaluation'">
    <modal
      :name="'modalAddEvaluation'"
      height="auto"
      transition="nice-modal-fade"
      :min-width="200"
      :min-height="200"
      :delay="100"
      :adaptive="true"
      :clickToClose="false"
      @before-open="beforeOpen"
    >
      <div class="fixed-header-modal">
        <div class="topright">
          <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalAddEvaluation')">
            <em class="fas fa-times"></em>
          </button>
          </div>
          <div class="update-field-header">
            <h2 class="update-title-header">
              {{ translations.addGrid }}
            </h2>
        </div>
      </div>
      <div class="modalC-content">
        <div class="form-group">
          <label>{{ translations.ChooseExistingGridModel }} :</label>
          <select v-model="model_id" class="dropdown-toggle">
            <option value="-1"></option>
            <option v-for="model in models" :key="model.form_id" :value="model.form_id">{{model.label}}</option>
          </select>
        </div>
        <div class="form-group" :class="{ 'mb-0': can_translate.label}">
          <label>{{ translations.Name }}<span class="em-red-500-color">*</span> :</label>
          <div class="input-can-translate">
            <input v-model="label.fr" type="text" maxlength="40" class="form__input field-general w-input" id="menu_label" style="margin: 0" :class="{ 'is-invalid': errors}"/>
            <button class="translate-icon" :class="{'translate-icon-selected': can_translate.label}" type="button" @click="can_translate.label = !can_translate.label"></button>
          </div>
        </div>
        <transition :name="'slide-down'" type="transition">
          <div class="inlineflex" v-if="can_translate.label">
            <label class="translate-label">
              {{ translations.TranslateEnglish }}
            </label>
            <i class="fas fa-sort-down"></i>
          </div>
        </transition>
        <transition :name="'slide-down'" type="transition">
          <div class="form-group mb-1" v-if="can_translate.label">
            <input v-model="label.en" type="text" maxlength="40" class="form__input field-general w-input"/>
          </div>
        </transition>
        <p v-if="errors && model_id == -1" class="error col-md-12 mb-2">
          <span class="error">{{ translations.LabelRequired }}</span>
        </p>
        <div class="form-group mt-1" :class="{'mb-0': can_translate.intro}">
          <label>{{ translations.Intro }} :</label>
          <div class="input-can-translate">
              <textarea v-model="intro.fr" class="form__input field-general w-input" rows="3" maxlength="300" style="margin: 0"></textarea>
              <button class="translate-icon" :class="{'translate-icon-selected': can_translate.intro}" type="button" @click="can_translate.intro = !can_translate.intro"></button>
          </div>
        </div>
        <transition :name="'slide-down'" type="transition">
          <div class="inlineflex" v-if="can_translate.intro">
            <label class="translate-label">
              {{ translations.TranslateEnglish }}
            </label>
            <em class="fas fa-sort-down"></em>
          </div>
        </transition>
        <transition :name="'slide-down'" type="transition">
          <div class="form-group mb-1" v-if="can_translate.intro">
            <textarea v-model="intro.en" rows="3" class="form__input field-general w-input" maxlength="300"></textarea>
          </div>
        </transition>
        <div class="col-md-12 em-flex-row" v-if="model_id == -1">
          <input type="checkbox" v-model="template">
          <label class="ml-10px">{{ translations.SaveAsTemplate }} :</label>
        </div>
      </div>
      <div class="em-flex-row em-flex-space-between mb-1">
        <button type="button"
          class="bouton-sauvergarder-et-continuer w-retour"
          @click.prevent="$modal.hide('modalAddEvaluation')"
        >{{ translations.Retour }}</button>
        <button type="button"
          class="bouton-sauvergarder-et-continuer"
          @click.prevent="createGrid()"
        >{{ translations.Continuer }}</button>
      </div>
      <div class="em-page-loader" v-if="submitted"></div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
  name: "modalAddEvaluation",
  components: {},
  props: { prog: Number, grid: Number },
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
      model_id: -1,
      template: 0,
      models: [],
      errors: false,
      changes: false,
      submitted: false,
      translations: {
        addGrid: "COM_EMUNDUS_ONBOARD_BUILDER_ADDGRID",
        ChooseExistingGridModel: "COM_EMUNDUS_ONBOARD_GRIDMODEL",
        Name: "COM_EMUNDUS_ONBOARD_FIELD_NAME",
        Intro: "COM_EMUNDUS_ONBOARD_FIELD_INTRO",
        Retour: "COM_EMUNDUS_ONBOARD_ADD_RETOUR",
        Continuer: "COM_EMUNDUS_ONBOARD_ADD_CONTINUER",
        LabelRequired: "COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME",
        TranslateEnglish: "COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH",
        SaveAsTemplate: "COM_EMUNDUS_ONBOARD_SAVE_AS_TEMPLATE",
      }
    };
  },
  methods: {
    beforeOpen(event) {
      this.getExistingGrids();
    },
    getExistingGrids() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=programme&task=getgridsmodel"
      }).then(response => {
        this.models = response.data.data;
      });
    },
    createGrid() {
      this.changes = true;

      if(this.label.fr != '' || this.model_id != -1) {
        if(!this.can_translate.label){
          this.label.en = this.label.fr;
        }
        if(!this.can_translate.intro){
          this.intro.en = this.intro.fr;
        }
        this.submitted = true;
        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=programme&task=creategrid",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            label: this.label,
            intro: this.intro,
            modelid: this.model_id,
            template: this.template,
            pid: this.prog
          })
        }).then((result) => {
          this.submitted = false;
          this.$emit("updateGrid");
          this.$modal.hide('modalAddEvaluation');
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
        this.models.forEach(model => {
          if(model.form_id == this.model_id){
            this.label.fr = model.label;
            this.intro.fr = model.intro;
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
