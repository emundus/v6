<template>
  <div class="w-full flex gap-8">
    <SidebarMenu json_source="settings/menus.json" @menuSelected="handleMenu" />

    <div class="w-full pt-6 pr-8 pb-3 pl-0" v-if="activeMenuItem">
      <h1 class="text-2xl pl-1 font-semibold text-green-700 mb-3">
        <span class="material-icons-outlined scale-150 text-green-700 me-2">
          {{ activeMenuItem.icon }}
        </span>
        {{ translate(activeMenuItem.label) }}
      </h1>

      <div>
        <Content :key="'json_'+activeMenuItem.name" v-if="activeMenuItem.type === 'JSON'" :json_source="'settings/'+activeMenuItem.source" @needSaving="handleNeedSaving" />

        <component v-else :is="activeMenuItem.component" :key="'component_'+activeMenuItem.name" v-bind="activeMenuItem.props" />
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>

</template>

<script>
import EditStatus from '../components/Settings/Files/EditStatus';
import EditTags from '../components/Settings/Files/EditTags';
import TranslationTool from '../components/Settings/Translation/TranslationTool';
import AttachmentStorage from '../components/Settings/AttachmentStorage/AttachmentStorage';

import EditEmailJoomla from "@/components/Settings/Files/EditEmailJoomla.vue";

import Global from "@/components/Settings/Style/General.vue";
import EditTheme from "@/components/Settings/Style/EditTheme.vue";
import EditFooter from "@/components/Settings/Content/EditFooter.vue";
import Translations from "@/components/Settings/Translation/Translations.vue";
import Orphelins from "@/components/Settings/Translation/Orphelins.vue";
import EditArticle from "@/components/Settings/Content/EditArticle.vue";
import EditorQuill from "@/components/editorQuill.vue";
import GlobalLang from "@/components/Settings/Translation/Global.vue";

import Multiselect from 'vue-multiselect';
import SidebarMenu from "@/components/Menus/SidebarMenu.vue";
import Content from "@/components/Settings/Content.vue";
import Addons from "@/components/Settings/Addons.vue";


export default {
  name: "globalSettings",
  components: {
    Content,
    SidebarMenu,
    EditEmailJoomla,
    EditorQuill,
    EditArticle,
    Orphelins,
    Translations,
    EditFooter,
    Global,
    EditTheme,
    AttachmentStorage,
    TranslationTool,
    EditStatus,
    EditTags,
    Multiselect,
    GlobalLang,
    Addons
  },
  props: {
    actualLanguage: {
      type: String,
      default: 'fr'
    },
    coordinatorAccess: {
      type: Number,
      default: 1
    },
    manyLanguages: {
      type: Number,
      default: 1
    }
  },

  data: () => ({
    saving: false,
    endSaving: false,
    loading: null,
    needSaving: false,

    activeMenuItem: null,
  }),

  created() {
    this.loading = true;
    this.changeCSS();
    this.loading = false;
  },
  mounted() {
    //this.URLMenu();
  },

  methods: {
    handleNeedSaving(needSaving) {
      this.needSaving = needSaving;
    },

    changeCSS() {
      document.getElementById("header-b").style.display = "none";
      document.getElementById("g-navigation").style.display = "none";
    },

    URLMenu() {
      const url = new URL(window.location.href);
      if (url.search) {
        const params = new URLSearchParams(url.search);
        const menu = params.get("Menu");
        const section = params.get("section");
        const indexMenu = this.searchMenu(menu)
        this.handleMenu(indexMenu, this.menus[indexMenu]);
        const indexSubSection = this.searchSubMenu(indexMenu, section);

        if (indexSubSection !== -1) {
          setTimeout(() => {
            this.handleSubMenu(indexSubSection);
          }, 100);//wait thant this.handleMenu(indexMenu  , this.menus[indexMenu]) is finished
        }
      } else {
        this.handleMenu(0, this.menus[0]);
      }
    },

    handleMenu(item) {
      //TODO: If need saving is true, show a modal to confirm the saving
      this.activeMenuItem = item;
    },
  },
  watch: {}
};

</script>

<style scoped>
</style>
