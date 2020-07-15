<!--
  $fnum = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);
  $str = "0001427";
  echo ltrim($str, "0");
-->
<template>
  <div class="column-menu-main w-row" style="margin-top: 120px">
    <ModalWarningFormBuilder
            :pid="profileId"
            :cid="campaignId"
    />
    <div class="w-row">
      <div class="col-md-4 p-1">
        <div class="mb-1">
          <h1 class="nom-campagne">{{ CAMPAIGN }} : {{ form.label }}</h1>
          <div class="date-menu orange">
            {{ form.end_date != null ? From : Since + " " }}
            {{ form.start_date }}
            {{ form.end_date != null ? To + " " + form.end_date : "" }}
          </div>
        </div>

        <p class="heading">{{chooseForm}}</p>
        <div class="heading-block">
          <select class="dropdown-toggle" id="select_profile" v-model="profileId">
            <option v-for="(profile, index) in profiles" :key="index" :value="profile.id" @click="updateProfileCampaign(profile.id)">
              {{profile.form_label}}
            </option>
          </select>
          <button @click.prevent="addNewForm()" class="w-inline-block edit-icon">
            <em class="fas fa-plus"></em>
          </button>
          <button @click.prevent="formbuilder()" class="w-inline-block edit-icon" v-if="profileId != null">
            <em class="fas fa-edit"></em>
          </button>
        </div>

        <div class="divider-menu"></div>

        <transition name="slide-right">
          <div class="col-md-12" :class="profileId == null ? 'mt-1' : 'mt-2'">
            <div class="container-menu-funnel">
              <div v-if="profileId == null" style="display: flex;" class="required">
                <em class="fas fa-exclamation-circle icon-warning-margin"></em>
                <p>{{chooseProfileWarning}}</p>
              </div>
              <div v-for="(formCat, index) in formCategories[langue]" :key="index">
                <a @click="profileId != null ? menuHighlight = index : ''"
                   class="menu-item"
                   :class="[(menuHighlight == index ? 'w--current' : ''), (profileId == null ? 'grey-link' : '')]"
                >{{ formCat }}</a>
              </div>
            </div>
          </div>
        </transition>
      </div>

      <div class="col-md-8 p-1" style="padding-left: 2em !important;border-left: solid 1px #cecece;">
        <h2>{{formCategories[langue][menuHighlight]}}</h2>
        <p class="paragraphe-sous-titre">{{funnelDescription[langue][menuHighlight]}}</p>
        <transition name="slide-right">
          <addFormulaire
                  v-if="menuHighlight == 0"
                  :profileId="profileId"
                  :key="formReload"
                  :visibility="null"
          ></addFormulaire>

          <addDocuments
                  v-if="menuHighlight == 1"
                  :funnelCategorie="formCategories[langue][menuHighlight]"
                  :profileId="profileId"
                  :campaignId="campaignId"
                  :langue="langue"
                  :menuHighlight="menuHighlight"
          ></addDocuments>

          <!--          <addEvalEval
                            v-if="menuHighlight == 6"
                            :funnelCategorie="formCategories[langue][menuHighlight]"
                    ></addEvalEval>-->
        </transition>
      </div>
    </div>

    <div
            class="section-sauvegarder-et-continuer-funnel"
            :class="menuHighlight == 5 ? 'big' : menuHighlight == 1 ? 'noShow' : ''"
    >
      <div class="w-container">
        <div class="container-evaluation w-clearfix">
          <a @click="next()" class="bouton-sauvergarder-et-continuer-3">{{ Continuer }}</a>
          <a class="bouton-sauvergarder-et-continuer-3 w-retour" @click="previous()">
            {{
            Retour
            }}
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import moment from "moment";
  import axios from "axios";
  import { Datetime } from "vue-datetime";

  import addFormulaire from "../views/funnelFormulaire/addFormulaire";
  import addDocuments from "../views/funnelFormulaire/addDocuments";
  import addGestionnaires from "../views/funnelFormulaire/addGestionnaires";
  import addEmail from "../views/funnelFormulaire/addEmail";
  import addEvaluation from "../views/funnelFormulaire/addEvaluation";
  import addEvalVisi from "../views/funnelFormulaire/addEvalVisi";
  import addEvalEval from "../views/funnelFormulaire/addEvalEval";
  import addMenu from "../views/funnelFormulaire/addMenu";
  import ModalWarningFormBuilder from "./advancedModals/ModalWarningFormBuilder";

  const qs = require("qs");

  export default {
    name: "addFormNextCampaign",

    components: {
      ModalWarningFormBuilder,
      Datetime,
      addFormulaire,
      addDocuments,
      addGestionnaires,
      addEmail,
      addEvaluation,
      addEvalVisi,
      addEvalEval,
      addMenu
    },

    props: {
      campaignId: Number,
      actualLanguage: String,
      index: Number
    },

    data: () => ({
      prid: "",
      EmitIndex: "0",
      menuHighlight: 0,
      formReload: 0,
      selectedform: "default",
      formList: "",

      profileId: null,
      profiles: [],

      currentElement: "",
      currentSpan: "",

      langue: 0,

      funnelDescription: [
        [
          Joomla.JText._("COM_EMUNDUS_ONBOARD_FORMDESCRIPTION"),
          Joomla.JText._("COM_EMUNDUS_ONBOARD_DOCSDESCRIPTION"),
        ],
        [
          Joomla.JText._("COM_EMUNDUS_ONBOARD_FORMDESCRIPTION"),
          Joomla.JText._("COM_EMUNDUS_ONBOARD_DOCSDESCRIPTION"),
        ]
      ],

      formCategories: [
        [
          "Aperçu du formulaire",
          "Documents"
        ],
        [
          "Form Preview",
          "Documents"
        ]
      ],

      /*formCategories: [
        [
          "Aperçu du formulaire",
          "Documents",
          "Gestionnaires",
          "Email",
          "Évaluations",
          "Visibilité",
          "Évaluateurs",
          "Attribution",
          "Paramètres",
          "Invitation",
          "Paramètres de campagne"
        ],
        [
          "Form Preview",
          "Documents",
          "Managers",
          "Email",
          "Evaluations",
          "Visibility",
          "Evaluators",
          "Attribution",
          "Settings",
          "Invitation",
          "Campaign Settings"
        ]
      ],*/

      form: {
        label: "",
        profile_id: "",
        start_date: "",
        end_date: ""
      },

      From: Joomla.JText._("COM_EMUNDUS_ONBOARD_FROM"),
      To: Joomla.JText._("COM_EMUNDUS_ONBOARD_TO"),
      Since: Joomla.JText._("COM_EMUNDUS_ONBOARD_SINCE"),
      Modify: Joomla.JText._("COM_EMUNDUS_ONBOARD_MODIFY"),
      CAMPAIGN: Joomla.JText._("COM_EMUNDUS_ONBOARD_CAMPAIGN"),
      FORM: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM"),
      ChooseEvaluatorGroup: Joomla.JText._(
              "COM_EMUNDUS_ONBOARD_CHOOSE_EVALUATOR_GROUP"
      ),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      chooseForm: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSE_FORM"),
      chooseProfileWarning: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSE_PROFILE_WARNING"),
    }),

    methods: {
      moment(date) {
        return moment(date);
      },

      next() {
        if (this.menuHighlight < 1) {
          this.menuHighlight++;
        } else {
          window.location.href = '/' + this.actualLanguage + '/configuration/campaigns';
        }
      },

      previous() {
        if (this.menuHighlight > 0) {
          this.menuHighlight--;
        } else {
          window.location.href = '/fr/configuration-campagne';
        }
      },

      formbuilder() {
        axios.get("index.php?option=com_emundus_onboard&controller=form&task=getfilesbyform&pid=" + this.profileId)
                .then(response => {
                  if(response.data.data != 0){
                    this.$modal.show('modalWarningFormBuilder');
                  } else {
                    window.location.replace(
                            "index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=" +
                            this.profileId +
                            "&index=" +
                            this.EmitIndex +
                            "&cid=" +
                            this.campaignId
                    );
                  }
                });
      },

      addNewForm() {
        window.location.replace(
                "index.php?option=com_emundus_onboard&view=form&layout=add&cid=" + this.campaignId
        );
      },

      updateProfileCampaign(profileId){
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=campaign&task=updateprofile",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            profile: profileId,
            campaign: this.campaignId
          })
        }).then(response => {
          this.formReload += 1;
        })
      }
    },

    computed: {
      console: () => console
    },

    created() {
      if (this.actualLanguage == "en") {
        this.langue = 1;
      }

      axios.get(`index.php?option=com_emundus_onboard&controller=campaign&task=getcampaignbyid&id=${this.campaignId}`
      ).then(response => {
        this.form.label = response.data.data.campaign.label;
        this.form.profile_id = response.data.data.campaign.profile_id;
        this.form.start_date = response.data.data.campaign.start_date;
        this.form.end_date = response.data.data.campaign.end_date;
        this.form.start_date = moment(this.form.start_date).format(
                "DD/MM/YYYY"
        );
        if (this.form.end_date == "0000-00-00 00:00:00") {
          this.form.end_date = null;
        } else {
          this.form.end_date = moment(this.form.end_date).format("DD/MM/YYYY");
        }
        axios.get(
                `index.php?option=com_emundus_onboard&controller=form&task=getallformpublished`
        ).then(profiles => {
          this.profiles = profiles.data.data;
          if(this.form.profile_id == null) {
            this.profiles.length != 0 ? this.profileId = this.profiles[0].id : this.profileId = null;
            if(this.profileId != null){
              this.formReload += 1;
              this.updateProfileCampaign(this.profileId)
            }
          } else {
            this.formReload += 1;
            this.profileId = this.form.profile_id;
          }
        });
      }).then(() => {}).catch(e => {
        console.log(e);
      });

      this.menuHighlight = this.index;
    },
  };
</script>

<style>
  .w-col-9 {
    width: 75% !important;
    padding-top: 0 !important;
  }

  .column-menu-main{
    position: relative;
    min-height: 100vh;
  }

  .description-block{
    margin-top: unset;
    margin-bottom: 1em;
  }

  .divider-menu{
    width: 80%;
    margin: 1em;
  }

  .heading-block{
    display: flex;
    align-items: center;
  }

  .heading{
    margin-bottom: 5px;
  }

  .edit-icon{
    margin-left: 10px;
    border-style: solid;
    border-width: 1px;
    border-color: #de6339;
    border-radius: 50%;
    background-color: #de6339;
    color: white;
    height: 32px;
    width: 32px;
    transition: all 0.2s ease-in-out;
  }

  .edit-icon:hover{
    border-color: #de6339;
    background-color: white;
    color: black;
  }

  .container-menu-funnel{
    flex-direction: column;
  }

  .paragraphe-sous-titre{
    margin-bottom: 1em;
    margin-top: 1em;
  }

  .grey-link{
    color: grey;
  }

  .icon-warning-margin{
    margin-top: 2px;
    margin-right: 5px;
  }

  .w-row{
    margin-bottom: 10em;
  }
</style>
