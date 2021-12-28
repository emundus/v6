<template>
  <div class="section-principale">
    <notifications
        group="foo-velocity"
        position="bottom left"
        animation-type="velocity"
        :speed="500"
        :classes="'vue-notification-custom'"
    />
    <div class="w-container general-information">
      <div class="section-sub-menu sub-form" v-if="campaignId == ''">
        <div class="container-2 w-container" style="max-width: unset">
          <div class="d-flex">
            <img src="/images/emundus/menus/megaphone.svg" srcset="/images/emundus/menus/megaphone.svg" class="tchooz-icon-title" alt="megaphone">
            <h1 class="tchooz-section-titles">{{translations.AddCampaign}}</h1>
          </div>
        </div>
      </div>
      <form id="campaign-form" @submit.prevent="submit">
        <div class="sous-container">
          <p class="required mb-1">{{translations.RequiredFieldsIndicate}}</p>
          <div class="form-group campaign-label">
            <label for="campLabel">{{translations.CampName}} <span class="em-red-500-color">*</span></label>
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
            <span class="error">{{translations.LabelRequired}}</span>
          </p>
          <div class="d-flex justify-content-between">
            <div class="w-col col-md-5">
              <div class="w-form">
                <label for="startDate">{{translations.StartDate}} <span class="em-red-500-color">*</span></label>
                <datetime
                    id="startDate"
                    :placeholder="translations.StartDate"
                    type="datetime"
                    :input-id="'start_date'"
                    v-model="form.start_date"
                    :phrases="{ok: translations.OK, cancel: translations.Cancel}"
                ></datetime>
              </div>
            </div>
            <div class="w-col col-md-5">
              <div class="w-form">
                <label for="endDate">{{translations.EndDate}} <span class="em-red-500-color">*</span></label>
                <datetime
                    id="endDate"
                    :placeholder="translations.EndDate + ' *'"
                    type="datetime"
                    :input-id="'end_date'"
                    :min-datetime="minDate"
                    v-model="form.end_date"
                    :phrases="{ok: translations.OK, cancel: translations.Cancel}"
                ></datetime>
              </div>
            </div>
          </div>
          <div class="form-group campaign-label">
            <label for="year">{{translations.PickYear}} <span class="em-red-500-color">*</span></label>
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
            <span for="published" class="ml-10px">{{ translations.Publish }}</span>
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
            <span for="limit" class="ml-10px">{{ translations.FilesLimit }}</span>
          </div>
          <transition name="'slide-down'">
            <div v-if="form.is_limited == 1">
              <div class="form-group campaign-label">
                <label for="campLabel">{{translations.FilesNumberLimit}} <span class="em-red-500-color">*</span></label>
                <input type="number"
                       class="form__input field-general w-input"
                       v-model="form.limit"
                       :class="{ 'is-invalid': errors.limit_files_number }"
                />
              </div>
              <p v-if="errors.limit_files_number" class="error">
                <span class="error">{{translations.FilesLimitRequired}}</span>
              </p>
              <div class="form-group campaign-label">
                <label for="campLabel">{{translations.StatusLimit}} <span class="em-red-500-color">*</span></label>
                <div class="users-block" :class="{ 'is-invalid': errors.limit_status}">
                  <div v-for="(statu, index) in status" :key="index" class="user-item">
                    <input type="checkbox" class="form-check-input bigbox" v-model="form.limit_status[statu.step]">
                    <div class="ml-10px">
                      <p>{{statu.value}}</p>
                    </div>
                  </div>
                </div>
                <p v-if="errors.limit_status" class="error">
                  <span class="error">{{translations.StatusLimitRequired}}</span>
                </p>
              </div>
            </div>
          </transition>
        </div>

        <div class="divider"></div>

        <div class="sous-container">
          <div class="heading-form">
            <h2 class="heading">{{ translations.Information }}</h2>
          </div>
          <div class="form-group campaign-label">
            <label style="top: 5em">{{translations.Resume}} <span class="em-red-500-color">*</span></label>
            <textarea
                type="textarea"
                rows="2"
                id="campResume"
                maxlength="500"
                class="form__input field-general w-input"
                placeholder=" "
                v-model="form.short_description"
                @keyup="checkMaxlength('campResume')"
                @focusout="removeBorderFocus('campResume')"
            />
          </div>
          <p v-if="errors.short_description" class="error col-md-12 mb-2">
            <span class="error">{{translations.ResumeRequired}}</span>
          </p>
          <div class="form-group controls" v-if="form.description != null">
            <editor :height="'30em'" :text="form.description" v-model="form.description" :enable_variables="false" :placeholder="translations.Description" :id="'campaign_description'" :key="editorKey"></editor>
          </div>
        </div>

        <div class="divider"></div>

        <div class="sous-container last-container">
          <div class="heading-form">
            <h2 class="heading">{{ translations.Program }}</h2>
          </div>
          <p>{{translations.ProgramDesc}}<span class="em-red-500-color">*</span></p>
          <div class="form-group container-flexbox-choisir-ou-plus w-clearfix mt-1">
            <select
                class="dropdown-toggle w-select" style="margin-bottom: 0"
                id="select_prog"
                v-model="form.training"
                v-on:change="setCategory"
                :disabled="this.programs.length <= 0"
            >
              <option value="">{{ translations.ChooseProg }}</option>
              <option
                  v-for="(item, index) in this.programs"
                  v-bind:value="item.code"
                  v-bind:data-category="item.programmes"
                  :key="index">
                {{ item.label }}
              </option>
            </select>
            <button v-if="coordinatorAccess != 0" :title="translations.AddProgram" type="button" @click="displayProgram" class="buttonAddDoc" id="add-program">
              <em class="fas fa-plus"></em>
            </button>
          </div>

          <transition name="slide-fade">
            <div class="program-addCampaign" v-if="isHiddenProgram">
              <div class="w-form">
                <div class="form-group prog-label">
                  <label for="prog_label" style="top: 5.7em">{{translations.ProgName}} <span class="em-red-500-color">*</span></label>
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
                  <span class="error">{{translations.ProgLabelRequired}}</span>
                </p>
              </div>
            </div>
          </transition>
        </div>

        <div class="divider"></div>

        <div class="section-sauvegarder-et-continuer">
          <div class="w-container btns-sauvegarder-et-continuer">
            <div class="container-evaluation d-flex justify-content-between">
              <button
                  type="button"
                  class="bouton-sauvergarder-et-continuer w-retour"
                  onclick="history.back()">
                {{ translations.Retour }}
              </button>
              <div class="d-flex">
                <button
                    type="button"
                    class="bouton-sauvergarder-et-continuer bouton-sauvergarder-et-continuer-green"
                    @click="quit = 1; submit()">
                  {{ translations.Continuer }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="loading-form" v-if="submitted">
      <RingLoader :color="'#12DB42'" />
    </div>
  </div>
</template>

<script>
import axios from "axios";
import { Datetime } from "vue-datetime";
import { DateTime as LuxonDateTime, Settings } from "luxon";
import Editor from "../components/editor";
import Autocomplete from "../components/autocomplete";
import Translation from "../components/translation"
import { global } from "../store/global";

const qs = require("qs");

export default {
  name: "addCampaign",

  components: {
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
  },

  data: () => ({
    // props
    campaignId: 0,
    actualLanguage: "",
    coordinatorAccess: 0,
    manyLanguages: 0,

    isHiddenProgram: false,

    // Date picker rules
    olderDate: "",
    minDate: "",
    //

    programs: [],
    years: [],
    status: [],
    languages: [],

    session: [],
    old_training: "",
    old_program_form: "",
    editorKey: 0,

    form: {
      label: {},
      start_date: "",
      end_date: "",
      short_description: "",
      description: null,
      training: "",
      year: "",
      published: 1,
      is_limited: 0,
      profile_id: 9,
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

    translations: {
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
      AddCampaign: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN"),
      ProgramDesc: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_INTRO_DESC"),
    },

    submitted: false
  }),

  created() {
    if (this.$props.campaign == "") {
      // Get datas that we need with store
      this.campaignId = global.getters.datas.campaign.value;
    } else {
      this.campaignId = this.$props.campaign;
    } 

    this.actualLanguage = global.getters.actualLanguage;
    this.manyLanguages = global.getters.manyLanguages;
    this.coordinatorAccess = global.getters.coordinatorAccess;
    //

    // Configure datetime
    Settings.defaultLocale = this.actualLanguage;
    //

    let now = new Date();
    this.form.start_date = LuxonDateTime.local(now.getFullYear(),now.getMonth() + 1,now.getDate(),0,0,0).toISO();
    this.getLanguages();
    this.getCampaignById();
    this.getAllPrograms();
    this.getYears();
    this.getStatus();
  },

  methods: {
    getCampaignById() {
      // Check if we add or edit a campaign
      if (typeof this.campaignId !== 'undefined' && this.campaignId !== "") {
        axios.get(
            `index.php?option=com_emundus_onboard&controller=campaign&task=getcampaignbyid&id=${this.campaignId}`
        ).then(response => {
          let label = response.data.data.campaign.label;

          this.form = response.data.data.campaign;
          this.$emit('getInformations',this.form);
          this.programForm = response.data.data.program;

          // Check label translations
          this.form.label = response.data.data.label
          this.languages.forEach((language) => {
            if(this.form.label[language.sef] === '' || this.form.label[language.sef] == null) {
              this.form.label[language.sef] = label;
            }
          });
          //

          // Convert date
          this.form.start_date = LuxonDateTime.fromSQL(this.form.start_date);
          this.form.end_date = LuxonDateTime.fromSQL(this.form.end_date);
          if (this.form.end_date == "0000-00-00T00:00:00.000Z") {
            this.form.end_date = "";
          } else {
            this.olderDate = this.form.end_date;
          }
          //

          if(typeof response.data.data.campaign.status != 'undefined') {
            this.form.limit_status = [];
            this.form.is_limited = 1;
            Object.values(response.data.data.campaign.status).forEach((statu) => {
              this.form.limit_status[parseInt(statu.limit_status)] = true;
            });
          } else {
            this.form.limit_status = [];
          }
        }).catch(e => {
          console.log(e);
        });
      }
    },
    getAllPrograms() {
      axios.get("index.php?option=com_emundus_onboard&controller=program&task=getallprogram")
          .then(response => {
            this.programs = response.data.data;
            if(Object.keys(this.programs).length !== 0) {
              this.programs.sort((a, b) => a.id - b.id);
            }
          }).catch(e => {
        console.log(e);
      });
    },
    getYears() {
      axios.get("index.php?option=com_emundus_onboard&controller=campaign&task=getyears")
        .then(response => {
          this.years = response.data.data;

          this.years.forEach((year) => {
            this.session.push(year.schoolyear);
          });

        }).catch(e => {
          console.log(e);
        });
    },
    getLanguages() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=settings&task=getactivelanguages"
      }).then(response => {
        this.languages = response.data.data;
      });
    },

    setCategory(e) {
      this.year.programmes = e.target.options[e.target.options.selectedIndex].dataset.category;
      this.programForm = this.programs.find(program => program.code == this.form.training);
    },
    updateCode() {
      if(this.programForm.label !== ''){
        this.programForm.code = this.programForm.label.toUpperCase().replace(/[^a-zA-Z0-9]/g,'_').substring(0,10) + '_00';
        if(Object.keys(this.programs).length !== 0) {
          this.programs.forEach((element) => {
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

    createCampaignWithExistingProgram(form_data){
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=campaign&task=createcampaign",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({body: form_data})
      }).then(response => {
        this.campaignId = response.data.data;
        this.quitFunnelOrContinue(this.quit);
      }).catch(error => {
        console.log(error);
      });
    },

    createCampainWithNoExistingProgram(programForm){
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=program&task=createprogram",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({body: programForm})
      }).then(() => {
        this.form.training = programForm.code;
        this.form.start_date = LuxonDateTime.fromISO(this.form.start_date).toISO();
        this.form.end_date = LuxonDateTime.fromISO(this.form.end_date).toISO();
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=campaign&task=createcampaign",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({body: this.form})
        }).then(response => {
          this.campaignId = response.data.data;
          this.quitFunnelOrContinue(this.quit);
        }).catch(error => {
          console.log(error);
        });
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
      if(this.form.label.fr == "" && this.form.label.en == ""){
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

      if (this.campaignId !== "") {
        let task = 'createprogram';
        let params = {body: this.programForm}

        if(this.form.training != ""){
          task = 'updateprogram';
          params = { body: this.programForm, id: this.form.progid };
        }
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=program&task=" + task,
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify(params)
        }).then(() => {
          this.form.training = this.programForm.code;
          this.form.start_date = LuxonDateTime.fromISO(this.form.start_date).toISO();
          this.form.end_date = LuxonDateTime.fromISO(this.form.end_date).toISO();

          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=campaign&task=updatecampaign",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({ body: this.form, cid: this.campaignId })
          }).then(() => {
            this.$emit('nextSection')
          }).catch(error => {
            console.log(error);
          });
        }).catch(error => {
          console.log(error);
        });
      } else {
        // get program code if there is training value
        if(this.form.training !=="")  {
          this.programForm = this.programs.find(program => program.code == this.form.training);
          this.form.training = this.programForm.code;
          this.form.start_date = LuxonDateTime.fromISO(this.form.start_date).toISO();
          this.form.end_date = LuxonDateTime.fromISO(this.form.end_date).toISO();
          this.createCampaignWithExistingProgram(this.form);
        } else {
          this.createCampainWithNoExistingProgram(this.programForm);
        }
      }
    },

    quitFunnelOrContinue(quit) {
      if (quit == 0) {
        this.redirectJRoute('index.php?option=com_emundus_onboard&view=campaign');
      } else if (quit == 1) {
        document.cookie = 'campaign_'+this.campaignId+'_menu = 2; expires=Session; path=/'
        this.redirectJRoute('index.php?option=com_emundus_onboard&view=form&layout=addnextcampaign&cid=' + this.campaignId + '&index=0')
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

    displayProgram() {
      if(this.isHiddenProgram){
        document.getElementById('add-program').style = 'transform: rotate(0)';
        this.form.training = this.old_training;
        this.programForm = this.old_program_form;
        document.getElementById('select_prog').removeAttribute('disabled');
      } else {
        this.old_training = this.form.training;
        this.old_program_form = this.programForm;
        this.form.training = "";
        this.programForm = {
          code: "",
          label: "",
          notes: "",
          programmes: "",
          published: 1,
          apply_online: 1
        }
        document.getElementById('add-program').style = 'transform: rotate(135deg)';
        document.getElementById('select_prog').setAttribute('disabled', 'disabled');
      }
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

      if(this.manyLanguages !=0) {

        this.show(
            "foo-velocity",
            Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATETIP") + '<em class="translate-icon"></em>',
            Joomla.JText._("COM_EMUNDUS_ONBOARD_TIP"),
        );
      }
    },

    show(group, text = "", title = "Information") {
      this.$notify({
        group,
        title: `${title}`,
        text,
        duration: 100000
      });
    },
    clean(group) {
      this.$notify({ group, clean: true });
    },
  },

  watch: {
    'form.start_date': function (val) {
      this.minDate = LuxonDateTime.fromISO(val).plus({ days: 1 }).toISO();
      if (this.form.end_date == "") {
        this.form.end_date = LuxonDateTime.fromISO(val).plus({days: 1});
      }
    }
  }
};
</script>

<style scoped>
.w-container.general-information {
  max-width: inherit !important;
}

.w-container.btns-sauvegarder-et-continuer {
  max-width: inherit !important;
}

.w-container.btns-sauvegarder-et-continuer .container-evaluation {
  margin: 0;
}
</style>
