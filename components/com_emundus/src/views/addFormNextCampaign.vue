<template>
	<div id="add-form-next-campaign">
		<div class="em-w-custom"></div>
		<div>
			<ModalWarningFormBuilder
					:pid="getProfileId"
					:cid="campaignId"
			/>
			<div>
				<div class="em-flex-row em-mt-16 em-pointer" @click="redirectJRoute('index.php?option=com_emundus&view=campaigns')">
					<span class="material-icons-outlined">arrow_back</span>
					<p class="em-ml-8">{{ translate('BACK') }}</p>
				</div>
				<div class="em-flex-row em-mt-16">
					<h1 class="em-h1" v-if="menuHighlight != -1">{{this.translate(formCategories[menuHighlight])}}</h1>
					<h1 class="em-h1" v-if="menuHighlightProg != -1">{{this.translate(formPrograms[menuHighlightProg])}}</h1>
				</div>
				<p v-if="menuHighlight != -1" v-html="this.translate(formCategoriesDesc[menuHighlight])" style="margin-top: 20px"></p>
				<p v-if="menuHighlightProg != -1" v-html="this.translate(formProgramsDesc[menuHighlightProg])" style="margin-top: 20px"></p>

				<hr>

      <div class="em-flex-row em-mb-32">
        <p>
          <b style="color: var(--em-coordinator-primary-color); font-weight: 700 !important;"> {{form.label}}</b>
          {{translations.From}}
          <strong>{{ form.start_date }}</strong>
          {{translations.To}}
          <strong>{{ form.end_date }}</strong>
        </p>
      </div>

				<!--- start Menu --->
				<div class="em-flex-row" >
					<ul class="nav nav-tabs topnav">

						<li v-for="(formCat, index) in formCategories" :key="'category-' + index" v-show="closeSubmenu">
							<a  @click="profileId != null ? changeToCampMenu(index): ''"
							    class="em-neutral-700-color em-pointer"
							    :class="[(menuHighlight == index ? 'w--current' : ''), (profileId == null ? 'grey-link' : '')]">
								{{ translate(formCat) }}
							</a>
						</li>

						<li v-for="(formProg, index) in formPrograms" :key="'program-' + index" v-show="closeSubmenu">
							<a @click="profileId != null ? changeToProgMenu(index) : ''"
							   class="em-neutral-700-color em-pointer"
							   :class="[(menuHighlightProg == index ? 'w--current' : ''), (profileId == null ? 'grey-link' : '')]">
								{{ translate(formProg) }}
							</a>
						</li>
					</ul>
				</div>
				<br>


				<!-- end Menu -->

				<div v-if="menuHighlightProg != -1" class="warning-message-program mb-1">
					<p class="em-red-500-color em-flex-row"><span class="material-icons-outlined em-mr-8 em-red-500-color">warning_amber</span>{{translations.ProgramWarning}}</p>
					<ul v-if="campaignsByProgram.length > 0" class="em-mt-8 em-mb-32">
						<li v-for="(campaign, index) in campaignsByProgram" :key="'camp_progs_' + index">{{campaign.label}}</li>
					</ul>
				</div>
				<transition name="fade">
					<add-campaign
							v-if="menuHighlight == 0 && campaignId !== ''"
							:campaign="campaignId"
							:coordinatorAccess="true"
							:actualLanguage="actualLanguage"
							:manyLanguages="manyLanguages"
							@nextSection="menuHighlight++"
							@getInformations="initInformations"
							@updateHeader="updateHeader"
					></add-campaign>
					<addFormulaire
							v-if="menuHighlight == 2"
							:profileId="profileId"
							:campaignId="campaignId"
							:profiles="profiles"
							:key="formReload"
							@profileId="setProfileId"
							:visibility="null"
					></addFormulaire>

					<add-documents-dropfiles
							v-if="menuHighlight == 1"
							:funnelCategorie="formCategories[langue][menuHighlight]"
							:profileId="getProfileId"
							:campaignId="campaignId"
							:menuHighlight="menuHighlight"
							:langue="actualLanguage"
							:manyLanguages="manyLanguages"
					/>

					<add-email
							v-if="menuHighlightProg == 0 && program.id != 0"
							:prog="Number(program.id)"
					></add-email>
				</transition>
			</div>

      <div class="em-flex-row em-flex-space-between em-float-right" v-if="menuHighlight !== 0 && menuHighlightProg !== 0">
        <button
            type="button"
            class="em-primary-button em-w-auto"
            @click="next">
          {{ translate('COM_EMUNDUS_ONBOARD_ADD_CONTINUER') }}
        </button>
      </div>

			<div class="em-page-loader" v-if="loading"></div>
		</div>
	</div>
