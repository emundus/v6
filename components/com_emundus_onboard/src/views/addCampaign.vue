<template>
  <div class="section-principale">
    <notifications
            group="foo-velocity"
            position="bottom left"
            animation-type="velocity"
            :speed="500"
            :classes="'vue-notification-custom'"
    />
    <div class="w-container">
      <form id="campaign-form" @submit.prevent="submit">
        <div class="sous-container">
          <p class="required">{{RequiredFieldsIndicate}}</p>
          <div class="heading-form">
            <div class="icon-title"></div>
            <h2 class="heading">{{ Parameter }}</h2>
          </div>
          <div class="form-group campaign-label">
            <label for="campLabel">{{CampName}} *</label>
            <div class="input-can-translate">
                <input
                  id="campLabel"
                  type="text"
                  v-focus
                  class="form__input field-general w-input"
                  v-model="form.label.fr"
                  @keyup="enableTranslationTip"
                  required
                  :class="{ 'is-invalid': errors.label, 'mb-0': translate.label }"
                />
              <button class="translate-icon" :class="{'translate-icon-selected': translate.label}" type="button" @click="translate.label = !translate.label"></button>
            </div>
            <transition :name="'slide-down'" type="transition">
            <div class="inlineflex" v-if="translate.label" style="margin: 10px">
              <label class="translate-label">
                {{TranslateEnglish}}
              </label>
              <em class="fas fa-sort-down"></em>
            </div>
            </transition>
            <transition :name="'slide-down'" type="transition">
            <input v-if="translate.label"
                   type="text"
                   class="form__input field-general w-input"
                   v-model="form.label.en"
            />
            </transition>
          </div>
          <p v-if="errors.label" class="error col-md-12 mb-2">
            <span class="error">{{LabelRequired}}</span>
          </p>
          <div class="w-row">
            <div class="w-col w-col-6">
              <div class="w-form">
                <label for="campLabel">{{StartDate}} *</label>
                <datetime
                  :placeholder="StartDate"
                  type="datetime"
                  :input-id="'start_date'"
                  v-model="form.start_date"
                  :phrases="{ok: OK, cancel: Cancel}"
                ></datetime>
              </div>
            </div>
            <div class="w-col w-col-6">
              <div class="w-form">
                <label for="campLabel">{{EndDate}} *</label>
                <datetime
                  :placeholder="EndDate + ' *'"
                  type="datetime"
                  :input-id="'end_date'"
                  :min-datetime="form.start_date"
                  v-model="form.end_date"
                  :phrases="{ok: OK, cancel: Cancel}"
                ></datetime>
              </div>
            </div>
          </div>
          <div class="form-group campaign-label">
            <label for="campLabel">{{PickYear}} *</label>
            <autocomplete
                    :id="'year'"
                    @searched="onSearchYear"
                    :name="'2020-2021'"
                    :items="this.session"
                    :year="form.year"
            />
          </div>
          <div class="form-group d-flex">
            <div class="toggle">
              <input type="checkbox"
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
            <label for="published" class="ml-10px">{{ Publish }}</label>
          </div>
        </div>
        <div class="divider"></div>
        <div class="sous-container">
          <div class="heading-form">
            <div class="icon-title informations"></div>
            <h2 class="heading">{{ Information }}</h2>
          </div>
          <p class="paragraphe-sous-titre">
            {{ InformationDesc }}
          </p>
          <div class="form-group campaign-label">
            <label for="campResume" style="top: 5em">{{Resume}} *</label>
            <textarea
              type="textarea"
              rows="2"
              id="campResume"
              maxlength="200"
              class="form__input field-general w-input"
              placeholder=" "
              v-model="form.short_description"
              @keyup="checkMaxlength('campResume')"
              @focusout="removeBorderFocus('campResume')"
            />
          </div>
          <p v-if="errors.short_description" class="error col-md-12 mb-2">
            <span class="error">{{ResumeRequired}}</span>
          </p>
          <div class="form-group campaign-label">
            <label for="campDescription" style="top: 12em">{{Description}}</label>
            <textarea
              type="textarea"
              rows="4"
              id="campDescription"
              maxlength="400"
              class="form__input field-general w-input"
              placeholder=" "
              v-model="form.description"
              @keyup="checkMaxlength('campDescription')"
              @focusout="removeBorderFocus('campDescription')"
            />
          </div>
        </div>
        <div class="divider"></div>
        <div class="sous-container last-container">
          <div class="heading-form">
            <div class="icon-title programme"></div>
            <h2 class="heading">{{ Program }}</h2>
          </div>
          <p class="paragraphe-sous-titre">
            {{ ProgramDesc }}
          </p>
          <div class="form-group container-flexbox-choisir-ou-plus w-clearfix">
            <select
              class="dropdown-toggle w-select" style="margin-bottom: 0"
              id="select_prog"
              v-model="form.training"
              v-on:change="setCategory"
            >
              <option value="">{{ ChooseProg }}</option>
              <option
                v-for="(item, index) in this.programs"
                v-bind:value="item.code"
                v-bind:data-category="item.programmes"
                :key="index">
                {{ item.label }}
              </option>
            </select>
            <div v-if="coordinatorAccess != 0"
              @click="displayProgram"
              id="add-program"
              class="addCampProgEmail">
            </div>
          </div>

          <transition name="slide-fade">
            <div class="sous-container program-addCampaign" v-if="isHiddenProgram">
              <h2 class="heading">{{ AddProgram }}</h2>
              <p class="paragraphe-sous-titre">
                {{ AddProgramDesc }}
              </p>
              <div class="w-form">
                <div class="form-group prog-label">
                  <label for="prog_label" style="top: 5.7em">{{ProgName}} *</label>
                  <input
                    type="text"
                    id="prog_label"
                    class="form__input field-general w-input"
                    placeholder=" "
                    v-model="programForm.label"
                    @keyup="updateCode"
                    :class="{ 'is-invalid': errors.progLabel }"
                  />
                </div>
                <p v-if="errors.progLabel" class="error col-md-12 mb-2">
                  <span class="error">{{ProgLabelRequired}}</span>
                </p>

                <div class="form-group campaign-label">
                  <label style="top: 10.7em">{{ChooseCategory}}</label>
                  <autocomplete
                    @searched="onSearchCategory"
                    :items="this.categories"
                    :year="programForm.programmes"
                  />
                </div>

                <div class="form-group controls">
                  <editor :text="programForm.notes" v-model="programForm.notes" :placeholder="ProgramResume" :id="'program_campaign'"></editor>
                </div>

                <div class="form-group d-flex">
                  <div class="toggle">
                    <input type="checkbox"
                           true-value="1"
                           false-value="0"
                           class="check"
                           id="published"
                           name="published"
                           v-model="programForm.published"
                    />
                    <strong class="b switch"></strong>
                    <strong class="b track"></strong>
                  </div>
                  <label for="published" class="ml-10px">{{ Publish }}</label>
                </div>

                <div class="form-group d-flex">
                  <div class="toggle">
                    <input type="checkbox"
                           true-value="1"
                           false-value="0"
                           class="check"
                           id="apply"
                           name="apply"
                           v-model="programForm.apply_online"
                    />
                    <strong class="b switch"></strong>
                    <strong class="b track"></strong>
                  </div>
                  <label for="apply" class="ml-10px">{{ DepotDeDossier }}</label>
                </div>
              </div>
            </div>
          </transition>
        </div>
        <div class="divider"></div>
        <div class="section-sauvegarder-et-continuer">
          <div class="w-container">
            <div class="container-evaluation w-clearfix">
              <button
                type="button"
                class="bouton-sauvergarder-et-continuer w-button"
                @click="quit = 1; submit()">
                {{ Continuer }}
              </button>
              <button
                type="button"
                class="bouton-sauvergarder-et-continuer w-quitter w-button"
                @click="quit = 0; submit()">
                {{ Quitter }}
              </button>
              <button
                type="button"
                class="bouton-sauvergarder-et-continuer w-retour w-button"
                onclick="history.go(-1)">
                {{ Retour }}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="loading-form" v-if="submitted">
      <RingLoader :color="'#de6339'" />
    </div>
  </div>
