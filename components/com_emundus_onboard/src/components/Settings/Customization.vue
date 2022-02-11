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
                  @LaunchLoading="runLoading"
                  @StopLoading="stopLoading"
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
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import editStatus from "./editStatus";
import editTags from "./editTags";
import editHomepage from "./Content/editHomepage";
import editFooter from "./Content/editFooter";
import editStyle from "./editStyle";
import editDatas from "./editDatas";
import editUsers from "./editUsers";
import editCGV from "@/components/Settings/editCGV";

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
    editUsers,
    editCGV
  },

  props: {
    actualLanguage: String,
    coordinatorAccess: Number,
    manyLanguages: Number,
  },

  data: () => ({
    menuHighlight: 0,
    langue: 0,

    settingsCategories: [
      [
        "Style",
        "Page d'accueil",
        "Conditions générales",
        "Pied de page",
        "Statuts",
        "Etiquettes"
      ],
      [
        "Styling",
        "Home page",
        "General Terms and Conditions",
        "Footer",
        "Status",
        "Tags"
      ]
    ],

    Retour: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
    Continuer: this.translate("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
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

    updateCgv(content) {
      this.runLoading();
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
        this.stopLoading();
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
