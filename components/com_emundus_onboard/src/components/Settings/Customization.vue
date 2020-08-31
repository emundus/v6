<template>
  <div class="container-evaluation" style="width: auto">
    <transition name="slide-right">
      <ul class="menus-row">
        <li class="MenuForm" v-for="(settingsCat, index) in settingsCategories[langue]" :key="index">
          <a @click="menuHighlight = index"
             class="MenuFormItem"
             :class="menuHighlight == index ? 'w--current' : ''"
          >{{ settingsCat }}</a>
        </li>
      </ul>
    </transition>
    <div class="w-row">
      <div class="col-md-10 p-1" style="padding-left: 2em !important;">
        <transition name="slide-right">
          <editStyle
                  v-if="menuHighlight == 0 && coordinatorAccess != 0"
                  @LaunchLoading="runLoading"
                  @StopLoading="stopLoading"
                  ref="styling"
          ></editStyle>

          <editHomepage
                  v-if="menuHighlight == 1 && coordinatorAccess != 0"
                  ref="homepage"
                  :actualLanguage="actualLanguage"
          ></editHomepage>

          <editFooter
              v-if="menuHighlight == 2 && coordinatorAccess != 0"
              ref="footer"
              :actualLanguage="actualLanguage"
          ></editFooter>

          <editStatus
                  v-if="menuHighlight == 3 && coordinatorAccess != 0"
                  @LaunchLoading="runLoading"
                  @StopLoading="stopLoading"
                  ref="status"
          ></editStatus>

          <editTags
                  v-if="menuHighlight == 4"
                  @LaunchLoading="runLoading"
                  @StopLoading="stopLoading"
                  ref="tags"
          ></editTags>
        </transition>
      </div>
    </div>

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
import editStatus from "./editStatus";
import editTags from "./editTags";
import editHomepage from "./editHomepage";
import editFooter from "./editFooter";
import editStyle from "./editStyle";
import editDatas from "./editDatas";
import editUsers from "./editUsers";

const qs = require("qs");

export default {
  name: "Customization",

  components: {
    editStatus,
    editTags,
    editHomepage,
    editFooter,
    editStyle,
    editDatas,
    editUsers
  },

  props: {
    actualLanguage: String,
    coordinatorAccess: Number
  },

  data: () => ({
    menuHighlight: 0,
    langue: 0,

    settingsCategories: [
      [
        "Style",
        "Page d'accueil",
        "Pied de page",
        "Statuts",
        "Etiquettes"
      ],
      [
        "Styling",
        "Home page",
        "Footer",
        "Status",
        "Tags"
      ]
    ],

    Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
    Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
  }),

  methods: {
    next() {
      this.menuHighlight++;
    },

    updateStatus(status) {
      this.runLoading();
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
        this.stopLoading();
      });
    },

    updateTags(tags){
      this.runLoading();
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
        this.stopLoading();
      });
    },

    updateFooter(content) {
      this.runLoading();
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
        this.stopLoading();
      });
    },

    updateHomepage(content) {
      this.runLoading();
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
        this.stopLoading();
      });
    },

    saveCurrentPage() {
      if(this.menuHighlight === 1) {
        this.updateHomepage(this.$refs.homepage.$data.form.content);
      } else if (this.menuHighlight === 2) {
        this.updateFooter(this.$refs.footer.$data.form.content);
      } else if (this.menuHighlight === 3) {
        this.updateStatus(this.$refs.status.$data.status);
      } else if (this.menuHighlight === 4) {
        this.updateTags(this.$refs.tags.$data.tags);
      }
    },

    runLoading() {
      this.$emit("updateLoading",true);
    },
    stopLoading() {
      this.$emit("updateLoading",false);
    },

    previous() {
      if (this.menuHighlight > 0) {
        this.menuHighlight--;
      } else {
        history.go(-1);
      }
    },
  },

  created() {
    if (this.actualLanguage == "en") {
      this.langue = 1;
    }
    if(this.coordinatorAccess == 0){
      this.menuHighlight = 3;
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
</style>
