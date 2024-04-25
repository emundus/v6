<template>
  <div id="edit-campaign">
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
        <div class="flex flex-row">
          <ul class="nav nav-tabs topnav">

            <li v-for="menu in displayedMenus" :key="menu.component" @click="selectMenu(menu)" :class="{'w--current': selectedMenu === menu.component}">
              <span class="cursor-pointer">{{ translate(menu.label) }}</span>
            </li>
          </ul>
        </div>
        <br>
        <div v-if="selectedMenu === 'addEmail'" class="warning-message-program mb-1">
          <p class="em-red-500-color flex flex-row"><span class="material-icons-outlined em-mr-8 em-red-500-color">warning_amber</span>{{ translate('COM_EMUNDUS_ONBOARD_PROGRAM_WARNING') }}
          </p>
          <ul v-if="campaignsByProgram.length > 0" class="em-mt-8 em-mb-32 em-pl-16">
            <li v-for="campaign in campaignsByProgram" :key="'camp_progs_' + campaign.id">{{ campaign.label }}</li>
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
              v-if="selectedMenu === 'campaignMore' && campaignId !== ''"
              :campaignId="campaignId"
              :defaultFormUrl="campaignMoreFormUrl"
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
           v-if="['addDocumentsDropfiles', 'addFormulaire'].includes(selectedMenu)">
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
import campaignService from '@/services/campaign.js';

import addCampaign from "@/views/addCampaign";
import ModalWarningFormBuilder from "@/components/AdvancedModals/ModalWarningFormBuilder";
import AddDocumentsDropfiles from "@/components/FunnelFormulaire/addDocumentsDropfiles";
import addEmail from "@/components/FunnelFormulaire/addEmail";
import addFormulaire from "@/components/FunnelFormulaire/addFormulaire";
import campaignMore from "@/components/FunnelFormulaire/CampaignMore";
import Swal from "sweetalert2";

const qs = require("qs");

export default {
  name: 'CampaignEdition',

  components: {
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
        component: "addCampaign",
        displayed: true
      },
      {
        label: "COM_EMUNDUS_CAMPAIGN_MORE",
        description: "COM_EMUNDUS_CAMPAIGN_MORE_DESC",
        icon: "description",
        component: "campaignMore",
        displayed: false
      },
      {
        label: "COM_EMUNDUS_DOCUMENTS_CAMPAIGNS",
        description: "COM_EMUNDUS_DOCUMENTS_CAMPAIGNS_DESC",
        icon: "description",
        component: "addDocumentsDropfiles",
        displayed: true
      },
      {
        label: "COM_EMUNDUS_FORM_CAMPAIGN",
        description: "COM_EMUNDUS_FORM_CAMPAIGN_DESC",
        icon: "description",
        component: "addFormulaire",
        displayed: true
      },
      {
        label: "COM_EMUNDUS_EMAILS",
        description: "COM_EMUNDUS_EMAILS_DESC",
        icon: "description",
        component: "addEmail",
        displayed: true
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
    campaignMoreFormUrl: '',
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
    this.campaignId = parseInt(this.$store.getters['global/datas'].campaignId.value);
    this.actualLanguage = this.$store.getters['global/shortLang'];
    this.manyLanguages = parseInt(this.$store.getters['global/manyLanguages']);
    //

    this.getCampaignMoreForm();
    this.getProgram();

    //this.loading = true;
    if (this.actualLanguage === "en") {
      this.langue = 1;
    }
  },
  methods: {
    getCampaignMoreForm() {
      campaignService.getCampaignMoreFormUrl(this.campaignId)
          .then(response => {
            if (response.status && response.data.length > 0) {
              this.menus.forEach(menu => {
                if (menu.component === 'campaignMore') {
                  menu.displayed = true;
                }
              });
              this.campaignMoreFormUrl = response.data;
            }
          })
          .catch(error => {
            console.error(error);
          });
    },
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
            this.program = rep.data.data;
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
      });
    },
    selectMenu(menu) {
      this.selectedMenu = menu.component;
    },

    setProfileId(prid) {
      this.profileId = prid;
    },
    next() {
      let index = this.displayedMenus.findIndex(menu => menu.component === this.selectedMenu);
      if (index < this.displayedMenus.length - 1) {
        this.selectedMenu = this.displayedMenus[index + 1].component;
      }
    },

    previous() {
      let index = this.displayedMenus.findIndex(menu => menu.component === this.selectedMenu);
      if (index > 0) {
        this.selectedMenu = this.displayedMenus[index - 1].component;
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
    getProfileId() {
      return Number(this.profileId);
    },
    selectedMenuItem() {
      return this.menus.find(menu => menu.component === this.selectedMenu);
    },
    displayedMenus() {
      return this.menus.filter(menu => menu.displayed);
    },
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
