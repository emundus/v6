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
        <p class="paragraphe-sous-titre">{{funnelDescription[langue][menuHighlight]}}</p>
        <transition name="slide-right">
          <editStyle
                  v-if="menuHighlight == 0 && coordinatorAccess != 0"
                  ref="styling"
          ></editStyle>

          <editHomepage
                  v-if="menuHighlight == 1 && coordinatorAccess != 0"
                  ref="homepage"
                  :actualLanguage="actualLanguage"
          ></editHomepage>

          <editStatus
                  v-if="menuHighlight == 2 && coordinatorAccess != 0"
                  ref="datas"
          ></editStatus>

          <editTags
                  v-if="menuHighlight == 3"
                  ref="tags"
          ></editTags>
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
            {{Retour}}
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import editStatus from "./editStatus";
import editTags from "./editTags";
import editHomepage from "./editHomepage";
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

    funnelDescription: [
      [
        '',
        Joomla.JText._("COM_EMUNDUS_ONBOARD_HOMEDESCRIPTION"),
        Joomla.JText._("COM_EMUNDUS_ONBOARD_STATUSDESCRIPTION"),
        Joomla.JText._("COM_EMUNDUS_ONBOARD_TAGSDESCRIPTION")
      ],
      [
        '',
        Joomla.JText._("COM_EMUNDUS_ONBOARD_HOMEDESCRIPTION"),
        Joomla.JText._("COM_EMUNDUS_ONBOARD_STATUSDESCRIPTION"),
        Joomla.JText._("COM_EMUNDUS_ONBOARD_TAGSDESCRIPTION")
      ]
    ],

    settingsCategories: [
      [
        "Style",
        "Page d'accueil",
        "Statuts",
        "Etiquettes"
      ],
      [
        "Styling",
        "Home page",
        "Status",
        "Tags"
      ]
    ],

    Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
    Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
  }),

  methods: {
    next() {
      if (this.menuHighlight == 1) {
        this.updateHomepage(this.$refs.homepage.$data.form.content);
      } else if (this.menuHighlight == 2) {
        this.updateStatus(this.$refs.datas.$data.status);
      } else if (this.menuHighlight == 3) {
        this.updateTags(this.$refs.tags.$data.tags);
      } else if (this.menuHighlight == 5) {
        this.history.go(-1);
      }
      this.menuHighlight++;
    },

    updateStatus(status) {
      axios({
        method: "post",
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=updatestatus',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          status: status
        })
      }).then(() => {});
    },

    updateTags(tags){
      axios({
        method: "post",
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=updatetags',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          tags: tags
        })
      }).then(() => {});
    },

    updateHomepage(content) {
      axios({
        method: "post",
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=updatehomepage',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          content: content
        })
      }).then(() => {});
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
  },
};
</script>

<style scoped>
</style>
