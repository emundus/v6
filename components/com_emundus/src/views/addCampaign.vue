<template>
  <div class="campaigns__add-campaign">
    <div>
      <form @submit.prevent="submit">
        <div>
          <div class="em-red-500-color em-mb-8">{{ translate('COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE') }}</div>

          <div class="em-mb-16">
            <label for="campLabel">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_CAMPNAME') }} <span class="em-red-500-color">*</span></label>
            <input
                id="campLabel"
                type="text"
                v-focus
                v-model="form.label[actualLanguage]"
                required
                :class="{ 'is-invalid': errors.label }"
            />
          </div>
          <span v-if="errors.label" class="em-red-500-color em-mb-8">
            <span class="em-red-500-color">{{ translate('COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME') }}</span>
          </span>

          <div class="em-grid-2 em-mb-16">
            <div>
              <div>
                <label for="startDate">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_STARTDATE') }} <span class="em-red-500-color">*</span></label>
                <datetime
                    v-model="form.start_date"
                    id="startDate"
                    type="datetime"
                    :placeholder="translate('COM_EMUNDUS_ONBOARD_ADDCAMP_STARTDATE')"
                    :input-id="'start_date'"
                    :phrases="{ok: translate('COM_EMUNDUS_ONBOARD_OK'), cancel: translate('COM_EMUNDUS_ONBOARD_CANCEL')}"
                ></datetime>
              </div>
            </div>
            <div>
              <div>
                <label for="endDate">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_ENDDATE') }} <span class="em-red-500-color">*</span></label>
                <datetime
                    v-model="form.end_date"
                    id="endDate"
                    type="datetime"
                    :placeholder="translate('COM_EMUNDUS_ONBOARD_ADDCAMP_ENDDATE') + ' *'"
                    :input-id="'end_date'"
                    :min-datetime="minDate"
                    :phrases="{ok: translate('COM_EMUNDUS_ONBOARD_OK'), cancel: translate('COM_EMUNDUS_ONBOARD_CANCEL')}"
                ></datetime>
              </div>
            </div>
          </div>

          <div class="em-mb-16">
            <label for="year">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_PICKYEAR') }} <span class="em-red-500-color">*</span></label>
            <autocomplete
                :id="'year'"
                @searched="onSearchYear"
                :items="this.session"
                :year="form.year"
                :name="'2020 - 2021'"
            />
          </div>

          <div class="em-mb-16 em-flex-row">
            <div class="em-toggle">
              <input type="checkbox"
                     true-value="1"
                     false-value="0"
                     class="em-toggle-check"
                     id="published"
                     name="published"
                     v-model="form.published"
              />
              <strong class="b em-toggle-switch"></strong>
              <strong class="b em-toggle-track"></strong>
            </div>
            <span for="published" class="em-ml-8">{{ translate('COM_EMUNDUS_ONBOARD_FILTER_PUBLISH') }}</span>
          </div>
        </div>

        <hr/>

        <div>
          <div class="em-mb-16">
            <h2>{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION') }}</h2>
          </div>

          <div class="em-mb-16">
            <label style="top: 5em">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_RESUME') }} <span class="em-red-500-color">*</span></label>
            <textarea
                type="textarea"
                rows="2"
                id="campResume"
                maxlength="500"
                placeholder=" "
                v-model="form.short_description"
                @keyup="checkMaxlength('campResume')"
                @focusout="removeBorderFocus('campResume')"
            />
          </div>

          <div class="em-mb-16" v-if="typeof form.description != 'undefined'">
            <editor :height="'30em'" :text="form.description" v-model="form.description" :enable_variables="false" :placeholder="translate('COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION')" :id="'campaign_description'" :key="editorKey"></editor>
          </div>
        </div>

        <hr/>

        <div>
          <div class="em-mb-16">
            <h2>{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM') }}</h2>
          </div>
          <div class="em-mb-16">{{ translate('COM_EMUNDUS_ONBOARD_PROGRAM_INTRO_DESC') }}<span class="em-red-500-color">*</span></div>

          <div class="em-flex-row em-mb-16">
            <select
                id="select_prog"
                class="em-w-100"
                v-model="form.training"
                v-on:change="setCategory"
                :disabled="this.programs.length <= 0"
            >
              <option value="">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_CHOOSEPROG') }}</option>
              <option
                  v-for="(item, index) in this.programs"
                  v-bind:value="item.code"
                  v-bind:data-category="item.programmes"
                  :key="index">
                {{ item.label }}
              </option>
            </select>
            <button v-if="coordinatorAccess != 0" :title="translate('COM_EMUNDUS_ONBOARD_ADDPROGRAM')" type="button" id="add-program" class="em-ml-8 em-transparent-button" @click="displayProgram">
              <span class="material-icons-outlined em-main-500-color">add_circle_outline</span>
            </button>
          </div>

          <transition name="slide-fade">
            <div v-if="isHiddenProgram">
              <div>
                <div class="em-mb-16">
                  <label for="prog_label">{{ translate('COM_EMUNDUS_ONBOARD_PROGNAME') }} <span class="em-red-500-color">*</span></label>
                  <input
                      type="text"
                      id="prog_label"
                      placeholder=" "
                      v-model="programForm.label"
                      @keyup="updateCode"
                      :class="{ 'is-invalid': errors.progLabel }"
                  />
                </div>
                <p v-if="errors.progLabel" class="em-red-500-color em-mb-8">
                  <span class="em-red-500-color">{{ translate('COM_EMUNDUS_ONBOARD_PROG_REQUIRED_LABEL') }}</span>
                </p>
              </div>
            </div>
          </transition>
        </div>

        <hr/>

        <div class="em-flex-row em-flex-space-between">
          <button
              type="button"
              class="em-secondary-button em-w-auto"
              onclick="history.back()">
            {{ translate('COM_EMUNDUS_ONBOARD_ADD_RETOUR') }}
          </button>
          <button
              type="button"
              class="em-primary-button em-w-auto"
              @click="quit = 1; submit()">
            {{ translate('COM_EMUNDUS_ONBOARD_ADD_CONTINUER') }}
          </button>
        </div>
      </form>
    </div>

    <div class="em-page-loader" v-if="submitted"></div>
  </div>
</template>

<script>
import axios from "axios";
import { Datetime } from "vue-datetime";
import { DateTime as LuxonDateTime, Settings } from "luxon";
import Editor from "../components/editor";
import Autocomplete from "../components/autocomplete";
import Translation from "../components/translation"

/** SERVICES **/
import campaignService from 'com_emundus/src/services/campaign';

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

  props: {
    campaign: Number,
  },

  data: () => ({
    // props
    campaignId: 0,
    actualLanguage: "",
    coordinatorAccess: 0,
    quit: 1,

    isHiddenProgram: false,

    // Date picker rules
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

    submitted: false
  }),

  created() {
    if (this.$props.campaign == "") {
      // Get datas that we need with store
      this.campaignId = this.$store.getters['global/datas'].campaign.value;
    } else {
      this.campaignId = this.$props.campaign;
    }

    this.actualLanguage = this.$store.getters['global/actualLanguage'];
    this.coordinatorAccess = this.$store.getters['global/coordinatorAccess'];

    // Configure datetime
    Settings.defaultLocale = this.actualLanguage;
    //

    let now = new Date();
    this.form.start_date = LuxonDateTime.local(now.getFullYear(),now.getMonth() + 1,now.getDate(),0,0,0).toISO();
    this.getLanguages();
    this.getCampaignById();
  },
  methods: {
    getCampaignById() {
      // Check if we add or edit a campaign
      if (typeof this.campaignId !== 'undefined' && this.campaignId !== "") {
        axios.get(
            `index.php?option=com_emundus&controller=campaign&task=getcampaignbyid&id=${this.campaignId}`
        ).then(response => {
          let label = response.data.data.campaign.label;

          this.form = response.data.data.campaign;
          this.$emit('getInformations',this.form);
          this.programForm = response.data.data.program;

          // Check label translations
          this.form.label = response.data.data.label;
          this.languages.forEach((language) => {
            if(this.form.label[language.sef] === '' || this.form.label[language.sef] == null) {
              this.form.label[language.sef] = label;
            }
          });
          //

          // Convert date
          this.form.start_date = LuxonDateTime.fromSQL(this.form.start_date).toISO();
          this.form.end_date = LuxonDateTime.fromSQL(this.form.end_date).toISO();
          if (this.form.end_date === "0000-00-00T00:00:00.000Z") {
            this.form.end_date = "";
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
      this.getAllPrograms();
    },
    getAllPrograms() {
      axios.get("index.php?option=com_emundus&controller=programme&task=getallprogram")
          .then(response => {
            this.programs = response.data.data;
            if(Object.keys(this.programs).length !== 0) {
              this.programs.sort((a, b) => a.id - b.id);
            }
          }).catch(e => {
        console.log(e);
      });

      this.getYears();
    },
    getYears() {
      axios.get("index.php?option=com_emundus&controller=campaign&task=getyears")
        .then(response => {
          this.years = response.data.data;

          this.years.forEach((year) => {
            this.session.push(year.schoolyear);
          });

        }).catch(e => {
          console.log(e);
        });

      this.getStatus();
    },
    getLanguages() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=settings&task=getactivelanguages"
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

    getStatus() {
      axios.get("index.php?option=com_emundus&controller=settings&task=getstatus")
          .then(response => {
            this.status = response.data.data;
          });
    },

    createCampaignWithExistingProgram(form_data){
      campaignService.createCampaign(form_data).then((response) => {
        this.campaignId = response.data.data;
        this.quitFunnelOrContinue(this.quit);
      });
    },

    createCampainWithNoExistingProgram(programForm){
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=programme&task=createprogram",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({body: programForm})
      }).then(() => {
        this.form.training = programForm.code;
        this.form.start_date = LuxonDateTime.fromISO(this.form.start_date).toISO();
        this.form.end_date = LuxonDateTime.fromISO(this.form.end_date).toISO();

        campaignService.createCampaign(this.form).then((response) => {
          this.campaignId = response.data.data;
          this.quitFunnelOrContinue(this.quit);
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

      if (this.form.label.en == "" || this.form.label.en == null || typeof this.form.label.en == "undefined") {
        this.form.label.en = this.form.label.fr;
      }

      this.submitted = true;

      if (typeof this.campaignId !== 'undefined' && this.campaignId !== null && this.campaignId !== "") {
        let task = 'createprogram';
        let params = {body: this.programForm}

        if (this.form.training != "") {
          task = 'updateprogram';
          params = { body: this.programForm, id: this.form.progid };
        }
        axios({
          method: "post",
          url: "index.php?option=com_emundus&controller=programme&task=" + task,
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
            url: "index.php?option=com_emundus&controller=campaign&task=updatecampaign",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({ body: this.form, cid: this.campaignId })
          }).then(() => {
            this.$emit('nextSection');
          }).catch(error => {
            console.log(error);
          });
        }).catch(error => {
          console.log(error);
        });
      } else {
        // get program code if there is training value
        if (this.form.training !== "")  {
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
      if (quit === 0) {
        this.redirectJRoute('index.php?option=com_emundus&view=campaign');
      } else if (quit === 1) {
        document.cookie = 'campaign_'+this.campaignId+'_menu = 2; expires=Session; path=/';
        this.redirectJRoute('index.php?option=com_emundus&view=campaigns&layout=addnextcampaign&cid=' + this.campaignId + '&index=0')
      }
    },

    redirectJRoute(link) {
      window.location.href = link
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
  },

  watch: {
    'form.start_date': function (val) {
      this.minDate = LuxonDateTime.fromISO(val).plus({ days: 1 }).toISO();
      if (this.form.end_date == "") {
        this.form.end_date = LuxonDateTime.fromISO(val).plus({days: 1}).toISO();
      }
    }
  }
};
</script>

<style scoped>
@import "../assets/css/date-time.css";
.campaigns__add-campaign{
  width: 75rem;
}
#add-program{
  height: 24px;
  width: 24px;
  padding: unset;
}
</style>
