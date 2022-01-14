<!--
  $fnum = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);
  $str = "0001427";
  echo ltrim($str, "0");
-->
<template>
  <div>
    <ModalWarningFormBuilder
        :pid="profileId"
        :cid="campaignId"
    />
    <div class="w-row">
      <div class="col-md-12 col-sm-10 p-1" style="padding-left: 2em !important">
        <div class="section-sub-menu">
          <div class="container-2 w-container" style="max-width: unset">
            <div class="d-flex">
              <img src="/images/emundus/menus/megaphone.svg" srcset="/images/emundus/menus/megaphone.svg" class="tchooz-icon-title" alt="megaphone">
              <h2 class="tchooz-section-titles" v-if="menuHighlight != -1">{{formCategories[langue][menuHighlight]}}</h2>
              <h2 class="tchooz-section-titles" v-if="menuHighlightProg != -1">{{formPrograms[langue][menuHighlightProg]}}</h2>
            </div>

            <p class="tchooz-section-description" v-if="menuHighlight != -1" v-html="formCategoriesDesc[langue][menuHighlight]" style="margin-top: 20px"></p>
            <p class="tchooz-section-description" v-if="menuHighlightProg != -1" v-html="formProgramsDesc[langue][menuHighlightProg]" style="margin-top: 20px"></p>
            <hr>
            <div class="d-flex">
              <p>
                <!-- <b style="color: #16afe1; font-weight: 700 !important;"> {{form.label}}</b>  {{translations.From}} <strong>{{form.start_date}}</strong>   {{translations.To}} <strong>{{form.end_date}}</strong> -->
                <b style="color: #16afe1; font-weight: 700 !important;"> {{form.label}}</b>  {{translations.From}} <strong>{{form.start_date}}</strong>   {{translations.To}} <strong>{{form.end_date}}</strong>
              </p>
            </div>

            <div v-if="profileId == null && loading == false" style="display: flex;" class="d-flex required">
              <em class="fas fa-exclamation-circle icon-warning-margin"></em>
              <p>{{translations.chooseProfileWarning}}</p>
            </div>
            </div>





        </div>
        <!--- start Menu --->
        <div class="d-flex" >
          <ul class="nav nav-tabs topnav">

            <li v-for="(formCat, index) in formCategories[langue]" :key="index" v-show="closeSubmenu">
              <a  @click="profileId != null ? changeToCampMenu(index): ''"
                  class="menu-item"
                  :class="[(menuHighlight == index ? 'w--current' : ''), (profileId == null ? 'grey-link' : '')]">
                {{ formCat }}
              </a>
            </li>

            <li v-for="(formProg, index) in formPrograms[langue]" :key="index" v-show="closeSubmenu">
              <a @click="profileId != null ? changeToProgMenu(index) : ''"
                 class="menu-item"
                 :class="[(menuHighlightProg == index ? 'w--current' : ''), (profileId == null ? 'grey-link' : '')]">
                {{ formProg }}
              </a>

            </li>

          </ul>
        </div>
        <br>


        <!-- end Menu -->

        <div v-if="menuHighlightProg != -1" class="warning-message-program mb-1">
          <p style="color: #e5283b;"><em class="fas fa-exclamation-triangle mr-1 red"></em>{{translations.ProgramWarning}}</p>
          <ul v-if="campaignsByProgram.length > 0">
            <li v-for="(campaign, index) in campaignsByProgram">{{campaign.label}}</li>
          </ul>
        </div>
        <transition name="slide-right">
          <add-campaign
              v-if="menuHighlight == 0"
              :campaign="campaignId"
              :coordinatorAccess="true"
              :actualLanguage="actualLanguage"
              :manyLanguages="manyLanguages"
              @nextSection="menuHighlight++"
              @getInformations="initInformations"
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

<!--          <addDocuments
              v-if="menuHighlight == 4"
              :funnelCategorie="formCategories[langue][menuHighlight]"
              :profileId="profileId"
              :campaignId="campaignId"
              :menuHighlight="menuHighlight"
              :langue="actualLanguage"
              :manyLanguages="manyLanguages"
          ></addDocuments>-->

          <add-documents-dropfiles
              v-if="menuHighlight == 1"
              :funnelCategorie="formCategories[langue][menuHighlight]"
              :profileId="profileId"
              :campaignId="campaignId"
              :menuHighlight="menuHighlight"
              :langue="actualLanguage"
              :manyLanguages="manyLanguages"
          />

