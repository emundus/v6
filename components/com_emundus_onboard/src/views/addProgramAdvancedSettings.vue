<!--
  $fnum = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);
  $str = "0001427";
  echo ltrim($str, "0");
-->
<template>
  <div class="column-menu-main w-row" style="margin-top: 120px">
    <div class="w-row">
      <div class="col-md-4 p-1">
        <div class="mb-1">
          <h1 class="nom-campagne">{{ Program }} : {{ program.label }}</h1>
          <div class="prog-info">
            <div>
              <p>
                {{ Code }} : {{ program.code }}
              </p>
              <p>
                {{ Category }} : {{ program.programmes }}
              </p>
            </div>
            <a :href="'index.php?option=com_emundus_onboard&view=program&layout=add&pid=' + prog"
                    class="modifier-la-campagne">
              <button class="w-inline-block edit-icon">
                <em class="fas fa-edit"></em>
              </button>
            </a>
          </div>
        </div>

        <div class="divider-menu"></div>

        <transition name="slide-right">
          <div class="col-md-12 mt-2">
            <div class="container-menu-funnel">
              <div v-for="(progCat, index) in progCategories[langue]" :key="index">
                <a @click="menuHighlight = index"
                   class="menu-item"
                   :class="menuHighlight == index ? 'w--current' : ''"
                >{{ progCat }}</a>
              </div>
            </div>
          </div>
        </transition>
      </div>

      <div class="col-md-8 p-1" style="padding-left: 2em !important;">
        <h2>{{progCategories[langue][menuHighlight]}}</h2>
        <p class="paragraphe-sous-titre">{{funnelDescription[langue][menuHighlight]}}</p>
        <transition name="slide-right">
          <addGestionnaires
                  v-if="menuHighlight == 0"
                  :funnelCategorie="progCategories[langue][menuHighlight]"
                  :group="this.prog_group"
                  :coordinatorAccess="coordinatorAccess"
          ></addGestionnaires>

          <addEvaluationGrid
                  v-if="menuHighlight == 1"
                  :funnelCategorie="progCategories[langue][menuHighlight]"
                  :prog="this.prog"
          ></addEvaluationGrid>

          <addEvalVisi
                  v-if="menuHighlight == 2"
                  :funnelCategorie="progCategories[langue][menuHighlight]"
                  :prog="this.prog"
          ></addEvalVisi>

          <addEmail
                  v-if="menuHighlight == 3"
                  :funnelCategorie="progCategories[langue][menuHighlight]"
                  :prog="this.prog"
          ></addEmail>
        </transition>
      </div>
    </div>

    <div
            class="section-sauvegarder-et-continuer-funnel"
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

  import addGestionnaires from "../views/funnelFormulaire/addGestionnaires";
  import addEmail from "../views/funnelFormulaire/addEmail";
  import addEvalVisi from "../views/funnelFormulaire/addEvalVisi";
  import addEvalEval from "../views/funnelFormulaire/addEvalEval";
  import addEvaluationGrid from "./funnelFormulaire/addEvaluationGrid";

  import ModalAddUser from "./advancedModals/ModalAddUser";

  const qs = require("qs");

  export default {
    name: "addProgramAdvancedSettings",

    components: {
      Datetime,
      addGestionnaires,
      addEmail,
      addEvalVisi,
      addEvalEval,
      addEvaluationGrid,
      ModalAddUser
    },

    props: {
      prog: Number,
      actualLanguage: String,
      coordinatorAccess: Number,
    },

    data: () => ({
      menuHighlight: null,

      dynamicComponent: false,
      categories: [],
      cats: [],
      prog_group: null,

      langue: 0,

      funnelDescription: [
        [
          Joomla.JText._("COM_EMUNDUS_ONBOARD_USERSDESCRIPTION"),
          Joomla.JText._("COM_EMUNDUS_ONBOARD_EVALUATIONGRIDDESCRIPTION"),
          Joomla.JText._("COM_EMUNDUS_ONBOARD_VISIBILITYDESCRIPTION"),
          Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAILSDESCRIPTION"),
        ],
        [
          Joomla.JText._("COM_EMUNDUS_ONBOARD_USERSDESCRIPTION"),
          Joomla.JText._("COM_EMUNDUS_ONBOARD_EVALUATIONGRIDDESCRIPTION"),
          Joomla.JText._("COM_EMUNDUS_ONBOARD_VISIBILITYDESCRIPTION"),
          Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAILSDESCRIPTION"),
        ]
      ],

      progCategories: [
        [
          "Utilisateurs",
          "Grille d'évaluation",
          "Visibilité",
          "Emails",
        ],
        [
          "Users",
          "Evaluation grid",
          "Visibility",
          "Emails",
        ]
      ],

      program: {
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

      From: Joomla.JText._("COM_EMUNDUS_ONBOARD_FROM"),
      To: Joomla.JText._("COM_EMUNDUS_ONBOARD_TO"),
      Since: Joomla.JText._("COM_EMUNDUS_ONBOARD_SINCE"),
      Modify: Joomla.JText._("COM_EMUNDUS_ONBOARD_MODIFY"),
      Program: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM"),
      Code: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_CODE"),
      Category: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_CATEGORY"),
      FORM: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM"),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
    }),

    methods: {
      moment(date) {
        return moment(date);
      },

      next() {
        if (this.menuHighlight < 3) {
          this.menuHighlight++;
        } else {
          window.location.href = '/fr/configuration-programme';
        }
      },

      previous() {
        if (this.menuHighlight > 0) {
          this.menuHighlight--;
        } else {
          window.location.href = '/fr/configuration-programme';
        }
      },

      getCampaignsByProgram(){
        axios.get("index.php?option=com_emundus_onboard&controller=campaign&task=getcampaignsbyprogram&pid=" + this.prog)
                .then(response => {
                  this.campaigns = response.data.data;
                });
      },
    },

    computed: {
      console: () => console
    },

    created() {
      if (this.actualLanguage == "en") {
        this.langue = 1;
      }

      axios.get("index.php?option=com_emundus_onboard&controller=program&task=getprogramcategories")
              .then(response => {
                this.categories = response.data.data;
                for (var i = 0; i < this.categories.length; i++) {
                  this.cats.push(this.categories[i]);
                }
                if (this.prog !== "") {
                  axios.get(`index.php?option=com_emundus_onboard&controller=program&task=getprogrambyid&id=${this.prog}`)
                          .then(rep => {
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
                            this.dynamicComponent = true;
                            this.prog_group = rep.data.data.group;
                            this.menuHighlight = 0;
                          }).catch(e => {
                            console.log(e);
                          });
                } else {
                  this.dynamicComponent = true;
                }
              }).catch(e => {
                console.log(e);
              });

      this.getCampaignsByProgram();
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

  .prog-info{
    display: flex;
    align-items: center;
  }

  .modifier-la-campagne{
    margin-left: 3em;
  }

  .w-row{
    margin-bottom: 10%;
  }

  .grey-link{
    color: grey;
  }
</style>
