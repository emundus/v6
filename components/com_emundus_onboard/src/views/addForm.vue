<!--
  $fnum = date('YmdHis').str_pad($campaign_id, 7, '0', STR_PAD_LEFT).str_pad($user->id, 7, '0', STR_PAD_LEFT);
  $str = "0001427";
  echo ltrim($str, "0");
-->
<template>
  <div class="column-menu-main w-row">
    <div class="column-menu w-col w-col-3">
      <div class="container-menu-funnel">
        <h1 class="nom-campagne">{{ CAMPAIGN }} : {{ form.label }}</h1>
        <div class="date-menu orange">
          {{ form.end_date != null ? From : Since + " " }}
          {{ form.start_date }}
          {{ form.end_date != null ? To + " " + form.end_date : "" }}
        </div>
        <a
          :href="
            'index.php?option=com_emundus_onboard&view=campaign&layout=add&cid=' +
              this.formulaireEmundus
          "
          class="modifier-la-campagne"
          >{{ Modify }}</a
        >
        <div class="divider-menu"></div>
        <div v-for="(formCat, index) in formCategories[langue]" :key="index">
          <a
            v-if="index < 4"
            @click="menuHighlight = index"
            class="menu-item"
            :class="menuHighlight == index ? 'w--current' : ''"
            >{{ formCat }}</a
          >
          <a
            v-if="index == 4"
            @click="menuHighlight = index"
            class="menu-item eval"
            :class="menuHighlight == index ? 'w--current' : ''"
            >{{ formCat }}</a
          >
          <a
            v-if="index > 4 && index < 10"
            @click="menuHighlight = index"
            class="link-2"
            :class="menuHighlight == index ? 'w--current' : ''"
            >{{ formCat }}</a
          >
          <a
            v-if="index == 10"
            @click="menuHighlight = index"
            class="menu-item parametres"
            :class="menuHighlight == index ? 'w--current' : ''"
            >{{ formCat }}</a
          >
        </div>
      </div>
    </div>
    <div class="column-funnel w-col w-col-9 container-fluid">
      <div class="container-in-funnel w-container">
        <addFormulaire
          v-if="menuHighlight == 0"
          :funnelCategorie="funnelCategories[langue][menuHighlight]"
          :profileId="profileId"
        ></addFormulaire>

        <addDocuments
          v-if="menuHighlight == 1"
          :funnelCategorie="funnelCategories[langue][menuHighlight]"
          :profileId="profileId"
          :langue="langue"
        ></addDocuments>

        <addGestionnaires
          v-if="menuHighlight == 2"
          :funnelCategorie="funnelCategories[langue][menuHighlight]"
          :formulaireEmundus="formulaireEmundus"
        ></addGestionnaires>

        <addEmail
          v-if="menuHighlight == 3"
          :funnelCategorie="funnelCategories[langue][menuHighlight]"
        ></addEmail>

        <addEvaluation
          v-if="menuHighlight == 4"
          :funnelCategorie="funnelCategories[langue][menuHighlight]"
          :form="form"
        ></addEvaluation>

        <addEvalVisi
          v-if="menuHighlight == 5"
          :funnelCategorie="funnelCategories[langue][menuHighlight]"
        ></addEvalVisi>

        <addEvalEval
          v-if="menuHighlight == 6"
          :funnelCategorie="funnelCategories[langue][menuHighlight]"
        ></addEvalEval>

        <addMenu v-if="menuHighlight == 7" :profileId="profileId"></addMenu>
      </div>
    </div>

    <div
      class="section-sauvegarder-et-continuer-funnel"
      :class="menuHighlight == 5 ? 'big' : menuHighlight == 1 ? 'noShow' : ''"
    >
      <div class="w-container">
        <div class="container-evaluation w-clearfix">
          <a @click="next()" class="bouton-sauvergarder-et-continuer-3">{{ Continuer }}</a>
          <a class="bouton-sauvergarder-et-continuer-3 w-retour" @click="previous()">{{
            Retour
          }}</a>
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

const qs = require("qs");

