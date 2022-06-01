<template>
  <div class="em-w-100 em-mt-80">
    <div>

      <!-- HEADER -->
      <div class="em-flex-row em-flex-start em-pointer em-m-24" v-if="menuHighlight === 1" style="margin-left: 10%" @click="menuHighlight = 0">
        <span class="material-icons-outlined">arrow_back</span><span class="em-ml-8">{{ translate('COM_EMUNDUS_ONBOARD_ADD_RETOUR') }}</span>
      </div>
      <h5 class="em-h5 em-m-24" v-if="menuHighlight === 0 && !modal_ready" style="margin-left: 10%">{{ translate("COM_EMUNDUS_ONBOARD_ADDCAMP_PARAMETER") }}</h5>
      <h5 class="em-h5 em-m-24" v-else-if="menuHighlight === 1" style="margin-left: 10%">{{ translate(currentTitle) }}</h5>

      <!--- MENU --->
      <transition name="slide-right">
        <div class="em-settings-menu" style="margin-left: 10%" v-if="menuHighlight === 0">
          <div v-for="(menu,index) in menus" :key="'menu_' + menu.index" class="em-shadow-cards col-md-3 em-hover-s-scale" v-if="menu.access === 1" v-wave @click="changeMenu(menu)">
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
            ref="styling"
        ></editStyle>

        <ContentTool
            v-if="menuHighlight === 2"
            v-show="modal_ready"
            @resetMenuIndex="menuHighlight = 0"
        />

        <FilesTool
            v-if="menuHighlight === 3"
            v-show="modal_ready"
            @resetMenuIndex="menuHighlight = 0"
        />

        <TranslationTool
            v-if="menuHighlight === 4"
            v-show="modal_ready"
            @resetMenuIndex="menuHighlight = 0"
            ref="translations"
        />

        <AttachmentStorage
            v-if="menuHighlight === 5"
            v-show="modal_ready"
            @resetMenuIndex="menuHighlight = 0"
        />

        <DashboardTool
            v-if="menuHighlight === 6"
            v-show="modal_ready"
            @resetMenuIndex="menuHighlight = 0"
          />
      </transition>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import EditStatus from "../components/Settings/FilesTool/EditStatus";
import EditTags from "../components/Settings/FilesTool/EditTags";
import EditStyle from "../components/Settings/EditStyle";
import TranslationTool from "../components/Settings/TranslationTool/TranslationTool";
import ContentTool from "../components/Settings/Content/ContentTool";
import FilesTool from "../components/Settings/FilesTool/FilesTool";
import AttachmentStorage from "../components/Settings/AttachmentStorage/AttachmentStorage";

import settingsService from "com_emundus/src/services/settings";
import DashboardTool from "../components/Settings/DashboardTool/DashboardTool";

const qs = require("qs");

export default {
  name: "globalSettings",

  components: {
    DashboardTool,
    AttachmentStorage,
    FilesTool,
    ContentTool,
    TranslationTool,
    EditStatus,
    EditTags,
    EditStyle
  },

  props: {
    actualLanguage: String,
    coordinatorAccess: Number,
    manyLanguages: Number
  },

  data: () => ({
    menuHighlight: 0,
    currentTitle: '',

    saving: false,
    endSaving: false,
    loading: false,

    em_params: {},
    menus: [
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_STYLE",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_STYLE_DESC",
        icon: 'style',
        index: 1,
        access: 0,
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_CONTENT",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_CONTENT_DESC",
        icon: 'notes',
        index: 2,
        access: 0,
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_FILES_TOOL",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_FILES_TOOL_DESC",
        icon: 'source',
        index: 3,
        access: 0,
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_TRANSLATIONS",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_TRANSLATIONS_DESC",
        icon: 'language',
        index: 4,
        access: 0,
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_ATTACHMENT_STORAGE",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_ATTACHMENT_STORAGE_DESC",
        icon: 'inventory_2',
        index: 5,
        access: 0,
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_DASHBOARD",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_DASHBOARD_DESC",
        icon: 'table_chart',
        index: 6,
        access: 0,
      },
    ],
    modal_ready: false
  }),

  created() {
    this.loading = true;
    settingsService.getEmundusParams().then((params) => {
      this.em_params = params.data.config;

      // Give access to modules
      this.menus[0].access = parseInt(this.em_params.style);
      this.menus[1].access = parseInt(this.em_params.content);
      this.menus[2].access = 1;
      this.menus[3].access = parseInt(this.em_params.translations);
      this.menus[4].access = parseInt(this.em_params.attachment_storage);
      this.menus[5].access = (parseInt(this.em_params.dashboard_settings) === 1 && parseInt(this.$store.state.global.datas.sysadminaccess.value) === 1) ? 1 : 0;
      //

      this.loading = false;
    });
  },

  methods: {
    changeMenu(menu){
      setTimeout(() => {
        this.menuHighlight = menu.index;
        this.currentTitle = menu.title;
      },200);
    }
  },

  watch: {
    menuHighlight: function(value){
      this.modal_ready = false;
      setTimeout(() => {
        switch (value){
          case 2:
            this.$modal.show('contentTool');
            this.modal_ready = true;
            break;
          case 3:
            this.$modal.show('filesTool');
            this.modal_ready = true;
            break;
          case 4:
            this.$modal.show('translationTool');
            this.modal_ready = true;
            break;
          case 5:
            this.$modal.show('attachmentStorage');
            this.modal_ready = true;
            break;
          case 6:
            this.$modal.show('dashboardTool');
            this.modal_ready = true;
            break;
          default:
            break;
        }
      },500)
    }
  }
};
</script>

<style scoped>
.em-hover-s-scale{
  transition: transform 0.2s ease-in-out;
}
.em-hover-s-scale:hover{
  transform: scale(1.03);
}
</style>
