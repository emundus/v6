<template>
  <div class="w-row em-mt-80">
    <div class="w-100">

      <!-- HEADER -->
      <div class="em-flex-row em-flex-start em-pointer em-m-24" v-if="menuHighlight !== 0 && menuHighlight !== 9 && menuHighlight !== 2 && menuHighlight !== 3" style="margin-left: 10%" @click="menuHighlight = 0">
        <span class="material-icons-outlined">arrow_back</span><span class="em-ml-8">{{ translate('COM_EMUNDUS_ONBOARD_ADD_RETOUR') }}</span>
      </div>
      <h5 class="em-h5 em-m-24" v-if="menuHighlight === 0 && !modal_ready" style="margin-left: 10%">{{ translate("COM_EMUNDUS_ONBOARD_ADDCAMP_PARAMETER") }}</h5>
      <h5 class="em-h5 em-m-24" v-else-if="menuHighlight !== 0 && menuHighlight !== 9 && menuHighlight !== 2 && menuHighlight !== 3" style="margin-left: 10%">{{ translate(currentTitle) }}</h5>

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
            v-if="menuHighlight === 9"
            v-show="modal_ready"
            @resetMenuIndex="menuHighlight = 0"
            ref="translations"
        />
      </transition>
    </div>
  </div>
</template>

<script>
import EditStatus from "../components/Settings/FilesTool/EditStatus";
import EditTags from "../components/Settings/FilesTool/EditTags";
import EditStyle from "../components/Settings/EditStyle";
import TranslationTool from "../components/Settings/TranslationTool/TranslationTool";
import ContentTool from "../components/Settings/Content/ContentTool";
import FilesTool from "../components/Settings/FilesTool/FilesTool";

const qs = require("qs");

export default {
  name: "globalSettings",

  components: {
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
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_FILES_TOOL",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_FILES_TOOL_DESC",
        icon: 'source',
        index: 3
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

  created() {
    if (this.actualLanguage == "en") {
      this.langue = 1;
    }
  },

  methods: {},

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
          case 9:
            this.$modal.show('translationTool');
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
</style>