<!--          <add-documents-form
              v-if="menuHighlight == 3"
              :funnelCategorie="formCategories[langue][menuHighlight]"
              :profileId="profileId"
              :campaignId="campaignId"
              :menuHighlight="menuHighlight"
              :langue="actualLanguage"
              :manyLanguages="manyLanguages"
          ></add-documents-form>-->

<!--          <add-gestionnaires
              v-if="menuHighlightProg == 0 && program.id != 0"
              :funnelCategorie="formPrograms[langue][menuHighlight]"
              :group="prog_group"
              :coordinatorAccess="true"
          ></add-gestionnaires>-->

          <add-evaluation-grid
              v-if="menuHighlightProg == 0 && program.id != 0"
              :funnelCategorie="formPrograms[langue][menuHighlight]"
              :prog="program.id"
          ></add-evaluation-grid>

          <add-email
              v-if="menuHighlightProg == 1 && program.id != 0"
              :funnelCategorie="formPrograms[langue][menuHighlight]"
              :prog="program.id"
          ></add-email>

          <!--          <addEvalEval
                            v-if="menuHighlight == 6"
                            :funnelCategorie="formCategories[langue][menuHighlight]"
                    ></addEvalEval>-->
        </transition>
      </div>
    </div>
    <div class="loading-form" v-if="loading">
      <Ring-Loader :color="'#12DB42'" />
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
import ModalWarningFormBuilder from "./advancedModals/ModalWarningFormBuilder";
import Tasks from "@/views/tasks";
import AddDocumentsDropfiles from "@/views/funnelFormulaire/addDocumentsDropfiles";
import AddDocumentsForm from "@/views/funnelFormulaire/addDocumentsForm";
import addCampaign from "@/views/addCampaign";
import AddEvaluationGrid from "@/views/funnelFormulaire/addEvaluationGrid";

const qs = require("qs");

