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
                  v-model="form.label[actualLanguage]"
                  @keyup="enableTranslationTip"
                  required
                  :class="{ 'is-invalid': errors.label, 'mb-0': translate.label }"
                />
              <button class="translate-icon" :class="{'translate-icon-selected': translate.label}" v-if="manyLanguages !== '0'" type="button" @click="enableLabelTranslation"></button>
            </div>
            <translation :label="form.label" :actualLanguage="actualLanguage" v-if="translate.label"></translation>
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
                  :min-datetime="minDate"
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
                    :items="this.session"
                    :year="form.year"
                    :name="'2020 - 2021'"
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
          <div class="form-group d-flex">
            <div class="toggle">
              <input type="checkbox"
                     true-value="1"
                     false-value="0"
                     class="check"
                     id="limit"
                     name="limit"
                     v-model="form.is_limited"
              />
              <strong class="b switch"></strong>
              <strong class="b track"></strong>
            </div>
            <label for="limit" class="ml-10px">{{ FilesLimit }}</label>
          </div>
          <transition name="'slide-down'">
            <div v-if="form.is_limited == 1">
              <div class="form-group campaign-label">
                <label for="campLabel">{{FilesNumberLimit}} *</label>
                <input type="number"
                       class="form__input field-general w-input"
                       v-model="form.limit"
                       :class="{ 'is-invalid': errors.limit_files_number }"
                />
              </div>
              <p v-if="errors.limit_files_number" class="error">
                <span class="error">{{FilesLimitRequired}}</span>
              </p>
              <div class="form-group campaign-label">
                <label for="campLabel">{{StatusLimit}} *</label>
                <div class="users-block" :class="{ 'is-invalid': errors.limit_status}">
                  <div v-for="(statu, index) in status" :key="index" class="user-item">
                    <input type="checkbox" class="form-check-input bigbox" v-model="form.limit_status[statu.step]">
                    <div class="ml-10px">
                      <p>{{statu.value}}</p>
                    </div>
                  </div>
                </div>
                <p v-if="errors.limit_status" class="error">
                  <span class="error">{{StatusLimitRequired}}</span>
                </p>
              </div>
            </div>
          </transition>
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
          <!--<div class="form-group campaign-label">
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
          </div>-->
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
              :disabled="this.programs.length <= 0"
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
                  <editor :text="programForm.notes" v-model="programForm.notes" :enable_variables="false" :placeholder="ProgramResume" :id="'program_campaign'"></editor>
                </div>

                <div class="form-group d-flex">
                  <div class="toggle">
                    <input type="checkbox"
                           true-value="1"
                           false-value="0"
                           class="check"
                           id="prog_published"
                           name="prog_published"
                           v-model="programForm.published"
                    />
                    <strong class="b switch"></strong>
                    <strong class="b track"></strong>
                  </div>
                  <label for="prog_published" class="ml-10px">{{ Publish }}</label>
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
                  <label for="apply" class="ml-10px mb-0">{{ DepotDeDossier }}</label>
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
                class="bouton-sauvergarder-et-continuer"
                @click="quit = 1; submit()">
                {{ Continuer }}
              </button>
              <button
                type="button"
                class="bouton-sauvergarder-et-continuer w-quitter"
                @click="quit = 0; submit()">
                {{ Quitter }}
              </button>
              <button
                type="button"
                class="bouton-sauvergarder-et-continuer w-retour"
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
    <tasks></tasks>
  </div>
</template>

<script>
import axios from "axios";
import { Datetime } from "vue-datetime";
import { DateTime as LuxonDateTime, Settings } from "luxon";
import Editor from "../components/editor";
import Autocomplete from "../components/autocomplete";
import Translation from "../components/translation"
import Tasks from "@/views/tasks";

const qs = require("qs");

