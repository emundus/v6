<template>
  <div class="em-w-100 em-mt-80">
    <div>

      <!--- MENU --->
      <transition name="slide-right">
        <div class="em-grid-3" v-if="menuHighlight === 0">
          <div v-for="(menu,index) in displayedMenus" :key="'menu_' + menu.index" class="em-shadow-cards col-md-3 em-hover-s-scale" v-wave @click="changeMenu(menu)">
            <span class="material-icons-outlined em-gradient-icons em-mb-16">{{menu.icon}}</span>
            <h4 class="em-body-16-semibold  em-main-500-color em-mb-8 em-h4">{{translate(menu.title)}}</h4>
            <p class="em-font-size-14">{{translate(menu.description)}}</p>
          </div>
        </div>
      </transition>

      <!-- COMPONENTS -->
      <transition name="fade">
        <StyleTool
            v-if="menuHighlight === 1"
            v-show="modal_ready"
            @resetMenuIndex="menuHighlight = 0"
        ></StyleTool>

        <ContentTool
            v-else-if="menuHighlight === 2"
            v-show="modal_ready"
            @resetMenuIndex="menuHighlight = 0"
        />

        <FilesTool
		        v-else-if="menuHighlight === 3"
            v-show="modal_ready"
            @resetMenuIndex="menuHighlight = 0"
        />

        <TranslationTool
		        v-else-if="menuHighlight === 9"
            v-show="modal_ready"
            @resetMenuIndex="menuHighlight = 0"
            ref="translations"
        />

        <AttachmentStorage
		        v-else-if="menuHighlight === 5"
            v-show="modal_ready"
            @resetMenuIndex="menuHighlight = 0"
        />
      </transition>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import EditStatus from '../components/Settings/FilesTool/EditStatus';
import EditTags from '../components/Settings/FilesTool/EditTags';
import TranslationTool from '../components/Settings/TranslationTool/TranslationTool';
import ContentTool from '../components/Settings/Content/ContentTool';
import FilesTool from '../components/Settings/FilesTool/FilesTool';
import StyleTool from '../components/Settings/Style/StyleTool';
import AttachmentStorage from '../components/Settings/AttachmentStorage/AttachmentStorage';

import settingsService from '../services/settings';

export default {
  name: "globalSettings",
  components: {
    StyleTool,
    AttachmentStorage,
    FilesTool,
    ContentTool,
    TranslationTool,
    EditStatus,
    EditTags
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
    menuHighlight: 0,
    currentTitle: '',
    langue: 0,
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
        access: 1,
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_CONTENT",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_CONTENT_DESC",
        icon: 'notes',
        index: 2,
        access: 1,
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_FILES_TOOL",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_FILES_TOOL_DESC",
        icon: 'source',
        index: 3,
        access: 1,
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_TRANSLATIONS",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_TRANSLATIONS_DESC",
        icon: 'language',
        index: 9,
        access: 1,
      },
      {
        title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_ATTACHMENT_STORAGE",
        description: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_ATTACHMENT_STORAGE_DESC",
        icon: 'inventory_2',
        index: 5,
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
      this.menus[0].access = this.em_params.style != undefined ? parseInt(this.em_params.style) : 1;
      this.menus[1].access = this.em_params.content != undefined ? parseInt(this.em_params.content) : 1;
      this.menus[2].access = 1;
      this.menus[3].access = this.em_params.translations != undefined  ? parseInt(this.em_params.translations) : 1;
      this.menus[4].access = this.em_params.attachment_storage != undefined ? parseInt(this.em_params.attachment_storage) : 0;
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

	computed: {
		displayedMenus() {
			return this.menus.filter((menu) => {
				return menu.access === 1;
			})
		}
	},

  watch: {
    menuHighlight: function(value){
      this.modal_ready = false;
      setTimeout(() => {
        switch (value){
          case 1:
            this.$modal.show('styleTool');
            this.modal_ready = true;
            break;
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
          case 5:
            this.$modal.show('attachmentStorage');
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