</template>

<script>
import { required } from "vuelidate/lib/validators";
import axios from "axios";
import { Datetime } from "vue-datetime";
import { DateTime as LuxonDateTime, Settings } from "luxon";
import Editor from "../components/editor";
import Autocomplete from "../components/autocomplete";

const qs = require("qs");

export default {
  name: "addCampaign",

  components: {
    Datetime,
    Editor,
    Autocomplete
  },

  directives: { focus: {
      inserted: function (el) {
        el.focus()
      }
    }
  },

  quit: 1,

  props: {
    campaign: Number,
    actualLanguage: String,
    coordinatorAccess: Number,
  },

  data: () => ({
    isHiddenProgram: false,

    olderDate: "",

    programs: [],
    years: [],
    categories: [],

    new_category: "",

    session: [],

    form: {
      label: {
        fr: '',
        en: ''
      },
      start_date: LuxonDateTime.local().toISO(),
      end_date: "",
      short_description: "",
      description: "",
      training: "",
      year: "",
      published: 1
    },

    translate: {
      label: false,
    },

    enableTip: false,

    programForm: {
      code: "",
      label: "",
      notes: "",
      programmes: "",
      published: 1,
      apply_online: 1
    },

    year: {
      label: "",
      code: "",
      schoolyear: "",
      published: 1,
      profile_id: "",
      programmes: ""
    },

    errors: {
      label: false,
      progCode: false,
      progLabel: false,
      short_description: false,
    },

    Parameter: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_PARAMETER"),
    CampName: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_CAMPNAME"),
    StartDate: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_STARTDATE"),
    EndDate: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_ENDDATE"),
    Information: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION"),
    Resume: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_RESUME"),
    Description: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION"),
    Program: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM"),
    AddProgram: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDPROGRAM"),
    ChooseProg: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_CHOOSEPROG"),
    PickYear: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_PICKYEAR"),
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
    RequiredFieldsIndicate: Joomla.JText._("COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE"),
    ProgramResume: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_RESUME"),
    ProgLabelRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROG_REQUIRED_LABEL"),
    ResumeRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_CAMP_REQUIRED_RESUME"),
    OK: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
    Cancel: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL"),
    TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),

    submitted: false
  }),

  validations: {
    form: {
      label: { required },
      start_date: { required },
      training: { required },
      year: { required },
    }
  },

  created() {
    Settings.defaultLocale = this.actualLanguage;
    if (this.campaign !== "") {
      axios.get(
          `index.php?option=com_emundus_onboard&controller=campaign&task=getcampaignbyid&id=${this.campaign}`
        ).then(response => {
          this.form.label.fr = response.data.data.label.fr.value;
          this.form.label.en = response.data.data.label.en.value;
          this.form.published = response.data.data.campaign.published;
          this.form.description = response.data.data.campaign.description;
          this.form.short_description = response.data.data.campaign.short_description;
          this.form.start_date = response.data.data.campaign.start_date;
          this.form.end_date = response.data.data.campaign.end_date;
          this.form.training = response.data.data.campaign.training;
          this.form.year = response.data.data.campaign.year;
          this.form.start_date = this.changeDate(this.form.start_date);
          this.form.end_date = this.changeDate(this.form.end_date);
          if (this.form.end_date == "0000-00-00T00:00:00.000Z") {
            this.form.end_date = "";
          } else {
            this.olderDate = this.form.end_date;
          }
        }).catch(e => {
          console.log(e);
        });
    }
    axios.get("index.php?option=com_emundus_onboard&controller=program&task=getallprogram")
      .then(response => {
        this.programs = response.data.data;
        this.programs.sort((a, b) => a.id - b.id);
      }).catch(e => {
        console.log(e);
      });

    axios.get("index.php?option=com_emundus_onboard&controller=campaign&task=getyears")
      .then(response => {
        this.years = response.data.data;

        for (var i = 0; i < this.years.length; i++) {
          this.session.push(this.years[i].schoolyear);
        }
      }).catch(e => {
        console.log(e);
      });

    axios.get("index.php?option=com_emundus_onboard&controller=program&task=getprogramcategories")
      .then(response => {
        this.categories = response.data.data;
      }).catch(e => {
        console.log(e);
      });
  },

  methods: {
    setCategory(e) {
      this.year.programmes = e.target.options[e.target.options.selectedIndex].dataset.category;
    },

    updateCode() {
      if(this.programForm.label !== ''){
        this.programForm.code = this.programForm.label.toUpperCase().replace(/[^a-zA-Z0-9]/g,'_').substring(0,10) + '_00';
        this.programs.forEach((element, index) => {
          if(this.programForm.code == element.code){
            let newCode = parseInt(element.code.split('_')[1]) + 1;
            if(newCode > 10) {
              this.programForm.code = this.programForm.label.toUpperCase()  + '_' + newCode;
            } else {
              this.programForm.code = this.programForm.label.toUpperCase()  + '_0' + newCode;
            }
          }
        });
      } else {
        this.programForm.code = '';
      }
    },

    checkCode() {
      this.programs.forEach((element, index) => {
        if(this.programForm.code == element.code){
          let newCode = parseInt(element.code.split('_')[1]) + 1;
          if(newCode > 10) {
            this.programForm.code = this.programForm.label.toUpperCase()  + '_' + newCode;
          } else {
            this.programForm.code = this.programForm.label.toUpperCase()  + '_0' + newCode;
          }
        }
      });
    },

    submit() {
      this.errors = {
        label: false,
        progCode: false,
        progLabel: false,
        short_description: false,
      }

      if(this.form.label.fr == ""){
         window.scrollTo({ top: 0, behavior: 'smooth' });
         this.errors.label = true;
         return 0;
      }

      if (this.form.end_date == "") {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        document.getElementById('end_date').focus();
        return 0;
      }

      if (this.form.year == "") {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        document.getElementById('year').focus();
        return 0;
      }

      if (this.form.short_description == "") {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        document.getElementById('campResume').focus();
        this.errors.short_description = true;
        return 0;
      }

      if (this.form.training == "") {
        if(this.isHiddenProgram){
          if (this.programForm.label == "") {
            this.errors.progLabel = true;
            document.getElementById('prog_label').focus();
            return 0;
          } else if (this.programForm.code == "") {
            this.errors.progCode = true;
            document.getElementById('prog_code').focus();
            return 0;
          } else {
            this.form.training = this.programForm.code;
          }
        } else {
          document.getElementById('select_prog').focus();
          return 0;
        }
      }

      // stop here if form is invalid
      this.$v.$touch();
      if (this.$v.$invalid) {
        return;
      }

      // Set year object values
      this.year.label = this.form.label;
      this.year.code = this.form.training;
      this.year.schoolyear = this.form.year;
      this.year.published = this.form.published;
      this.year.profile_id = this.form.profile_id;

      this.submitted = true;

      if(!this.translate.label){
        this.form.label.en = this.form.label.fr;
      }

      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=program&task=createprogram",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({ body: this.programForm })
      }).then(() => {
          if (this.campaign !== "") {
            axios({
              method: "post",
              url: "index.php?option=com_emundus_onboard&controller=campaign&task=updatecampaign",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({ body: this.form, cid: this.campaign })
            }).then(response => {
                this.quitFunnelOrContinue(this.quit);
              }).catch(error => {
                console.log(error);
              });
          } else {
            axios({
              method: "post",
              url: "index.php?option=com_emundus_onboard&controller=campaign&task=createcampaign",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({ body: this.form })
            }).then(response => {
                this.campaign = response.data.data;
                this.quitFunnelOrContinue(this.quit);
              }).catch(error => {
                console.log(error);
              });
          }
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=campaign&task=createyear",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({ body: this.year })
          }).then(response => {})
            .catch(error => {
              console.log(error);
            });
        }).catch(error => {
          console.log(error);
        });
    },

    quitFunnelOrContinue(quit) {
      if (quit == 0) {
        history.go(-1);
      }
      else if (quit == 1) {
        this.redirectJRoute('index.php?option=com_emundus_onboard&view=form&layout=addnextcampaign&cid=' + this.campaign + '&index=0')
      }
    },

    redirectJRoute(link) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute",
        params: {
          link: link,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        window.location.href = window.location.pathname + response.data.data;
      });
    },

    changeDate(dbDate) {
      const regexDate = /\d{4}-\d{2}-\d{2}/gm;
      const regexHour = /\d{2}:\d{2}:\d{2}/gm;
      const str = dbDate;
      let m;
      var formatDate = "";

      while ((m = regexDate.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regexHour.lastIndex) {
          regexHour.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach((yy_MM_dd, groupIndex) => {
          formatDate = `${yy_MM_dd}T`;
        });
      }

      while ((m = regexHour.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === regexHour.lastIndex) {
          regexHour.lastIndex++;
        }

        // The result can be accessed through the `m`-variable.
        m.forEach((HH_mm, groupIndex) => {
          formatDate = formatDate + `${HH_mm}.000Z`;
        });
      }
      return formatDate;
    },

    onSearchYear(value) {
      this.form.year = value;
    },
    onSearchCategory(value) {
      this.programForm.programmes = value;
    },

    changeEndDate() {
      if (this.form.end_date == "") {
        this.form.end_date = this.olderDate;
      } else {
        this.olderDate = this.form.end_date;
        this.form.end_date = "";
      }
    },

    displayProgram() {
      this.isHiddenProgram ? document.getElementById('add-program').style = 'transform: rotate(0)' : document.getElementById('add-program').style = 'transform: rotate(135deg)';
      this.form.training = "";
      this.isHiddenProgram ? document.getElementById('select_prog').removeAttribute('disabled') : document.getElementById('select_prog').setAttribute('disabled', 'disabled');
      this.isHiddenProgram = !this.isHiddenProgram;
    },

    enableTranslationTip() {
      if(!this.enableTip){
        this.enableTip = true;
        this.tip();
      }
    },

    checkMaxlength(id) {
      var maxLength = document.getElementById(id).getAttribute('maxlength');
      if(maxLength == this.form.short_description.length) {
        document.getElementById(id).style.borderColor = 'red';
      } else {
        document.getElementById(id).style.borderColor = '#3898ec';
      }
    },

    removeBorderFocus(id){
      document.getElementById(id).style.borderColor = '#cccccc';
    },


    /**
     * ** Methods for notify
     */
    tip(){
      this.show(
              "foo-velocity",
              Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATETIP") + '<em class="translate-icon"></em>',
              Joomla.JText._("COM_EMUNDUS_ONBOARD_TIP"),
      );
    },

    show(group, text = "", title = "Information") {
      this.$notify({
        group,
        title: `${title}`,
        text,
        duration: 10000
      });
    },
    clean(group) {
      this.$notify({ group, clean: true });
    },
  },
};
</script>

