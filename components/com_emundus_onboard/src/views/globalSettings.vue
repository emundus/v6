<template>
    <div class="w-row">

      <div class="col-md-12 p-1" style="padding-left: 2em !important;">
        <!--- start Menu --->
        <div class="d-flex" >
          <ul class="nav nav-tabs topnav">

            <li v-for="(settingsCat, index) in settingsCategories[actualLanguage]" :key="index">
              <a @click="menuHighlight = index"
                 class="menu-item"
                 :class="menuHighlight == index ? 'w--current' : ''"
              >{{ settingsCat }}</a>
            </li>


          </ul>
          <br>
        </div>

        <div class="d-flex justify-content-between" style="margin-bottom: 10px">
          <div class="d-flex" style="width: 100%;justify-content: end;margin-bottom: -90px;" v-if="menuHighlight != 0 && menuHighlight != 7  && menuHighlight != 8">
            <transition name="slide-right">
              <div class="loading-form-save" v-if="saving">
                <Ring-Loader :color="'#12DB42'" />
              </div>
            </transition>
            <transition name="slide-right">
              <div class="loading-form-save d-flex" v-if="endSaving">
                <i class="fas fa-check"></i><span class="mr-1">{{Saved}}</span>
              </div>
            </transition>
            <button type="button" v-if="menuHighlight != 0 && menuHighlight != 7" @click="saveCurrentPage()" class="bouton-sauvergarder-et-continuer" :style="'right: 10%'">{{ Save }}</button>
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
              :manyLanguages="manyLanguages"
          ></editHomepage>

          <editCGV
              v-if="menuHighlight == 2 && coordinatorAccess != 0"
              ref="cgv"
              :actualLanguage="actualLanguage"
              :manyLanguages="manyLanguages"
          ></editCGV>

          <editFooter
              v-if="menuHighlight == 3 && coordinatorAccess != 0"
              ref="footer"
              :actualLanguage="actualLanguage"
              :manyLanguages="manyLanguages"
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
              @LaunchLoading="updateLoading"
              @StopLoading="updateLoading"
              ref="tags"
          ></editTags>

          <edit-applicants
              v-if="menuHighlight == 6"
              @LaunchLoading="updateLoading"
              @StopLoading="updateLoading"
              ref="applicants"
          ></edit-applicants>

          <editDatas
                  v-if="menuHighlight == 7 && coordinatorAccess != 0"
                  ref="datas"
                  :actualLanguage="actualLanguage"
                  :manyLanguages="manyLanguages"
          ></editDatas>
        </transition>
      </div>
    </div>
</template>

<script>
import axios from "axios";
import editStatus from "../components/Settings/editStatus";
import editTags from "../components/Settings/editTags";
import editHomepage from "../components/Settings/editHomepage";
import editStyle from "../components/Settings/editStyle";
import editDatas from "../components/Settings/editDatas";
import editCGV from "../components/Settings/editCGV";
import editFooter from "../components/Settings/editFooter";
import EditApplicants from "@/components/Settings/editApplicants";
import { global } from "../store/global";

const qs = require("qs");

export default {
  name: "globalSettings",

  components: {
    EditApplicants,
    editStatus,
    editTags,
    editCGV,
    editFooter,
    editHomepage,
    editStyle,
    editDatas,
  },

  data: () => ({
    menuHighlight: 0,
    langue: 0,
    saving: false,
    endSaving: false,
    actualLanguage: "",
    coordinatorAccess: 0,
    manyLanguages: 0,
    settingsCategories: {
      "fr": [
        "Styles",
        "Page d'accueil",
        "Conditions générales de vente",
        "Pied de page",
        "Statuts",
        "Tags",
        "Candidats",
        "Données",
      ],
      "en": [
        "Styles",
        "Homepage",
        "Terms and conditions",
        "Footer",
        "Statuts",
        "Tags",
        "Applicants",
        "Datas",
      ],
      
    },
    Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
    Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
    Save: Joomla.JText._("COM_EMUNDUS_ONBOARD_SAVE"),
    Saved: Joomla.JText._("COM_EMUNDUS_ONBOARD_SAVED"),
    Settings: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_PARAMETER"),
  }),
  created() {
    this.actualLanguage = global.getters.actualLanguage;
    console.log(this.actualLanguage);
    this.manyLanguages = Number(global.getters.manyLanguages);
    this.coordinatorAccess = global.getters.coordinatorAccess;
  },
  methods: {
    updateStatus(status) {
      this.updateLoading(true);
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
        this.updateLoading(false);
      });
    },

    updateTags(tags){
      this.updateLoading(true);
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
        this.updateLoading(false);
      });
    },

    updateFooter(content) {
      this.updateLoading(true);
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
        this.updateLoading(false);
      });
    },

    updateHomepage(content,label,color) {
      this.updateLoading(true);
      axios({
        method: "post",
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=updatehomepage',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          content: content,
          label: label,
          color: color
        })
      }).then(() => {
        this.updateLoading(false);
      });
    },

    updateCgv(content) {
      this.updateLoading(true);
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
        this.updateLoading(false);
      });
    },

    saveCurrentPage() {
      switch (this.menuHighlight) {
        case 1:
          this.updateHomepage(this.$refs.homepage.$data.form.content,this.$refs.homepage.$data.form.label,this.$refs.homepage.$data.form.titleColor);
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
        case 6:
          this.updateLoading(true);
          setTimeout(() => {
            this.updateLoading(false);
          },500);
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
};
</script>

<style scoped>
.fa-check{
  width: 40px;
  font-size: 25px;
  color: #12DB42;
}
.bouton-sauvergarder-et-continuer,.loading-form-save{
  position: absolute;
  z-index: 10;
  width: auto;
  margin-top: -33px;
  margin-right: 20px;
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
