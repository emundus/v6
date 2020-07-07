<template>
  <div class="section-principale">
    <div class="w-container">
      <form id="program-form" @submit.prevent="submit">
        <div class="sous-container">
          <div class="heading-form">
            <div class="icon-title informations"></div>
            <h2 class="heading">{{ Informations }}</h2>
          </div>
          <p class="paragraphe-sous-titre">
            {{ InformationsDesc }}
          </p>
          <div class="w-form">
            <div class="form-group">
              <label>{{emailName}} *</label>
              <input
                type="text"
                class="form__input field-general w-input"
                v-model="form.subject"
              />
            </div>

            <div class="form-group">
              <label>{{receiverName}} *</label>
              <input
                type="text"
                class="form__input field-general w-input"
                v-model="form.name"
              />
            </div>

            <div class="form-group">
              <label>{{emailAddress}} *</label>
              <input
                type="text"
                class="form__input field-general w-input"
                v-model="form.emailfrom"
              />
            </div>

            <div class="form-group controls forms-emails-editor">
              <editor :text="form.message" v-if="dynamicComponent" v-model="form.message" :placeholder="EmailResume"></editor>
            </div>
          </div>
        </div>

        <div class="divider"></div>
        <div class="sous-container last-container">
          <div class="heading-form">
            <div class="icon-title"></div>
            <h2 class="heading">{{ Parameter }}</h2>
          </div>
          <p class="paragraphe-sous-titre">
            {{ ParameterDesc }}
          </p>
          <div class="form-group container-flexbox-choisir-ou-plus w-clearfix">
            <select class="dropdown-toggle w-select" v-model="form.type">
              <option disabled value="">{{ emailType }}</option>
              <option
                v-for="(item, index) in this.types"
                v-bind:value="item"
                :key="index"
                :selected="form.type"
                >{{ emailtype[langue][item - 1] }}</option
              >
            </select>
          </div>

          <div class="form-group container-flexbox-choisir-ou-plus w-clearfix">
            <select class="dropdown-toggle w-select" v-model="form.category" :disabled="isHiddenCategory">
              <option disabled value="">{{ emailCategory }}</option>
              <option
                v-for="(item, index) in this.categories"
                v-bind:value="item"
                :key="index"
                :selected="form.category"
                >{{ item }}</option
              >
            </select>
            <div
                    @click="displayCategory"
                    id="add-category"
                    class="addCampProgEmail"
            >
            </div>
          </div>
          <transition name="slide-fade">
            <label v-if="isHiddenCategory">{{CategoryName}} *</label>
            <input v-if="isHiddenCategory"
              type="text"
              class="form__input field-general w-input"
              v-model="new_category"
            />
          </transition>
        </div>

        <div class="section-sauvegarder-et-continuer">
          <div class="w-container">
            <div class="container-evaluation w-clearfix">
              <button type="submit" class="bouton-sauvergarder-et-continuer w-button">
                {{ continuer }}
              </button>
              <button
                type="button"
                class="bouton-sauvergarder-et-continuer w-retour w-button"
                onclick="history.go(-1)"
              >
                {{ retour }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { required } from "vuelidate/lib/validators";
import axios from "axios";
import Editor from "../components/editor";

const qs = require("qs");

export default {
  name: "addEmail",

  components: {
    Editor
  },

  props: {
    email: Number,
    actualLanguage: String
  },

  data: () => ({
    langue: 0,

    dynamicComponent: false,
    isHiddenCategory: false,

    Parameter: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_PARAMETER"),
    Informations: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION"),
    emailType: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_CHOOSETYPE"),
    emailCategory: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSECATEGORY"),
    retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
    continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
    emailName: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_NAME"),
    receiverName: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_RECEIVER"),
    emailAddress: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_ADDRESS"),
    EmailResume: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_RESUME"),
    CategoryName: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDEMAIL_CATEGORY"),

    new_category: "",

    types: [],
    categories: [],

    emailtype: [
      ['Système', 'Modèle'],
      ['System', 'Model']
    ],

    form: {
      lbl: "",
      subject: "",
      name: "",
      emailfrom: "",
      message: "",
      type: "",
      category: "",
      published: 1
    },
    submitted: false
  }),

  validations: {
    form: {
      subject: { required },
      message: { required },
      type: { required },
      category: { required }
    }
  },

  methods: {
    submit() {
      this.submitted = true;

      this.form.lbl = this.form.subject;

      if (this.new_category !== "") {
        this.form.category = this.new_category;
      }

      // stop here if form is invalid
      this.$v.$touch();

      if (this.$v.$invalid) {
        return;
      }
      if (this.email !== "") {
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=email&task=updateemail",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({ body: this.form, code: this.email })
        })
          .then(response => {
            window.location.replace("emails");
          })
          .catch(error => {
            console.log(error);
          });
      } else {
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=email&task=createemail",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({ body: this.form })
        })
          .then(response => {
            window.location.replace("emails");
          })
          .catch(error => {
            console.log(error);
          });
      }
    },

    displayCategory() {
      this.isHiddenCategory ? document.getElementById('add-category').style = 'transform: rotate(0)' : document.getElementById('add-category').style = 'transform: rotate(135deg)';
      this.isHiddenCategory = !this.isHiddenCategory;
    }
  },

  created() {
    axios
      .get("index.php?option=com_emundus_onboard&controller=email&task=getemailtypes")
      .then(response => {
        this.types = response.data.data;
        axios
          .get("index.php?option=com_emundus_onboard&controller=email&task=getemailcategories")
          .then(rep => {
            this.categories = rep.data.data;
            if (this.email !== "") {
              axios
                .get(
                  `index.php?option=com_emundus_onboard&controller=email&task=getemailbyid&id=${this.email}`
                )
                .then(resp => {
                  this.form.subject = resp.data.data.subject;
                  this.form.name = resp.data.data.name;
                  this.form.emailfrom = resp.data.data.emailfrom;
                  this.form.message = resp.data.data.message;
                  this.form.type = resp.data.data.type;
                  this.form.category = resp.data.data.category;
                  this.form.published = resp.data.data.published;
                  this.dynamicComponent = true;
                })
                .catch(e => {
                  console.log(e);
                });
            } else {
              this.dynamicComponent = true;
            }
          });
      })
      .catch(e => {
        console.log(e);
      });
  },

  mounted() {
    if (this.actualLanguage == "en") {
      this.langue = 1;
    }
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

.container-evaluation {
  position: relative;
  width: 85%;
  margin-right: auto;
  margin-left: auto;
}

h2 {
  color: #1b1f3c !important;
}

.w-checkbox-input {
  float: left;
  margin-bottom: 0px;
  margin-left: -20px;
  margin-right: 0px;
  margin-top: 4px;
  line-height: normal;
  width: 4% !important;
}

.checkbox-label {
  color: #696969;
  font-size: 12px;
}

.w-form-label {
  display: inline-block;
  cursor: pointer;
  font-weight: normal;
  margin-bottom: 0;
  margin-top: 5.3%;
}

.w-checkbox {
  display: block;
  margin-bottom: 5px;
  padding-left: 20px;
}

.w-select,
.plus.w-inline-block {
  background-color: white;
  border-color: #cccccc;
}

.w-input,
.w-select {
  min-height: 55px;
  padding: 12px;
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

#add-category{
  width: 32px;
  height: 30px;
  cursor: pointer;
  transition: transform 0.5s ease-in-out;
}
</style>
