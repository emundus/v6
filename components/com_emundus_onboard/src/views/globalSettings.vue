<template>
    <div class="w-row em-mt-80">
      <div class="w-100">

        <!-- HEADER -->
        <div class="em-flex-row em-flex-start em-pointer em-m-24" v-if="menuHighlight !== 0 && menuHighlight !== 9 && menuHighlight !== 2" style="margin-left: 10%" @click="menuHighlight = 0">
          <span class="material-icons-outlined">arrow_back</span><span class="em-ml-8">{{ translate('COM_EMUNDUS_ONBOARD_ADD_RETOUR') }}</span>
        </div>
        <h5 class="em-h5 em-m-24" v-if="menuHighlight === 0 && !modal_ready" style="margin-left: 10%">{{ translate("COM_EMUNDUS_ONBOARD_ADDCAMP_PARAMETER") }}</h5>
        <h5 class="em-h5 em-m-24" v-else-if="menuHighlight !== 0 && menuHighlight !== 9 && menuHighlight !== 2" style="margin-left: 10%">{{ translate(currentTitle) }}</h5>

        <!--- MENU --->
        <transition name="slide-right">
          <div class="em-settings-menu" style="margin-left: 10%" v-if="menuHighlight === 0">
            <div v-for="(menu,index) in menus" :key="'menu_' + menu.index" class="em-shadow-cards col-md-3" v-wave @click="menuHighlight = menu.index;currentTitle = menu.title">
              <span class="material-icons-outlined em-gradient-icons em-mb-16">{{menu.icon}}</span>
              <p class="em-body-16-semibold em-mb-8">{{translate(menu.title)}}</p>
              <p class="em-font-size-14">{{translate(menu.description)}}</p>
            </div>
          </div>
        </transition>

        <!-- COMPONENTS -->
        <transition name="fade">
          <editStyle
              v-if="menuHighlight === 1"
              @LaunchLoading="updateLoading"
              @StopLoading="updateLoading"
              ref="styling"
          ></editStyle>

          <ContentTool
              v-if="menuHighlight === 2"
              v-show="modal_ready"
              @resetMenuIndex="menuHighlight = 0"
              ref="content"
          />

          <TranslationTool
              v-if="menuHighlight === 9"
              v-show="modal_ready"
              @resetMenuIndex="menuHighlight = 0"
              ref="translations"
          />

          <editCGV
              v-if="menuHighlight === 3"
              ref="cgv"
              :actualLanguage="actualLanguage"
              :manyLanguages="manyLanguages"
          ></editCGV>

          <editFooter
              v-if="menuHighlight === 4"
              ref="footer"
              :actualLanguage="actualLanguage"
              :manyLanguages="manyLanguages"
          ></editFooter>

          <editStatus
              v-if="menuHighlight === 5"
              @LaunchLoading="updateLoading"
              @StopLoading="updateLoading"
              ref="status"
              :actualLanguage="actualLanguage"
              :manyLanguages="manyLanguages"
          ></editStatus>

          <editTags
              v-if="menuHighlight === 6"
              @LaunchLoading="updateLoading"
              @StopLoading="updateLoading"
              ref="tags"
          ></editTags>

          <edit-applicants
              v-if="menuHighlight === 7"
              @LaunchLoading="updateLoading"
              @StopLoading="updateLoading"
              ref="applicants"
          ></edit-applicants>

<!--          <editDatas
                  v-if="menuHighlight == 8 && coordinatorAccess != 0"
                  ref="datas"
                  :actualLanguage="actualLanguage"
                  :manyLanguages="manyLanguages"
          ></editDatas>-->



<!--          <help-settings
              v-if="menuHighlight == 8"
              ref="help"
              :actualLanguage="actualLanguage"
              :manyLanguages="manyLanguages"
          ></help-settings>-->
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
import TranslationTool from "../components/Settings/TranslationTool/TranslationTool";
import ContentTool from "../components/Settings/Content/ContentTool";

const qs = require("qs");

export default {
  name: "globalSettings",

  components: {
    ContentTool,
    TranslationTool,
    EditApplicants,
    editStatus,
    editTags,
    editCGV,
    editFooter,
    editHomepage,
    editStyle,
    editDatas,
  },

  props: {
    actualLanguage: String,
    coordinatorAccess: Number,
    manyLanguages: Number
  },

  data: () => ({
    menuHighlight: 0,
    currentTitle: '',
    langue: 0,
    saving: false,
    endSaving: false,

    menus: [
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_STYLE",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_STYLE_DESC",
        icon: 'style',
        index: 1
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_CONTENT",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_CONTENT_DESC",
        icon: 'notes',
        index: 2
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_STATUS",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_STATUS_DESC",
        icon: 'bookmark_border',
        index: 5
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_TAGS",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_TAGS_DESC",
        icon: 'label',
        index: 6
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_APPLICANTS",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_APPLICANTS_DESC",
        icon: 'people',
        index: 7
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_TRANSLATIONS",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_TRANSLATIONS_DESC",
        icon: 'language',
        index: 9
      },
    ],
    modal_ready: false
  }),

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
        /*case 2:
          this.updateCgv(this.$refs.cgv.$data.form.content);
          break;*/
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

  created() {
    if (this.actualLanguage == "en") {
      this.langue = 1;
    }
  },

  watch: {
    menuHighlight: function(value){
      this.modal_ready = false;
      setTimeout(() => {
        if(value === 9){
          this.$modal.show('translationTool');
          this.modal_ready = true;
        }
        if(value === 2){
          this.$modal.show('contentTool');
          this.modal_ready = true;
        }
      },500)
    }
  }
};
</script>

<style scoped>
</style>