</template>

<script>
import mixin from '../mixins/mixin';
import axios from "axios";

import addCampaign from "@/views/addCampaign";
import ModalWarningFormBuilder from "@/components/AdvancedModals/ModalWarningFormBuilder";
import AddDocumentsDropfiles from "@/components/FunnelFormulaire/addDocumentsDropfiles";
import addEmail from "@/components/FunnelFormulaire/addEmail";
import addFormulaire from "@/components/FunnelFormulaire/addFormulaire";
import AddEvaluationGrid from "@/components/FunnelFormulaire/addEvaluationGrid";
import Swal from "sweetalert2";

const qs = require("qs");

export default {
  name: "addFormNextCampaign",

  components: {
    AddEvaluationGrid,
    AddDocumentsDropfiles,
    addCampaign,
    ModalWarningFormBuilder,
    addFormulaire,
    addEmail,
  },

  props: {
    index: Number,
  },
  mixins: [mixin],

  data: () => ({
    campaignId: 0,
    actualLanguage: "",
    manyLanguages: 0,

    prid: "",
    menuHighlight: 0,
    menuHighlightProg: -1,
    formReload: 0,
    prog: 0,
    loading: false,
    closeSubmenu: true,
    profileId: null,
    profiles: [],
    campaignsByProgram: [],
    langue: 0,
    formCategoriesDesc: [
        'COM_EMUNDUS_GLOBAL_INFORMATIONS_DESC',
        'COM_EMUNDUS_DOCUMENTS_CAMPAIGNS_DESC',
        'COM_EMUNDUS_FORM_CAMPAIGN_DESC'
    ],

    formCategories: [
      'COM_EMUNDUS_GLOBAL_INFORMATIONS',
      'COM_EMUNDUS_DOCUMENTS_CAMPAIGNS',
      'COM_EMUNDUS_FORM_CAMPAIGN'
    ],

    formPrograms: [
      'COM_EMUNDUS_EMAILS'
    ],

    formProgramsDesc: [
      'COM_EMUNDUS_EMAILS_DESC'
    ],

    form: {},

    program: {
      id: 0,
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

    prog_group: {
      prog: null,
      evaluator: null,
      manager: null,
    },

    translations: {
      DATE_FORMAT: 'DATE_FORMAT_JS_LC2',
      From: 'COM_EMUNDUS_ONBOARD_FROM',
      To: 'COM_EMUNDUS_ONBOARD_TO',
      chooseProfileWarning: 'COM_EMUNDUS_ONBOARD_CHOOSE_PROFILE_WARNING',
      ProgramWarning: 'COM_EMUNDUS_ONBOARD_PROGRAM_WARNING',
    },
  }),

  created () {
    // Get datas that we need with store
    this.campaignId = Number(this.$store.getters['global/datas'].campaignId.value);
    this.actualLanguage = this.$store.getters['global/shortLang'];
    this.manyLanguages = Number(this.$store.getters['global/manyLanguages']);
    //

    //this.loading = true;
    if (this.actualLanguage === "en") {
      this.langue = 1;
    }
  },
  methods: {
    initInformations(campaign) {
      this.form.label = campaign.label;
      this.form.profile_id = campaign.profile_id;
      this.form.program_id = campaign.progid;

      this.initDates(campaign);

      axios.get(
          `index.php?option=com_emundus&controller=form&task=getallformpublished`
      ).then(profiles => {
        this.profiles = profiles.data.data;
        if(this.form.profile_id == null) {
          this.profiles.length != 0 ? this.profileId = this.profiles[0].id : this.profileId = null;
          if(this.profileId != null){
            this.formReload += 1;
            //this.updateProfileCampaign(this.profileId)
          }
        } else {
          this.formReload += 1;
          this.profileId = this.form.profile_id;
        }
        this.loading = false;

        let cookie = this.getCookie('campaign_'+this.campaignId+'_menu');
        if(cookie){
          this.menuHighlight = cookie;
          document.cookie = 'campaign_'+this.campaignId+'_menu =; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        }
      });
    },

    updateHeader(value){
      this.form.label = value.label[this.actualLanguage];
      this.initDates(value);
    },

    initDates(campaign){
      this.form.start_date = campaign.start_date;
      this.form.end_date = campaign.end_date;

	    let currentLanguage = this.$store.getters['global/currentLanguage'];
			if (currentLanguage === '' || currentLanguage === undefined) {
				currentLanguage = 'fr-FR';
			}

			const dateOptions = { dateStyle: 'long', timeStyle: 'short' };
	    const startDate = new Date(campaign.start_date);
	    this.form.start_date = new Intl.DateTimeFormat(currentLanguage, dateOptions).format(startDate);

      if (this.form.end_date === '0000-00-00 00:00:00') {
        this.form.end_date = null;
      } else {
        const endDate = new Date(campaign.end_date);
				this.form.end_date = new Intl.DateTimeFormat(currentLanguage, dateOptions).format(endDate);
      }
    },

    changeToProgMenu(index) {
      axios.get(`index.php?option=com_emundus&controller=programme&task=getprogrambyid&id=${this.form.program_id}`)
          .then(rep => {
            this.program.id = rep.data.data.id;
            this.program.code = rep.data.data.code;
            this.program.label = rep.data.data.label;
            this.program.notes = rep.data.data.notes;
            this.program.programmes = rep.data.data.programmes;
            this.program.tmpl_badge = rep.data.data.tmpl_badge;
            this.program.published = rep.data.data.published;
            this.program.apply_online = rep.data.data.apply_online;
            if (rep.data.data.synthesis != null) {
              this.program.synthesis = rep.data.data.synthesis.replace(/>\s+</g, "><");
            }
            if (rep.data.data.tmpl_trombinoscope != null) {
              this.program.tmpl_trombinoscope = rep.data.data.tmpl_trombinoscope.replace(
                  />\s+</g,
                  "><"
              );
            }
            this.prog_group.prog = rep.data.data.group;
            this.prog_group.evaluator = rep.data.data.evaluator_group;
            this.prog_group.manager = rep.data.data.manager_group;
            axios({
              method: "get",
              url: "index.php?option=com_emundus&controller=programme&task=getcampaignsbyprogram",
              params: {
                pid: this.program.id,
              },
              paramsSerializer: params => {
                return qs.stringify(params);
              }
            }).then(repcampaigns => {
              this.campaignsByProgram = repcampaigns.data.campaigns;
            });
          }).catch(e => {
        console.log(e);
      });

      this.menuHighlightProg = index;
      this.menuHighlight = -1;
    },

    changeToCampMenu(index) {
      if (this.formCategories[this.menuHighlight] == "COM_EMUNDUS_GLOBAL_INFORMATIONS" && this.$store.getters['campaign/unsavedChanges'] === true) {
        // check if there are unsaved changes
        Swal.fire({
          title: this.translate("COM_EMUNDUS_CAMPAIGN_UNSAVED_CHANGES"),
          type: "warning",
            showCancelButton: true,
            confirmButtonText: this.translate("JYES"),
            cancelButtonText: this.translate("JNO"),
            reverseButtons: true,
            customClass: {
              title: 'em-swal-title',
              cancelButton: 'em-swal-cancel-button',
              confirmButton: 'em-swal-confirm-button',
            }
        }).then(result => {
          if (result.value) {
            this.menuHighlight = index;
            this.menuHighlightProg = -1;
            this.$store.dispatch("campaign/setUnsavedChanges", false);
          }
        });
      } else {
        this.menuHighlight = index;
        this.menuHighlightProg = -1;
      }
    },

    setProfileId(prid) {
      this.profileId = prid;
    },
    next() {
      if (this.menuHighlight < 2) {
        let index = this.menuHighlight + 1;
        this.changeToCampMenu(index)
      } else if(this.menuHighlightProg < 1) {
        this.changeToProgMenu(0)
      }
    },

    previous() {
      if (this.menuHighlight > 0) {
        this.menuHighlight--;
      } else {
        this.redirectJRoute('index.php?option=com_emundus&view=campaign');
      }
    },

    redirectJRoute(link) {
      window.location.href = link;
    },

    getCookie(cname) {
      var name = cname + "=";
      var decodedCookie = decodeURIComponent(document.cookie);
      var ca = decodedCookie.split(';');

      for (let c of ca) {
        while (c.charAt(0) == ' ') {
          c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
          return c.substring(name.length, c.length);
        }
      }
      return "";
    },
  },

  computed: {
    console: () => console,
    getProfileId() {
      return Number(this.profileId);
    },
  },
};
</script>

<style scoped>
@import "../assets/css/formbuilder.scss";

.w--current{
  border: solid 1px #eeeeee;
  background: #eeeeee;
}

.w--current:hover{
  color: var(--main-500);
}

.em-pointer:hover{
  color: var(--main-500);
}

.em-w-custom {
  width: calc(100% - 75px) !important;
  margin-left: auto;
}

#add-form-next-campaign{
  width: 100%;
}
</style>
