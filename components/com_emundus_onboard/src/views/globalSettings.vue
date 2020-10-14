<template>
  <div class="column-menu-main w-row" style="margin-top: 120px">
    <div class="w-row">
      <div class="col-md-2 p-1">
        <transition name="slide-right">
          <div class="col-md-12 mt-2">
            <div class="container-menu-funnel">
              <div v-for="(settingsCat, index) in settingsCategories[langue]" :key="index">
                <a @click="menuHighlight = index"
                   class="menu-item"
                   :class="menuHighlight == index ? 'w--current' : ''"
                >{{ settingsCat }}</a>
              </div>
            </div>
          </div>
        </transition>
      </div>

      <div class="col-md-10 p-1" style="padding-left: 2em !important;">
        <div class="d-flex justify-content-between">
          <h2 class="mb-0">{{settingsCategories[langue][menuHighlight]}}</h2>
          <div class="d-flex" v-if="menuHighlight == 0">
            <transition name="slide-right">
              <div class="loading-form-save" v-if="saving">
                <Ring-Loader :color="'#de6339'" />
              </div>
            </transition>
            <transition name="slide-right">
              <div v-if="endSaving" class="d-flex">
                <i class="fas fa-check"></i><span class="mr-1">{{Saved}}</span>
              </div>
            </transition>
            <button type="button" @click="savePage()" class="bouton-sauvergarder-et-continuer">{{ Save }}</button>
          </div>
        </div>
        <p class="paragraphe-sous-titre">{{funnelDescription[langue][menuHighlight]}}</p>
        <transition name="slide-right">
          <customization
                  v-if="menuHighlight == 0"
                  @updateLoading="updateLoading"
                  :actualLanguage="actualLanguage"
                  :manyLanguages="manyLanguages"
                  ref="customization"
          ></customization>

          <!--<editUsers
                  v-if="menuHighlight == 1 && coordinatorAccess != 0"
                  ref="users"
          ></editUsers>-->

          <editDatas
                  v-if="menuHighlight == 1 && coordinatorAccess != 0"
                  ref="datas"
                  :actualLanguage="actualLanguage"
                  :manyLanguages="manyLanguages"
          ></editDatas>
        </transition>
      </div>
    </div>
    <tasks></tasks>

    <!--<div
            class="section-sauvegarder-et-continuer-funnel"
    >
      <div class="w-container">
        <div class="container-evaluation w-clearfix">
          <a @click="next()" class="bouton-sauvergarder-et-continuer-3">{{ Continuer }}</a>
          <a class="bouton-sauvergarder-et-continuer-3 w-retour" @click="previous()">
            {{Retour}}
          </a>
        </div>
      </div>
    </div>-->
  </div>
</template>

<script>
import axios from "axios";
import editStatus from "../components/Settings/editStatus";
import editTags from "../components/Settings/editTags";
import editHomepage from "../components/Settings/editHomepage";
import editStyle from "../components/Settings/editStyle";
import editDatas from "../components/Settings/editDatas";
import editUsers from "../components/Settings/editUsers";
import customization from "../components/Settings/Customization"
import Tasks from "@/views/tasks";

const qs = require("qs");

export default {
  name: "globalSettings",

  components: {
    Tasks,
    editStatus,
    editTags,
    editHomepage,
    editStyle,
    editDatas,
    editUsers,
    customization
  },

  props: {
    actualLanguage: String,
    coordinatorAccess: Number,
    manyLanguages: Number
  },

  data: () => ({
    menuHighlight: 0,
    langue: 0,
    saving: false,
    endSaving: false,

    funnelDescription: [
      [
        '',
        '',
        Joomla.JText._("COM_EMUNDUS_ONBOARD_USERSDESCRIPTIONSETTINGS"),
      ],
      [
        '',
        '',
        Joomla.JText._("COM_EMUNDUS_ONBOARD_USERSDESCRIPTIONSETTINGS"),
      ]
    ],

    settingsCategories: [
      [
        "Personnalisation",
        "Référentiels de données",
      ],
      [
        "Styling",
        "Data repository",
      ]
    ],

    Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
    Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
    Save: Joomla.JText._("COM_EMUNDUS_ONBOARD_SAVE"),
    Saved: Joomla.JText._("COM_EMUNDUS_ONBOARD_SAVED"),
  }),

  methods: {
    savePage() {
      this.$refs.customization.saveCurrentPage();
    },

    updateLoading(run) {
      this.saving = run;
      if(this.saving === false){
        setTimeout(() => {
          this.endSaving = true;
        },500)
      }
      setTimeout(() => {
        this.endSaving = false;
      },3000);
    }
  },

  created() {
    if (this.actualLanguage == "en") {
      this.langue = 1;
    }
  },
};
</script>

<style scoped>
.fa-check{
  width: 40px;
  font-size: 25px;
  color: green;
}
</style>
