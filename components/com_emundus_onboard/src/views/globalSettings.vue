<template>
    <div class="w-row">
      <div class="col-md-2 tchooz-sidebar-menu">
        <transition name="slide-right">
          <div class="col-md-12 tchooz-sidebar-menus">
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

      <div class="col-md-10 col-md-offset-2 p-1" style="padding-left: 2em !important;">
        <div class="d-flex justify-content-between" style="margin-bottom: 10px">
          <div class="d-flex" v-if="menuHighlight != 0 && menuHighlight != 6  && menuHighlight != 7">
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
            <button type="button" v-if="menuHighlight != 0 && menuHighlight != 6  && menuHighlight != 7" @click="saveCurrentPage()" class="bouton-sauvergarder-et-continuer">{{ Save }}</button>
          </div>
        </div>
        <transition name="slide-right">
          <editStyle
              v-if="menuHighlight == 0 && coordinatorAccess != 0"
              @LaunchLoading="updateLoading"
              @StopLoading="updateLoading"
              ref="styling"
          ></editStyle>

          <editHomepage
              v-if="menuHighlight == 1 && coordinatorAccess != 0"
              ref="homepage"
              :actualLanguage="actualLanguage"
          ></editHomepage>

          <editCGV
              v-if="menuHighlight == 2 && coordinatorAccess != 0"
              ref="cgv"
              :actualLanguage="actualLanguage"
          ></editCGV>

          <editFooter
              v-if="menuHighlight == 3 && coordinatorAccess != 0"
              ref="footer"
              :actualLanguage="actualLanguage"
          ></editFooter>

          <editStatus
              v-if="menuHighlight == 4 && coordinatorAccess != 0"
              @LaunchLoading="updateLoading"
              @StopLoading="updateLoading"
              ref="status"
              :actualLanguage="actualLanguage"
              :manyLanguages="manyLanguages"
          ></editStatus>

          <editTags
              v-if="menuHighlight == 5"
              @LaunchLoading="runLoading"
              @StopLoading="stopLoading"
              ref="tags"
          ></editTags>

          <editDatas
                  v-if="menuHighlight == 6 && coordinatorAccess != 0"
                  ref="datas"
                  :actualLanguage="actualLanguage"
                  :manyLanguages="manyLanguages"
          ></editDatas>

          <help-settings
              v-if="menuHighlight == 7"
              ref="help"
              :actualLanguage="actualLanguage"
              :manyLanguages="manyLanguages"
          ></help-settings>
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
</template>

<script>
import axios from "axios";
import editStatus from "../components/Settings/editStatus";
import editTags from "../components/Settings/editTags";
import editHomepage from "../components/Settings/editHomepage";
import editStyle from "../components/Settings/editStyle";
import editDatas from "../components/Settings/editDatas";
import editUsers from "../components/Settings/editUsers";
import editCGV from "../components/Settings/editCGV";
import editFooter from "../components/Settings/editFooter";
import helpSettings from "@/components/Settings/helpSettings";
import Tasks from "@/views/tasks";
import HelpSettings from "@/components/Settings/helpSettings";

const qs = require("qs");

export default {
  name: "globalSettings",

  components: {
    HelpSettings,
    Tasks,
    editStatus,
    editTags,
    editCGV,
    editFooter,
    editHomepage,
    editStyle,
    editDatas,
    editUsers
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

    settingsCategories: [
      [
        "Style",
        "Page d'accueil",
        "Conditions générales",
        "Pied de page",
        "Statuts",
        "Etiquettes",
        "Référentiels de données",
        "Aide"
      ],
      [
        "Styling",
        "Home page",
        "General Terms and Conditions",
        "Footer",
        "Status",
        "Tags",
        "Data repository",
        "Help"
      ]
    ],

    Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
    Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
    Save: Joomla.JText._("COM_EMUNDUS_ONBOARD_SAVE"),
    Saved: Joomla.JText._("COM_EMUNDUS_ONBOARD_SAVED"),
  }),

  methods: {
    updateStatus(status) {
      this.updateLoading();
      axios({
        method: "post",
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=updatestatus',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          status: status
        })
      }).then(() => {
        this.updateLoading();
      });
    },

    updateTags(tags){
      this.updateLoading();
      axios({
        method: "post",
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=updatetags',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          tags: tags
        })
      }).then(() => {
        this.updateLoading();
      });
    },

    updateFooter(content) {
      this.updateLoading();
      axios({
        method: "post",
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=updatefooter',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          content: content
        })
      }).then(() => {
        this.updateLoading();
      });
    },

    updateHomepage(content) {
      this.updateLoading();
      axios({
        method: "post",
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=updatehomepage',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          content: content
        })
      }).then(() => {
        this.updateLoading();
      });
    },

    updateCgv(content) {
      this.updateLoading();
      axios({
        method: "post",
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=updatecgv',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          content: content
        })
      }).then(() => {
        this.updateLoading();
      });
    },

    saveCurrentPage() {
      switch (this.menuHighlight) {
        case 1:
          this.updateHomepage(this.$refs.homepage.$data.form.content);
          break;
        case 2:
          this.updateCgv(this.$refs.cgv.$data.form.content);
          break;
        case 3:
          this.updateFooter(this.$refs.footer.$data.form.content);
          break;
        case 4:
          this.updateStatus(this.$refs.status.$data.status);
          break;
        case 5:
          this.updateTags(this.$refs.tags.$data.tags);
          break;
      }
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
    this.$nextTick(function () {
      window.setInterval(() => {
        this.saveCurrentPage();
      },20000);
    })
  },
};
</script>

<style scoped>
.fa-check{
  width: 40px;
  font-size: 25px;
  color: green;
}
.bouton-sauvergarder-et-continuer{
  position: absolute;
  right: 10%;
  top: 7%;
}
</style>