<style scoped>
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
  margin: 0 10px 0 -20px;
  line-height: normal;
  width: 4% !important;
  cursor: pointer;
}

.checkbox-label {
  color: #696969;
  font-size: 12px;
  margin-top: 0 !important;
}

.w-form-label {
  display: inline-block;
  cursor: pointer;
  font-weight: normal;
  margin-bottom: 0;
  margin-top: 5.5%;
}

.w-checkbox {
  display: flex;
  margin-bottom: 0;
  align-items: center;
  padding-left: 20px;
}

.w-select,
.plus.w-inline-block {
  background-color: white;
  border-color: #cccccc;
}

.w-input,
.w-select {
  font-weight: 300;
  min-height: 50px;
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

.w-quitter {
  margin-right: 5%;
  background: none !important;
  border: 1px solid #1b1f3c;
  color: #1b1f3c;
}

.program-addCampaign {
  padding: 2%;
  margin-bottom: 5%;
}

  .d-flex{
    display: flex;
    align-items: center;
  }

  .d-flex label{
    margin-bottom: 0;
    margin-right: 10px;
  }

  #add-program{
    width: 32px;
    height: 30px;
    cursor: pointer;
    transition: transform 0.5s ease-in-out;
  }

  .translate-icon{
    height: auto;
    position: absolute;
    right: 1em;
    margin-bottom: 10px;
  }

  .translate-icon-selected{
    margin-bottom: 0;
  }

  .w-row{
    margin-bottom: 1em;
  }

  .inlineflex{
    display: flex;
    align-items: center;
  }

</style>
