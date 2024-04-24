<template>
  <div id="add-form-next-campaign">
    <div class="em-w-custom"></div>
    <div>
      <ModalWarningFormBuilder
          :pid="getProfileId"
          :cid="campaignId"
      />
      <div>
        <div class="em-flex-row em-pointer" @click="redirectJRoute('index.php?option=com_emundus&view=campaigns')">
          <span class="material-icons-outlined">navigate_before</span>
          <span class="em-ml-8 em-text-neutral-900">{{ translate('BACK') }}</span>
        </div>
        <div class="em-flex-row em-mt-16">
          <h1>{{ translate(selectedMenuItem.label) }}</h1>
        </div>
        <p> {{ translate(selectedMenuItem.description) }} </p>
        <hr>

        <div class="em-flex-row em-mb-32">
          <p>
            <b style="color: var(--em-profile-color); font-weight: 700 !important;"> {{ form.label }}</b>
            {{ translate('COM_EMUNDUS_ONBOARD_FROM') }}
            <strong>{{ form.start_date }}</strong>
            {{ translate('COM_EMUNDUS_ONBOARD_TO') }}
            <strong>{{ form.end_date }}</strong>
          </p>
        </div>

        <!--- start Menu --->
        <div class="em-flex-row">
          <ul class="nav nav-tabs topnav">

            <li v-for="menu in menus" :key="menu.component" @click="selectMenu(menu)" :class="{'w--current': selectedMenu === menu.component}">
              <a href="javascript:void(0)">
                <span class="material-icons-outlined em-mr-8">{{ menu.icon }}</span>
                {{ translate(menu.label) }}
              </a>
            </li>
          </ul>
        </div>
        <br>


        <!-- end Menu -->

        <div v-if="selectedMenu === 'addEmail'" class="warning-message-program mb-1">
          <p class="em-red-500-color em-flex-row"><span class="material-icons-outlined em-mr-8 em-red-500-color">warning_amber</span>{{ translate('COM_EMUNDUS_ONBOARD_PROGRAM_WARNING') }}
          </p>
          <ul v-if="campaignsByProgram.length > 0" class="em-mt-8 em-mb-32 em-pl-16">
            <li v-for="(campaign, index) in campaignsByProgram" :key="'camp_progs_' + index">{{ campaign.label }}</li>
          </ul>
        </div>
        <transition name="fade">
          <add-campaign
              v-if="selectedMenu === 'addCampaign' && campaignId !== ''"
              :campaign="campaignId"
              :coordinatorAccess="true"
              :actualLanguage="actualLanguage"
              :manyLanguages="manyLanguages"
              @nextSection="next"
              @getInformations="initInformations"
              @updateHeader="updateHeader"
          ></add-campaign>
          <campaign-more
              v-if="selectedMenu === 'campaignMore' && campaignId > 0"
              :campaignId="campaignId"
          >
          </campaign-more>
          <addFormulaire
              v-if="selectedMenu === 'addFormulaire'"
              :profileId="profileId"
              :campaignId="campaignId"
              :profiles="profiles"
              :key="formReload"
              @profileId="setProfileId"
              :visibility="null"
          ></addFormulaire>

          <add-documents-dropfiles
              v-if="selectedMenu === 'addDocumentsDropfiles'"
              :funnelCategorie="selectedMenuItem.label"
              :profileId="getProfileId"
              :campaignId="campaignId"
              :langue="actualLanguage"
              :manyLanguages="manyLanguages"
          />

          <add-email
              v-if="selectedMenu === 'addEmail' && program.id != 0"
              :prog="Number(program.id)"
          ></add-email>
        </transition>
      </div>

      <div class="em-flex-row em-flex-space-between em-float-right"
           v-if="menuHighlight !== 0 && menuHighlightProg !== 0">
        <button
            type="button"
            class="em-primary-button em-w-auto mb-4"
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
import campaignMore from "@/components/FunnelFormulaire/CampaignMore";
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
    campaignMore
  },

  props: {
    index: Number,
  },
  mixins: [mixin],

  data: () => ({
    campaignId: 0,
    actualLanguage: '',
    manyLanguages: 0,
    prid: '',
    menus: [
      {
        label: "COM_EMUNDUS_GLOBAL_INFORMATIONS",
        description: "COM_EMUNDUS_GLOBAL_INFORMATIONS_DESC",
        icon: "info",
        component: "addCampaign"
      },
      {
        label: "COM_EMUNDUS_CAMPAIGN_MORE",
        description: "COM_EMUNDUS_CAMPAIGN_MORE_DESC",
        icon: "description",
        component: "campaignMore"
      },
      {
        label: "COM_EMUNDUS_DOCUMENTS_CAMPAIGNS",
        description: "COM_EMUNDUS_DOCUMENTS_CAMPAIGNS_DESC",
        icon: "description",
        component: "addDocumentsDropfiles"
      },
      {
        label: "COM_EMUNDUS_FORM_CAMPAIGN",
        description: "COM_EMUNDUS_FORM_CAMPAIGN_DESC",
        icon: "description",
        component: "addFormulaire"
      },
      {
        label: "COM_EMUNDUS_EMAILS",
        description: "COM_EMUNDUS_EMAILS_DESC",
        icon: "description",
        component: "addEmail"
      }
    ],
    selectedMenu: 'addCampaign',
    formReload: 0,
    prog: 0,
    loading: false,
    closeSubmenu: true,
    profileId: null,
    profiles: [],
    campaignsByProgram: [],
    form: {},
    program: {
      id: 0,
      code: '',
      label: '',
      notes: '',
      programmes: [],
      tmpl_badge: '',
      published: 0,
      apply_online: 0,
      synthesis: '',
      tmpl_trombinoscope: '',
    },
  }),

  created() {
    // Get datas that we need with store
    this.campaignId = Number(this.$store.getters['global/datas'].campaignId.value);
    this.actualLanguage = this.$store.getters['global/shortLang'];
    this.manyLanguages = Number(this.$store.getters['global/manyLanguages']);
    //

    this.getProgram();

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
        if (this.form.profile_id == null) {
          this.profiles.length != 0 ? this.profileId = this.profiles[0].id : this.profileId = null;
          if (this.profileId != null) {
            this.formReload += 1;
            //this.updateProfileCampaign(this.profileId)
          }
        } else {
          this.formReload += 1;
          this.profileId = this.form.profile_id;
        }
        this.loading = false;

        let cookie = this.getCookie('campaign_' + this.campaignId + '_menu');
        if (cookie) {
          this.menuHighlight = cookie;
          document.cookie = 'campaign_' + this.campaignId + '_menu =; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        }
      });
    },

    updateHeader(value) {
      this.form.label = value.label[this.actualLanguage];
      this.initDates(value);
    },

    initDates(campaign) {
      this.form.start_date = campaign.start_date;
      this.form.end_date = campaign.end_date;

      let currentLanguage = this.$store.getters['global/currentLanguage'];
      if (currentLanguage === '' || currentLanguage === undefined) {
        currentLanguage = 'fr-FR';
      }

      const dateOptions = {dateStyle: 'long', timeStyle: 'short'};
      const startDate = new Date(campaign.start_date);
      this.form.start_date = new Intl.DateTimeFormat(currentLanguage, dateOptions).format(startDate);

      if (this.form.end_date === '0000-00-00 00:00:00') {
        this.form.end_date = null;
      } else {
        const endDate = new Date(campaign.end_date);
        this.form.end_date = new Intl.DateTimeFormat(currentLanguage, dateOptions).format(endDate);
      }
    },

    getProgram() {
      axios.get(`/index.php?option=com_emundus&controller=campaign&task=getProgrammeByCampaignID&campaign_id=${this.campaignId}`)
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
    },
    selectMenu(menu) {
      this.selectedMenu = menu.component;
    },

    setProfileId(prid) {
      this.profileId = prid;
    },
    next() {
      // select next menu
      let index = this.menus.findIndex(menu => menu.component === this.selectedMenu);
      if (index < this.menus.length - 1) {
        this.selectedMenu = this.menus[index + 1].component;
      }
    },

    previous() {
      let index = this.menus.findIndex(menu => menu.component === this.selectedMenu);
      if (index > 0) {
        this.selectedMenu = this.menus[index - 1].component;
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
    selectedMenuItem() {
      return this.menus.find(menu => menu.component === this.selectedMenu);
    }
  },
};
</script>

<style scoped>
@import "../assets/css/formbuilder.scss";

.w--current {
  border: solid 1px #eeeeee;
  background: #eeeeee;
}

.w--current:hover {
  color: var(--em-profile-color);
}

.em-pointer:hover {
  color: var(--em-profile-color);
}

.em-w-custom {
  width: calc(100% - 75px) !important;
  margin-left: auto;
}

#add-form-next-campaign {
  width: 100%;
}
</style>
