<template>
  <div class="section-principale">
    <div class="w-container">
      <div class="sous-container">
        <p class="required">{{RequiredFieldsIndicate}}</p>
        <div class="heading-form">
          <div class="icon-title programme"></div>
          <h2 class="heading">{{ Program }}</h2>
        </div>
        <p class="paragraphe-sous-titre">
          {{ AddProgramDesc }}
        </p>
        <div class="w-form">
          <form id="program-form" @submit.prevent="submit">
            <div class="form-group prog-label">
              <label for="prog_label">{{ProgName}} *</label>
              <input
                id="prog_label"
                type="text"
                class="form__input field-general w-input"
                placeholder=" "
                v-model="form.label"
                v-focus
                @keyup="updateCode"
                :class="{ 'is-invalid': errors.label }"
              />
            </div>
            <p v-if="errors.label" class="error col-md-12 mb-2">
              <span class="error">{{LabelRequired}}</span>
            </p>

            <!--<div class="form-group prog-code">
              <label for="prog_code" style="top: 12.8em">{{ProgCode}} *</label>
              <input
                id="prog_code"
                type="text"
                class="form__input field-general w-input"
                placeholder=" "
                v-model="form.code"
                @keyup="checkCode"
                :class="{ 'is-invalid': errors.code }"
              />
            </div>
            <p v-if="errors.code" class="error col-md-12 mb-2">
              <span class="error">{{CodeRequired}}</span>
            </p>-->

            <div class="form-group prog-label">
              <label for="prog_category" style="top: 10.7em">{{ChooseCategory}}</label>
              <autocomplete
                @searched="onSearchCategory"
                :id="'prog_category'"
                :items="this.categories"
                :year="form.programmes"
              />
            </div>

            <div class="form-group controls">
              <editor :height="'30em'" :text="form.notes" :lang="actualLanguage" v-if="dynamicComponent" :enable_variables="false" :id="'program'" v-model="form.notes"  :placeholder="ProgramResume"></editor>
            </div>

            <div class="form-group d-flex">
              <div class="toggle">
                <input
                  type="checkbox"
                  true-value="1"
                  false-value="0"
                  class="check"
                  id="published"
                  name="published"
                  v-model="form.published"
                />
                <strong class="b switch"></strong>
                <strong class="b track"></strong>
              </div>
              <label for="published" class="ml-10px mb-0">{{ Publish }}</label>
            </div>

            <div class="form-group d-flex last-container">
              <div class="toggle">
                <input
                  type="checkbox"
                  true-value="1"
                  false-value="0"
                  class="check"
                  id="apply"
                  name="apply"
                  v-model="form.apply_online"
                />
                <strong class="b switch"></strong>
                <strong class="b track"></strong>
              </div>
              <label for="apply" class="ml-10px mb-0">{{ DepotDeDossier }}</label>
            </div>
            <div class="section-sauvegarder-et-continuer">
              <div class="w-container">
                <div class="container-evaluation w-clearfix">
                  <button class="bouton-sauvergarder-et-continuer"
                          type="button"
                          @click="quit = 1; submit()">
                    {{ Continuer }}
                  </button>
                  <button class="bouton-sauvergarder-et-continuer w-quitter"
                          type="button"
                          @click="quit = 0; submit()">
                    {{ Quitter }}
                  </button>
                  <button
                    type="button"
                    class="bouton-sauvergarder-et-continuer w-retour"
                    onclick="history.go(-1)"
                  >
                    {{ Retour }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="loading-form" v-if="submitted">
      <RingLoader :color="'#de6339'" />
    </div>
    <tasks></tasks>
  </div>
</template>

<script>
import { required } from "vuelidate/lib/validators";
import axios from "axios";
import Editor from "../components/editor";
import Autocomplete from "../components/autocomplete";
import Tasks from "@/views/tasks";

const qs = require("qs");

export default {
  name: "addProgram",

  components: {
    Tasks,
    Editor,
    Autocomplete
  },

  directives: { focus: {
      inserted: function (el) {
        el.focus()
      }
    }
  },

  props: {
    prog: Number,
    actualLanguage: String
  },

  data: () => ({
    dynamicComponent: false,
    isHidden: false,

    new_category: "",
    categories: [],
    cats: [],
    programs: [],

    quit: 1,

    Program: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM"),
    AddProgram: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDPROGRAM"),
    ChooseProg: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_CHOOSEPROG"),
    Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
    Quitter: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_QUITTER"),
    Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
    Publish: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
    DepotDeDossier: Joomla.JText._("COM_EMUNDUS_ONBOARD_DEPOTDEDOSSIER"),
    ProgName: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGNAME"),
    ProgCode: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGCODE"),
    ChooseCategory: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSECATEGORY"),
    NameCategory: Joomla.JText._("COM_EMUNDUS_ONBOARD_NAMECATEGORY"),
    LabelRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME"),
    CodeRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROG_REQUIRED_CODE"),
    CategoryRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROG_REQUIRED_CATEGORY"),
    RequiredFieldsIndicate: Joomla.JText._("COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE"),
    ProgramResume: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_RESUME"),
    AdvancedSettings: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADVANCED_SETTINGS"),

    form: {
      label: "",
      code: "",
      programmes: "",
      notes: "",
      synthesis:
        '<ul><li><strong>[APPLICANT_NAME]</strong></li><li><a href="mailto:[EMAIL]">[EMAIL]</a></li></ul>',
      tmpl_trombinoscope:
        '<table cellpadding="2" style="width: 100%;"><tbody><tr style="border-collapse: collapse;"><td align="center" valign="top" style="text-align: center;"><p style="text-align: center;"><img src="[PHOTO]" alt="Photo" height="100" /> </p><p style="text-align: center;"><b>[NAME]</b><br /></p></td></tr></tbody></table>',
      tmpl_badge:
        '<table width="100%"><tbody><tr><td style="vertical-align: top; width: 100px;" align="left" valign="middle" width="30%"><img src="[LOGO]" alt="Logo" height="50" /></td><td style="vertical-align: top;" align="left" valign="top" width="70%"><b>[NAME]</b></td></tr></tbody></table>\n',
      published: 1,
      apply_online: 1
    },
    submitted: false,
    errors: {
      label: false,
      code: false,
      category: false
    }
  }),

  validations: {
    form: {
      label: { required },
      code: { required },
    }
  },

  methods: {
    submit() {
      this.errors.label = false;
      this.errors.code = false;
      this.errors.category = false;

      if(this.form.label == ""){
        this.errors.label = true;
        return 0;
      }

      if(this.form.code == ""){
        this.errors.code = true;
        return 0;
      }

      // Check if we are creating a new category and set the form category element
      if (this.new_category !== "") {
        this.form.programmes = this.new_category;
      }

      // stop here if form is invalid
      this.$v.$touch();

      if (this.$v.$invalid) {
        return;
      }
      this.submitted = true;
      if (this.prog !== "") {
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=program&task=updateprogram",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({ body: this.form, code: this.prog })
        })
          .then(response => {
            this.quitFunnelOrContinue(this.quit);
          })
          .catch(error => {
            console.log(error);
          });
      } else {
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=program&task=createprogram",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({ body: this.form })
        })
          .then(response => {
            this.prog = response.data.data;
            this.quitFunnelOrContinue(this.quit);
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
    onSearchCategory(value) {
      this.form.programmes = value;
    },
    updateCode() {
      if(this.form.label !== ''){
        this.form.code = this.form.label.toUpperCase().replace(/[^a-zA-Z0-9]/g,'_').substring(0,10) + '_00';
        this.programs.forEach((element, index) => {
          if(this.form.code == element.code){
            let newCode = parseInt(element.code.split('_')[1]) + 1;
            if(newCode > 10) {
              this.form.code = this.form.label.toUpperCase() + '_' + newCode;
            } else {
              this.form.code = this.form.label.toUpperCase() + '_0' + newCode;
            }
          }
        });
      } else {
        this.form.code = '';
      }
    },

    checkCode() {
      this.programs.forEach((element, index) => {
        if(this.form.code.toUpperCase() == element.code){
          let newCode = parseInt(element.code.split('_')[1]) + 1;
          if(newCode > 10) {
            this.form.code = this.form.label.toUpperCase()  + '_' + newCode;
          } else {
            this.form.code = this.form.label.toUpperCase()  + '_0' + newCode;
          }
        }
      });
    },

    quitFunnelOrContinue(quit) {
      if (quit == 0) {
        window.location.href = '/configuration-programs'
      }
      else if (quit == 1) {
        window.location.replace('index.php?option=com_emundus_onboard&view=program&layout=advancedsettings&pid=' + this.prog);
      }
    },
  },

  created() {
    axios
      .get("index.php?option=com_emundus_onboard&controller=program&task=getprogramcategories")
      .then(response => {
        this.categories = response.data.data;
        for (var i = 0; i < this.categories.length; i++) {
          this.cats.push(this.categories[i]);
        }
        if (this.prog !== "") {
          axios
            .get(
              `index.php?option=com_emundus_onboard&controller=program&task=getprogrambyid&id=${this.prog}`
            ).then(rep => {
              this.form.code = rep.data.data.code;
              this.form.label = rep.data.data.label;
              this.form.notes = rep.data.data.notes;
              this.form.programmes = rep.data.data.programmes;
              this.form.tmpl_badge = rep.data.data.tmpl_badge;
              this.form.published = rep.data.data.published;
              this.form.apply_online = rep.data.data.apply_online;
              if (rep.data.data.synthesis != null) {
                this.form.synthesis = rep.data.data.synthesis.replace(/>\s+</g, "><");
              }
              if (rep.data.data.tmpl_trombinoscope != null) {
                this.form.tmpl_trombinoscope = rep.data.data.tmpl_trombinoscope.replace(
                  />\s+</g,
                  "><"
                );
              }
              this.dynamicComponent = true;
            }).catch(e => {
              console.log(e);
            });
        } else {
          this.dynamicComponent = true;
        }
      })
      .catch(e => {
        console.log(e);
      });

    axios
            .get("index.php?option=com_emundus_onboard&controller=program&task=getallprogram")
            .then(response => {
              this.programs = response.data.data;
              this.programs.sort((a, b) => a.id - b.id);
            })
            .catch(e => {
              console.log(e);
            });
  }
};
</script>

<style scoped>
</style>