export default {
  name: "addForm",

  components: {
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
    formulaireEmundus: Number,
    actualLanguage: String
  },

  data: () => ({
    prid: "",
    EmitIndex: "0",
    menuHighlight: 0,
    selectedform: "default",
    formList: "",

    profileId: 0,

    currentElement: "",
    currentSpan: "",

    langue: 0,

    funnelCategories: [
      [
        "Formulaire",
        "Documents",
        "Gestionnaires",
        "Email",
        ["Période d'évaluation", "Grille d'évaluation"],
        ["Visibilité", "Informations générales", "Informations personnelles"],
        ["Groupe d'évaluateurs", "Évaluateurs"],
        "Attribution",
        "Paramètres",
        "Invitation",
        "Paramètres de camapgne"
      ],
      [
        "Form",
        "Documents",
        "Managers",
        "Email",
        ["Evaluation period", "Evaluation grid"],
        ["Visibility", "General Information", "Personal Information"],
        ["Panel of Evaluators", "Evaluators"],
        "Attribution",
        "Settings",
        "Invitation",
        "Camapign settings"
      ]
    ],

    formCategories: [
      [
        "Formulaire",
        "Documents",
        "Gestionnaires",
        "Email",
        "Évaluations",
        "Visibilité",
        "Évaluateurs",
        "Attribution",
        "Paramètres",
        "Invitation",
        "Paramètres de camapgne"
      ],
      [
        "Form",
        "Documents",
        "Managers",
        "Email",
        "Evaluations",
        "Visibility",
        "Evaluators",
        "Attribution",
        "Settings",
        "Invitation",
        "Camapign Settings"
      ]
    ],

    form: {
      label: "",
      start_date: "",
      end_date: ""
    },

    From: Joomla.JText._("COM_EMUNDUSONBOARD_FROM"),
    To: Joomla.JText._("COM_EMUNDUSONBOARD_TO"),
    Since: Joomla.JText._("COM_EMUNDUSONBOARD_SINCE"),
    Modify: Joomla.JText._("COM_EMUNDUSONBOARD_MODIFY"),
    CAMPAIGN: Joomla.JText._("COM_EMUNDUSONBOARD_CAMPAIGN"),
    ChooseEvaluatorGroup: Joomla.JText._("COM_EMUNDUSONBOARD_CHOOSE_EVALUATOR_GROUP"),
    Retour: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_RETOUR"),
    Continuer: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_CONTINUER")
  }),

  methods: {
    moment(date) {
      return moment(date);
    },

    next() {
      if (this.menuHighlight < 10) {
        this.menuHighlight++;
      } else {
        window.location.replace("campaigns");
      }
    },

    previous() {
      if (this.menuHighlight > 0) {
        this.menuHighlight--;
      } else {
        window.location.replace(
          "index.php?option=com_emundus_onboard&view=campaign&layout=add&cid=" +
            this.formulaireEmundus
        );
      }
    }
  },

  computed: {
    console: () => console
  },

  created() {
    if (this.actualLanguage == "en") {
      this.langue = 1;
    }

    axios
      .get(
        `index.php?option=com_emundus_onboard&controller=campaign&task=getcampaignbyid&id=${this.formulaireEmundus}`
      )
      .then(response => {
        this.form.label = response.data.data.label;
        this.form.start_date = response.data.data.start_date;
        this.form.end_date = response.data.data.end_date;
        this.form.start_date = moment(this.form.start_date).format("DD/MM/YYYY");
        if (this.form.end_date == "0000-00-00 00:00:00") {
          this.form.end_date = null;
        } else {
          this.form.end_date = moment(this.form.end_date).format("DD/MM/YYYY");
        }
        this.profileId = response.data.data.profile_id;
      })
      .then(() => {})
      .catch(e => {
        console.log(e);
      });
  },

  mounted() {
    var formulaireEmundus = this.formulaireEmundus;
    var fid = this.formulaireEmundus;

    jQuery(document).ready(function($) {
      if (window.history && window.history.pushState) {
        window.history.pushState(
          "forward",
          null,
          "index.php?option=com_emundus_onboard&view=form&layout=add&fid=" + fid + "#forward"
        );

        $(window).on("popstate", function() {
          window.location.replace(
            "index.php?option=com_emundus_onboard&view=campaign&layout=add&cid=" + formulaireEmundus
          );
        });
      }
    });
  }
};
</script>

<style>
.span12 {
  width: 900px;
}
</style>