export default {
  name: "addFormNextCampaign",

  components: {
    AddEvaluationGrid,
    AddDocumentsForm,
    Tasks,
    AddDocumentsDropfiles,
    addCampaign,
    ModalWarningFormBuilder,
    Datetime,
    addFormulaire,
    addDocuments,
    addGestionnaires,
    addEmail,
    addEvaluation,
    addEvalVisi,
    addEvalEval
  },

  props: {
    campaignId: Number,
    actualLanguage: String,
    index: Number,
    manyLanguages: Number,
  },

  data: () => ({
    prid: "",
    EmitIndex: "0",
    menuHighlight: 0,
    menuHighlightProg: -1,
    formReload: 0,
    selectedform: "default",
    formList: "",
    prog: 0,

    loading: false,

    closeSubmenu: true,

    profileId: null,
    profiles: [],
    campaignsByProgram: [],

    currentElement: "",
    currentSpan: "",

    langue: 0,

    formCategoriesDesc: [
      [
        "Vous pouvez modifier vos dates limites de campagne, vos descriptifs et définir une limite de dossiers",
        "Proposez à vos visiteurs des documents d'informations avant même qu'il se connecte. Ces documents seront disponibles dans la section <em>Plus d'informations</em> de votre campagne",
        "Choississez le formulaire de votre campagne. Nous vous proposons des modèles que vous pouvez modifier à tout moment.",
        "Proposez des documents des documents à vos candidats qui remplissent le formulaire. Cela peut être des modèles que les candidats doivent compléter puis déposer ou simplement des documents complémentaires à certains informations demandées",
        "Ajoutez des types de documents que le candidat doit déposer à la suite du formulaire. Glissez simplement les documents entre les 2 colonnes ou ajouter un nouveau type de document",
      ],
      [
        "You can change your campaign deadlines, your descriptions and set a file limit",
        "Offer your visitors information documents before they even log in. These documents will be available in the <em>More information</em> section of your campaign",
        "Choose the form for your campaign. We offer templates that you can modify at any time.",
        "Offer documents to your applicants when they fill in the form. These can be templates that applicants need to complete and then submit, or simply documents to supplement some of the information requested",
        "Add document types that the applicant should file after the form. Simply drag and drop documents between the 2 columns or add a new document type",
      ]
    ],

    formCategories: [
      [
        "Informations générales",
        "Documents préalables",
        "Formulaire",
      ],
      [
        "Global informations",
        "Preliminary documents",
        "Form",
      ]
    ],

    formPrograms: [
      [
        //"Utilisateurs",
        "Grille d'évaluation",
        "Emails",
      ],
      [
        //"Users",
        "Evaluation grid",
        "Emails",
      ]
    ],

    formProgramsDesc: [
      [
        //"Ajoutez des utilisateurs, affectez-les à des rôles qui va leur donner des droits sur les dossiers.",
        "Définissez une phase d'évaluation en créant une grille avec différents critères.",
        "Configurer des envois d'emails automatique aux changements de statuts de vos différents candidats.",
      ],
      [
        //"Users",
        "Evaluation grid",
        "Emails",
      ]
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

    translations:{
      DATE_FORMAT: Joomla.JText._("DATE_FORMAT_JS_LC2"),
      From: Joomla.JText._("COM_EMUNDUS_ONBOARD_FROM"),
      To: Joomla.JText._("COM_EMUNDUS_ONBOARD_TO"),
      chooseProfileWarning: Joomla.JText._("COM_EMUNDUS_ONBOARD_CHOOSE_PROFILE_WARNING"),
      ProgramWarning: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_WARNING"),
    },
  }),

  methods: {
    initInformations(campaign){
      this.form.label = campaign.label;
      this.form.profile_id = campaign.profile_id;
      this.form.program_id = campaign.progid;

      this.form.start_date = campaign.start_date;
      this.form.end_date = campaign.end_date;
      this.form.start_date = moment(this.form.start_date).format(
          this.translations.DATE_FORMAT
      );
      if (this.form.end_date == "0000-00-00 00:00:00") {
        this.form.end_date = null;
      } else {
        this.form.end_date = moment(this.form.end_date).format(this.translations.DATE_FORMAT);
      }

      axios.get(
          `index.php?option=com_emundus_onboard&controller=form&task=getallformpublished`
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
      });
      setTimeout(() => {
        //this.menuHighlight = this.getCookie('campaign_'+this.campaignId+'_menu')
        if(typeof this.menuHighlight == 'undefined'){
          this.menuHighlight = 0;
        }
      },500);
    },

    changeToProgMenu(index){
      axios.get(`index.php?option=com_emundus_onboard&controller=program&task=getprogrambyid&id=${this.form.program_id}`)
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
              url: "index.php?option=com_emundus_onboard&controller=program&task=getcampaignsbyprogram",
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

    changeToCampMenu(index){
      this.menuHighlight = index;
      this.menuHighlightProg = -1;
    },

    setProfileId(prid){
      this.profileId = prid;
    },

    moment(date) {
      return moment(date);
    },

    next() {
      if (this.menuHighlight < 1) {
        this.menuHighlight++;
      } else {
        this.redirectJRoute('index.php?option=com_emundus_onboard&view=campaign');
      }
    },

    previous() {
      if (this.menuHighlight > 0) {
        this.menuHighlight--;
      } else {
        this.redirectJRoute('index.php?option=com_emundus_onboard&view=campaign');
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

    getCookie(cname) {
      var name = cname + "=";
      var decodedCookie = decodeURIComponent(document.cookie);
      var ca = decodedCookie.split(';');
      for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
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
    console: () => console
  },

  created() {
    this.loading = true;
    if (this.actualLanguage == "en") {
      this.langue = 1;
    }
  },
};
</script>

<style scoped>
.section-sub-menu{
  margin-top: 10px;
  margin-bottom: 30px;
}
.w-container{
  margin-left: 30px;
  max-width: unset;
}
.section-principale{
  margin-left: 30px;
}
.w-container{
  max-width: unset;
}
.topnav  {
  /*background-color: #333;*/
  overflow: hidden;
  margin: 0 auto;
  border-bottom: 1px solid #ddd
}
.w--current{
  border: 1px solid #ddd;
  background-color: white;
  border-bottom-left-radius: unset;
  border-bottom-right-radius: unset;
}



</style>