export default {
  name: "addCampaign",

  components: {
    Tasks,
    Datetime,
    Editor,
    Autocomplete,
    Translation
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
    manyLanguages: Number,
  },

  data: () => ({
    isHiddenProgram: false,

    olderDate: "",
    minDate: "",

    programs: [],
    years: [],
    categories: [],
    status: [],

    new_category: "",

    session: [],

    form: {
      label: {
        fr: '',
        en: ''
      },
      start_date: "",
      end_date: "",
      short_description: "",
      description: "",
      training: "",
      year: "",
      published: 1,
      is_limited: 0,
      limit: 50,
      limit_status: [],
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
      limit_files_number: false,
      limit_status: false
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
    FilesLimit: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILES_LIMIT"),
    FilesNumberLimit: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILES_LIMIT_NUMBER"),
    StatusLimit: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILES_LIMIT_STATUS"),
    StatusLimitRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRIGGERSTATUS_REQUIRED"),
    FilesLimitRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILES_LIMIT_REQUIRED"),

    submitted: false
  }),

  created() {
    // Configure datetime
    Settings.defaultLocale = this.actualLanguage;
    //

    let now = new Date();
    this.form.start_date = LuxonDateTime.local(now.getFullYear(),now.getMonth() + 1,now.getDate(),0,0,0).toISO();

    //Check if we add or edit a campaign
    if (this.campaign !== "") {
      axios.get(
          `index.php?option=com_emundus_onboard&controller=campaign&task=getcampaignbyid&id=${this.campaign}`
        ).then(response => {
          if(response.data.data.label.fr == null && response.data.data.label.en == null){
            this.form.label.fr = response.data.data.campaign.label;
            this.form.label.en = response.data.data.campaign.label;
          } else {
            this.form.label.fr = response.data.data.label.fr.value;
            this.form.label.en = response.data.data.label.en.value;
          }
          this.form.published = response.data.data.campaign.published;
          this.form.description = response.data.data.campaign.description;
          this.form.short_description = response.data.data.campaign.short_description;
          this.form.start_date = response.data.data.campaign.start_date;
          this.form.end_date = response.data.data.campaign.end_date;
          this.form.training = response.data.data.campaign.training;
          this.form.year = response.data.data.campaign.year;
          this.form.is_limited = response.data.data.campaign.is_limited;
          this.form.limit = response.data.data.campaign.limit;
          this.form.start_date = LuxonDateTime.fromSQL(this.form.start_date);
          this.form.end_date = LuxonDateTime.fromSQL(this.form.end_date);
          if(typeof response.data.data.campaign.status != 'undefined') {
            Object.values(response.data.data.campaign.status).forEach((statu) => {
              this.form.limit_status[parseInt(statu.limit_status)] = true;
            });
          }
          if (this.form.end_date == "0000-00-00T00:00:00.000Z") {
            this.form.end_date = "";
          } else {
            this.olderDate = this.form.end_date;
          }
        }).catch(e => {
          console.log(e);
        });
    }
    //
    axios.get("index.php?option=com_emundus_onboard&controller=program&task=getallprogram")
      .then(response => {
        this.programs = response.data.data;
        if(Object.keys(this.programs).length !== 0) {
          this.programs.sort((a, b) => a.id - b.id);
        }
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
        this.categories  = response.data.data;
      }).catch(e => {
        console.log(e);
      });
    this.getStatus();
  },

  methods: {
    setCategory(e) {
      this.year.programmes = e.target.options[e.target.options.selectedIndex].dataset.category;
    },

    updateCode() {
      if(this.programForm.label !== ''){
        this.programForm.code = this.programForm.label.toUpperCase().replace(/[^a-zA-Z0-9]/g,'_').substring(0,10) + '_00';
        if(Object.keys(this.programs).length !== 0) {
          this.programs.forEach((element, index) => {
            if (this.programForm.code == element.code) {
              let newCode = parseInt(element.code.split('_')[1]) + 1;
              if (newCode > 10) {
                this.programForm.code = this.programForm.label.toUpperCase() + '_' + newCode;
              } else {
                this.programForm.code = this.programForm.label.toUpperCase() + '_0' + newCode;
              }
            }
          });
        }
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

    enableLabelTranslation(){
      this.translate.label = !this.translate.label
      if(this.translate.label){
        setTimeout(() => {
          document.getElementById('label_en').focus();
        },100);
      }
    },

    getStatus() {
      axios.get("index.php?option=com_emundus_onboard&controller=settings&task=getstatus")
              .then(response => {
                this.status = response.data.data;
              });
    },

    submit() {
      // Checking errors
      this.errors = {
        label: false,
        progCode: false,
        progLabel: false,
        short_description: false,
        limit_files_number: false,
        limit_status: false
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

      if (this.form.is_limited == 1){
        let least_one_status = this.form.limit_status.every((value) => {
          return value === false;
        });
        if(this.form.limit == ''){
          window.scrollTo({ top: 0, behavior: 'smooth' });
          this.errors.limit_files_number = true;
          return 0;
        }
        if(this.form.limit_status.length == 0 || least_one_status) {
          window.scrollTo({ top: 0, behavior: 'smooth' });
          this.errors.limit_status = true;
          return 0;
        }
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
      //

      // Set year object values
      this.year.label = this.form.label;
      this.year.code = this.form.training;
      this.year.schoolyear = this.form.year;
      this.year.published = this.form.published;
      this.year.profile_id = this.form.profile_id;
      //

      if(this.form.label.en == ""){
        this.form.label.en = this.form.label.fr;
      }

      this.submitted = true;

      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=program&task=createprogram",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({ body: this.programForm })
      }).then(() => {
          let newsession = false;

          if (this.campaign !== "") {
            if(typeof this.form.start_date == 'object'){
              this.form.start_date = LuxonDateTime.fromISO(this.form.start_date).toISO();
            }
            if(typeof this.form.end_date == 'object'){
              this.form.end_date = LuxonDateTime.fromISO(this.form.end_date).toISO();
            }
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
            if(typeof this.form.start_date == 'object'){
              this.form.start_date = LuxonDateTime.fromISO(this.form.start_date).toISO();
            }
            if(typeof this.form.end_date == 'object'){
              this.form.end_date = LuxonDateTime.fromISO(this.form.end_date).toISO();
            }
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
        this.years.forEach((elt) => {
          if(elt.schoolyear == this.year.schoolyear){
            newsession = true;
          }
        });
          if(newsession) {
            axios({
              method: "post",
              url: "index.php?option=com_emundus_onboard&controller=campaign&task=createyear",
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({body: this.year})
            }).then(response => {})
                .catch(error => {
                  console.log(error);
                });
          }
        }).catch(error => {
          console.log(error);
        });
    },

    quitFunnelOrContinue(quit) {
      if (quit == 0) {
        window.location.href = '/configuration-campaigns'
      } else if (quit == 1) {
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

  watch: {
    'form.start_date': function (val, oldVal) {
      this.minDate = LuxonDateTime.fromISO(val).plus({ days: 1 });
      if(this.form.end_date == "") {
        this.form.end_date = LuxonDateTime.fromISO(val).plus({days: 1});
      }
    }
  }
};
</script>

<style scoped>
.w-row{
  margin-bottom: 1em;
}
.addCampProgEmail{
  width: 32px;
  height: 30px;
}
</style>
