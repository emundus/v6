<template>
  <div class="campaigns__add-campaign">
    <div v-if="typeof campaignId == 'undefined' || campaignId == 0">
      <div class="flex items-center mt-4 cursor-pointer" @click="redirectJRoute('index.php?option=com_emundus&view=campaigns')">
        <span class="material-icons-outlined">arrow_back</span>
        <p class="ml-2">{{ translate('BACK') }}</p>
      </div>

      <h1 class="mt-4">{{ translate('COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN') }}</h1>
      <p class="mt-4">{{ translate('COM_EMUNDUS_GLOBAL_INFORMATIONS_DESC') }}</p>

      <hr class="mt-1.5 mb-1.5">
    </div>

    <div>
      <form @submit.prevent="submit" v-if="ready" class="emundus-form fabrikForm">
        <div>
          <div class="em-red-500-color mb-2">{{ translate('COM_EMUNDUS_ONBOARD_REQUIRED_FIELDS_INDICATE') }}</div>

          <div class="mb-4">
            <label for="campLabel">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_CAMPNAME') }} <span class="em-red-500-color">*</span></label>
            <input
                id="campLabel"
                type="text"
                v-model="form.label[actualLanguage]"
                required
                :class="{ 'is-invalid': errors.label }"
                class="form-control fabrikinput w-full"
                @focusout="onFormChange()"
            />
            <span v-if="errors.label" class="em-red-500-color mb-2">
              <span class="em-red-500-color">{{ translate('COM_EMUNDUS_ONBOARD_FORM_REQUIRED_NAME') }}</span>
            </span>
          </div>

          <div class="grid grid-cols-2 mb-4 gap-1.5">
            <div>
              <div>
                <label for="startDate">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_STARTDATE') }} <span class="em-red-500-color">*</span></label>
                <datetime
                    v-model="form.start_date"
                    id="startDate"
                    type="datetime"
                    class="w-full"
                    format=""
                    :placeholder="translate('COM_EMUNDUS_ONBOARD_ADDCAMP_STARTDATE')"
                    :input-id="'start_date'"
                    :phrases="{ok: translate('COM_EMUNDUS_ONBOARD_OK'), cancel: translate('COM_EMUNDUS_ONBOARD_CANCEL')}"
                    @focusout="onFormChange()"
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
                    class="w-full"
                    format=""
                    :placeholder="translate('COM_EMUNDUS_ONBOARD_ADDCAMP_ENDDATE') + ' *'"
                    :input-id="'end_date'"
                    :min-datetime="minDate"
                    :phrases="{ok: translate('COM_EMUNDUS_ONBOARD_OK'), cancel: translate('COM_EMUNDUS_ONBOARD_CANCEL')}"
                    @focusout="onFormChange()"
                ></datetime>
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label for="year">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_PICKYEAR') }} <span class="em-red-500-color">*</span></label>
            <autocomplete
                :id="'year'"
                @searched="onSearchYear"
                :items="this.session"
                :year="form.year"
                :name="'2020 - 2021'"
            />
          </div>

          <div class="mb-4 flex items-center">
            <div class="em-toggle">
              <input type="checkbox"
                     true-value="1"
                     false-value="0"
                     class="em-toggle-check"
                     id="published"
                     name="published"
                     v-model="form.published"
                     @click="onFormChange()"
              />
              <strong class="b em-toggle-switch"></strong>
              <strong class="b em-toggle-track"></strong>
            </div>
            <span for="published" class="ml-2">{{ translate('COM_EMUNDUS_ONBOARD_FILTER_PUBLISH') }}</span>
          </div>

          <div class="mb-4 flex items-center">
            <div class="em-toggle">
              <input type="checkbox"
                     true-value="1"
                     false-value="0"
                     class="em-toggle-check"
                     id="pinned"
                     name="pinned"
                     v-model="form.pinned"
                     @click="onFormChange()"
              />
              <strong class="b em-toggle-switch"></strong>
              <strong class="b em-toggle-track"></strong>
            </div>
            <span for="pinned" class="ml-2 flex items-center">{{ translate('COM_EMUNDUS_CAMPAIGNS_PIN') }}
              <span class="material-icons-outlined em-ml-4 em-font-size-16 em-pointer" @click="displayPinnedCampaignTip">help_outline</span>
            </span>
          </div>
        </div>

        <hr/>

        <div class="mb-4">
          <div class="mb-4">
            <h3>{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_INFORMATION') }}</h3>
          </div>

          <div class="mb-4">
            <label style="top: 5em">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_RESUME') }} <span class="em-red-500-color">*</span></label>
            <editor-quill
                style="height: 25em"
                :text="form.short_description"
                v-model="form.short_description"
                :enable_variables="false"
                :placeholder="translate('COM_EMUNDUS_ONBOARD_ADDCAMP_RESUME')"
                :id="'campResume'"
                :key="editorResumeKey"
                :limit="500"
                :toolbar="'light'"
                @focusout="onFormChange">
            </editor-quill>
          </div>

          <label class="mt-4">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION') }}</label>
          <div class="mb-4" v-if="typeof form.description != 'undefined'">
            <editor-quill
                style="height: 25em"
                :text="form.description"
                v-model="form.description"
                :enable_variables="false"
                :placeholder="translate('COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION')"
                :id="'campaign_description'"
                :key="editorKey"
                @focusout="onFormChange"
            ></editor-quill>
          </div>
        </div>

        <hr class="mt-16"/>

        <div class="mt-8">
          <div class="mb-4">
            <h2>{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM') }}</h2>
          </div>
          <div class="mb-4">{{ translate('COM_EMUNDUS_ONBOARD_PROGRAM_INTRO_DESC') }}<span class="em-red-500-color">*</span></div>

          <div class="flex items-center mb-4">
            <select
                id="select_prog"
                class="form-control fabrikinput w-full"
                v-model="form.training"
                v-on:change="setCategory"
                :disabled="this.programs.length <= 0"
            >
              <option value="">{{ translate('COM_EMUNDUS_ONBOARD_ADDCAMP_CHOOSEPROG') }}</option>
              <option
                  v-for="(item, index) in programs"
                  v-bind:value="item.code"
                  v-bind:data-category="item.programmes"
                  :key="index">
                {{ item.label && item.label[actualLanguage] !== null && typeof item.label[actualLanguage] != 'undefined' ? item.label[actualLanguage] : item.label }}
              </option>
            </select>
            <button v-if="coordinatorAccess != 0" :title="translate('COM_EMUNDUS_ONBOARD_ADDPROGRAM')" type="button" id="add-program" class="ml-2 em-transparent-button" @click="displayProgram">
              <span class="material-icons-outlined em-main-500-color">add_circle_outline</span>
            </button>
          </div>

          <transition name="slide-fade">
            <div v-if="isHiddenProgram">
              <div>
                <div class="mb-4">
                  <label for="prog_label">{{ translate('COM_EMUNDUS_ONBOARD_PROGNAME') }} <span class="em-red-500-color">*</span></label>
                  <input
                      type="text"
                      id="prog_label"
                      class="form-control fabrikinput w-full"
                      placeholder=" "
                      v-model="programForm.label"
                      :class="{ 'is-invalid': errors.progLabel }"
                  />
                </div>
                <p v-if="errors.progLabel" class="em-red-500-color mb-2">
                  <span class="em-red-500-color">{{ translate('COM_EMUNDUS_ONBOARD_PROG_REQUIRED_LABEL') }}</span>
                </p>

                <div class="mb-4" style="display: none">
                  <label for="prog_color">{{ translate('COM_EMUNDUS_ONBOARD_PROGCOLOR') }}</label>
                  <div class="flex">
                    <div v-for="(color,index) in colors" :key="color.text">
                      <div class="em-color-round cursor-pointer flex justify-center"
                           :class="index != 0 ? 'ml-2' : ''"
                           :style="selectedColor == color.text ? 'background-color:' + color.text + ';border: 2px solid ' + color.background : 'background-color:' + color.text"
                           @click="programForm.color = color.text;selectedColor = color.text">
                        <span v-if="selectedColor == color.text" class="material-icons-outlined" style="font-weight: bold;color: black;filter: invert(1)">done</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <hr class="mt-1.5 mb-1.5"/>

        <div class="flex justify-between float-right em-mb-16">
          <button
              type="button"
              class="em-primary-button em-w-auto"
              @click="quit = 1; submit()">
            {{ translate('COM_EMUNDUS_ONBOARD_ADD_CONTINUER') }}
          </button>
        </div>
      </form>
    </div>

    <div class="em-page-loader" v-if="submitted || !ready"></div>
  </div>
</template>

<script>
import Swal from "sweetalert2";
import axios from "axios";
import { Datetime } from "vue-datetime";
import { DateTime as LuxonDateTime, Settings } from "luxon";
import Autocomplete from "../components/autocomplete";
import Translation from "../components/translation"

/** SERVICES **/
import campaignService from 'com_emundus/src/services/campaign';
import EditorQuill from "../components/editorQuill";

const qs = require("qs");

export default {
  name: "addCampaign",

  components: {
    EditorQuill,
    Datetime,
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
    editorResumeKey: 0,

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
      pinned: 0,
    },
    programForm: {
      code: "",
      label: "",
      notes: "",
      programmes: "",
      published: 1,
      apply_online: 1,
      color: "#1C6EF2"
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

    colors: [
        {
          text: '#1C6EF2',
          background: '#79B6FB',
        },
      {
        text: '#20835F',
        background: '#87D4B8',
      },
      {
        text: '#DB333E',
        background: '#FBABAB',
      },
      {
        text: '#FFC633',
        background: '#FEEBA1',
      },
    ],
    selectedColor: '#1C6EF2',

    submitted: false,
    ready: false,
  }),

  created() {
    if (this.$props.campaign == '') {
      // Get datas that we need with store
      this.campaignId = this.$store.getters['global/datas'].campaign ? this.$store.getters['global/datas'].campaign.value : 0;
    } else {
      this.campaignId = this.$props.campaign ? this.$props.campaign : 0;
    }

    this.actualLanguage = this.$store.getters['global/shortLang'];
    this.coordinatorAccess = this.$store.getters['global/coordinatorAccess'];

    // Configure datetime
    Settings.defaultLocale = this.actualLanguage;
    //

    let now = new Date();
    this.form.start_date = LuxonDateTime.local(now.getFullYear(),now.getMonth() + 1,now.getDate(),0,0,0).toISO();
    this.getLanguages().then(() => {
      this.getCampaignById();
    });
  },
  methods: {

    displayPinnedCampaignTip() {
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_PINNED_CAMPAIGN_TIP"),
        text: this.translate("COM_EMUNDUS_ONBOARD_PINNED_CAMPAIGN_TIP_TEXT"),
        showCancelButton: false,
        confirmButtonText: this.translate("COM_EMUNDUS_SWAL_OK_BUTTON"),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          confirmButton: 'em-swal-confirm-button',
          actions: "em-swal-single-action",
        },
      });
    },
    getCampaignById() {
      // Check if we add or edit a campaign
      if (typeof this.campaignId !== 'undefined' && this.campaignId !== '' && this.campaignId > 0) {
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
          this.ready = true;
        }).catch(e => {
          console.log(e);
        });
      } else {
        this.ready = true;
      }
      this.getAllPrograms();
    },
    getAllPrograms() {
      axios.get('index.php?option=com_emundus&controller=programme&task=getallprogram')
          .then(response => {

	          if (response.data.status) {
		          this.programs = response.data.data.datas;
		          if(Object.keys(this.programs).length !== 0) {
			          this.programs.sort((a, b) => a.id - b.id);
		          }
	          } else {
		          this.programs = [];
	          }
          }).catch(e => {
              console.log(e);
          });
      this.getYears();
    },
    getYears() {
      axios.get('index.php?option=com_emundus&controller=campaign&task=getyears')
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
    async getLanguages() {
      const response = await axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=settings&task=getactivelanguages"
      });

      if (response) {
        this.languages = response.data.data;
      }

      return response;
    },

    setCategory(e) {
      this.year.programmes = e.target.options[e.target.options.selectedIndex].dataset.category;
      this.programForm = this.programs.find(program => program.code == this.form.training);
    },

    getStatus() {
      axios.get("index.php?option=com_emundus&controller=settings&task=getstatus")
          .then(response => {
            this.status = response.data.data;
          });
    },

	  createCampaign(form_data){
      campaignService.createCampaign(form_data).then((response) => {
        if(response.data.status == 1) {
          this.campaignId = response.data.data;
          this.quitFunnelOrContinue(this.quit, response.data.redirect);
        }
      });
    },

	  createCampaignWithNoExistingProgram(programForm){
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=programme&task=createprogram",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({body: programForm})
      }).then((rep) => {
        if (rep.data.status) {
          this.form.progid = rep.data.data.programme_id;
          this.form.training = rep.data.data.programme_code;
          this.form.start_date = LuxonDateTime.fromISO(this.form.start_date).toISO();
          this.form.end_date = LuxonDateTime.fromISO(this.form.end_date).toISO();

					this.createCampaign(this.form);
        } else {
          Swal.fire({
            title: this.translate(rep.data.msg),
            text: rep.data.data,
            type: "error",
            confirmButtonText: this.translate("OK"),
            customClass: {
              title: 'em-swal-title',
              confirmButton: 'em-flex-center',
            }
          });

          this.submitted = false;
        }
      });
    },


    submit() {
	    this.$store.dispatch('campaign/setUnsavedChanges', true);

      // Checking errors
      this.errors = {
        label: false,
        progCode: false,
        progLabel: false,
        short_description: false,
        limit_files_number: false,
        limit_status: false
      }
      if (this.form.label[this.actualLanguage] === '' || this.form.label[this.actualLanguage] == null || typeof this.form.label[this.actualLanguage] === 'undefined') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        this.errors.label = true;
        return 0;
      }

      if (this.form.end_date == '' || this.form.end_date == '0000-00-00 00:00:00') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
	      const endDate = document.getElementById('end_date');
	      if (endDate) {
		      endDate.focus();
	      }
        return 0;
      }

	    if (this.form.start_date == '' || this.form.start_date == '0000-00-00 00:00:00') {
		    window.scrollTo({ top: 0, behavior: 'smooth' });
				const startDate = document.getElementById('start_date');
				if (startDate) {
					startDate.focus();
				}
		    return 0;
	    }

      if (this.form.year == "") {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        const year = document.getElementById('year');
				if (year) {
					year.focus();
				}
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
        if (this.isHiddenProgram) {
          if (this.programForm.label == "") {
            this.errors.progLabel = true;
	          document.getElementById('prog_label').focus();
	          return 0;
          } else {
						// does this label already exists
						const similarProgram = this.programs.find((program) => {
							return program.label == this.programForm.label;
						});

						if (similarProgram != undefined) {
							this.errors.progLabel = true;
							document.getElementById('prog_label').focus();
							return 0;
						}
          }
        } else {
          document.getElementById('select_prog').focus();
          return 0;
        }
      }

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

      if (typeof this.campaignId !== 'undefined' && this.campaignId !== null && this.campaignId !== "" && this.campaignId !== 0) {
        if (this.form.training != '') {
          this.updateCampaign();
        } else {
	        axios({
		        method: "post",
		        url: "index.php?option=com_emundus&controller=programme&task=createprogram",
		        headers: {
			        "Content-Type": "application/x-www-form-urlencoded"
		        },
		        data: qs.stringify( {body: this.programForm})
	        }).then((response) => {
		        this.programForm.code = response.data.data.programme_code;
		        this.form.progid = response.data.data.programme_id;

						this.updateCampaign();
	        }).catch(error => {
		        console.log(error);
	        });
        }
      } else {
        // get program code if there is training value
        if (this.form.training !== "")  {
          this.programForm = this.programs.find(program => program.code == this.form.training);
          this.form.training = this.programForm.code;
          this.form.start_date = LuxonDateTime.fromISO(this.form.start_date).toISO();
          this.form.end_date = LuxonDateTime.fromISO(this.form.end_date).toISO();
          this.createCampaign(this.form);
        } else {
          this.createCampaignWithNoExistingProgram(this.programForm);
        }
      }
    },

	  updateCampaign() {
		  this.form.training = this.programForm.code;
		  this.form.start_date = LuxonDateTime.fromISO(this.form.start_date).toISO();
		  this.form.end_date = LuxonDateTime.fromISO(this.form.end_date).toISO();

		  axios({
			  method: 'post',
			  url: 'index.php?option=com_emundus&controller=campaign&task=updatecampaign',
			  headers: {
				  'Content-Type': 'application/x-www-form-urlencoded'
			  },
			  data: qs.stringify({ body: this.form, cid: this.campaignId })
		  }).then((response) => {
			  if (!response.status) {
				  Swal.fire({
					  type: 'error',
					  title: this.translate('COM_EMUNDUS_ADD_CAMPAIGN_ERROR'),
					  reverseButtons: true,
					  customClass: {
						  title: 'em-swal-title',
						  confirmButton: 'em-swal-confirm-button',
						  actions: "em-swal-single-action",
					  },
				  });
				  this.submitted = false;
				  return 0;
			  } else {
				  this.$emit('nextSection');
				  this.$emit('updateHeader',this.form);
			  }
		  }).catch(error => {
			  console.log(error);
		  });
	  },

    quitFunnelOrContinue(quit, redirect = '') {
      if (quit === 0) {
        this.redirectJRoute('index.php?option=com_emundus&view=campaign');
      } else if (quit === 1) {
        document.cookie = 'campaign_'+this.campaignId+'_menu = 1; expires=Session; path=/';

        if(redirect === '') {
          redirect = 'index.php?option=com_emundus&view=campaigns&layout=addnextcampaign&cid=' + this.campaignId + '&index=0'
        }

        this.redirectJRoute(redirect)
      }
    },

    redirectJRoute(link) {
      window.location.href = link
    },

    onSearchYear(value) {
      this.form.year = value;
    },
    onFormChange() {
      this.$store.dispatch('campaign/setUnsavedChanges', true);
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
        this.form.training = '';
        this.programForm = {
          code: '',
          label: '',
          notes: '',
          programmes: '',
          published: 1,
          apply_online: 1
        }
        document.getElementById('add-program').style = 'transform: rotate(135deg)';
        document.getElementById('select_prog').setAttribute('disabled', 'disabled');
      }
      this.isHiddenProgram = !this.isHiddenProgram;
    },
  },

  watch: {
    'form.start_date': function (val) {
      this.minDate = LuxonDateTime.fromISO(val).plus({ days: 1 }).toISO();
      if (this.form.end_date == "") {
        this.form.end_date = LuxonDateTime.fromISO(val).plus({days: 1}).toISO();
      }
    },
  }
};
</script>

<style scoped>
@import "../assets/css/date-time.css";
#add-program{
  height: 24px;
  width: 24px;
  padding: unset;
}

#campResume {
  height: 130px !important;
}

.em-color-round{
  height: 30px;
  width: 30px;
  border-radius: 50%;
}
</style>
