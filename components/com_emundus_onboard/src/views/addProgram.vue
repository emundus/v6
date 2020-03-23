<template>
  <div class="section-principale">
    <div class="w-container">
      <div class="sous-container">
        <h2 class="heading">{{ AddProgram }}</h2>
        <p class="paragraphe-sous-titre">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros
          elementum tristique.
        </p>
        <div class="w-form">
          <form id="program-form" @submit.prevent="submit">
            <div class="form-group">
              <input
                type="text"
                class="form__input field-general w-input"
                :placeholder="ProgName"
                v-model="form.label"
              />
            </div>

            <div class="form-group">
              <input
                type="text"
                class="form__input field-general w-input"
                :placeholder="ProgCode"
                v-model="form.code"
              />
            </div>

            <div class="form-group container-flexbox-choisir-ou-plus w-clearfix">
              <autocomplete
                @searched="onSearchCategory"
                :name="ChooseCategory"
                :items="this.categories"
                :year="form.programmes"
              />
            </div>

            <div class="form-group controls">
              <editor :text="form.notes" v-if="dynamicComponent" v-model="form.notes"></editor>
            </div>

            <div class="form-group">
              <label for="published">{{ Publish }}</label>
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
            </div>

            <div class="form-group last-container">
              <label for="apply">{{ DepotDeDossier }}</label>
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
            </div>
            <div class="icon-title programme"></div>
            <div class="section-sauvegarder-et-continuer">
              <div class="w-container">
                <div class="container-evaluation w-clearfix">
                  <button type="submit" class="bouton-sauvergarder-et-continuer w-button">
                    {{ Continuer }}
                  </button>
                  <button
                    type="button"
                    class="bouton-sauvergarder-et-continuer w-retour w-button"
                    onclick="window.location.href='programs'"
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
  </div>
</template>

<script>
import { required } from "vuelidate/lib/validators";
import axios from "axios";
import Editor from "../components/editor";
import Autocomplete from "../components/autocomplete";

const qs = require("qs");

export default {
  name: "addProgram",

  components: {
    Editor,
    Autocomplete
  },

  props: {
    prog: Number
  },

  data: () => ({
    dynamicComponent: false,
    isHidden: false,

    new_category: "",
    categories: [],
    cats: [],

    Program: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_PROGRAM"),
    AddProgram: Joomla.JText._("COM_EMUNDUSONBOARD_ADDPROGRAM"),
    ChooseProg: Joomla.JText._("COM_EMUNDUSONBOARD_ADDCAMP_CHOOSEPROG"),
    Retour: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_RETOUR"),
    Continuer: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_CONTINUER"),
    Publish: Joomla.JText._("COM_EMUNDUSONBOARD_FILTER_PUBLISH"),
    DepotDeDossier: Joomla.JText._("COM_EMUNDUSONBOARD_DEPOTDEDOSSIER"),
    ProgName: Joomla.JText._("COM_EMUNDUSONBOARD_PROGNAME"),
    ProgCode: Joomla.JText._("COM_EMUNDUSONBOARD_PROGCODE"),
    ChooseCategory: Joomla.JText._("COM_EMUNDUSONBOARD_CHOOSECATEGORY"),
    NameCategory: Joomla.JText._("COM_EMUNDUSONBOARD_NAMECATEGORY"),

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
    submitted: false
  }),

  validations: {
    form: {
      label: { required },
      code: { required },
      programmes: { required }
    }
  },

  methods: {
    submit() {
      this.submitted = true;

      // Check if we are creating a new category and set the form category element
      if (this.new_category !== "") {
        this.form.programmes = this.new_category;
      }

      // stop here if form is invalid
      this.$v.$touch();

      if (this.$v.$invalid) {
        return;
      }
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
            window.location.replace("programs");
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
            window.location.replace("programs");
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
    onSearchCategory(value) {
      this.form.programmes = value;
    }
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
            )
            .then(response => {
              this.form.code = response.data.data.code;
              this.form.label = response.data.data.label;
              this.form.notes = response.data.data.notes;
              this.form.programmes = response.data.data.programmes;
              this.form.tmpl_badge = response.data.data.tmpl_badge;
              this.form.published = response.data.data.published;
              this.form.apply_online = response.data.data.apply_online;
              if (response.data.data.synthesis != null) {
                this.form.synthesis = response.data.data.synthesis.replace(/>\s+</g, "><");
              }
              if (response.data.data.tmpl_trombinoscope != null) {
                this.form.tmpl_trombinoscope = response.data.data.tmpl_trombinoscope.replace(
                  />\s+</g,
                  "><"
                );
              }
              this.dynamicComponent = true;
            })
            .catch(e => {
              console.log(e);
            });
        } else {
          this.dynamicComponent = true;
        }
      })
      .catch(e => {
        console.log(e);
      });
  }
};
</script>

<style scoped>
.is-invalid {
  border-color: #dc3545 !important;
}
.is-invalid:hover,
.is-invalid:focus {
  border-color: #dc3545 !important;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

input[type="radio"],
input[type="checkbox"] {
  width: 100% !important;
}

.toggle > b {
  display: block;
}

.toggle {
  position: relative;
  width: 40px;
  height: 20px;
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

.plus.w-inline-block {
  background-color: white;
  border-color: #cccccc;
}

.w-input,
.w-select {
  min-height: 55px;
  padding: 12px;
  background-color: white !important;
}

.bouton-sauvergarder-et-continuer {
  position: relative;
  padding: 10px 30px;
  float: right;
  border-radius: 4px;
  background-color: #1b1f3c;
  -webkit-transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
  transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
}

.last-container {
  padding-bottom: 30px;
}

.section-principale {
  padding-bottom: 0;
}

h2 {
  color: #1b1f3c;
}

.w-retour {
  margin-right: 5%;
  background: none !important;
  border: 1px solid #1b1f3c;
  color: #1b1f3c;
}
</style>
