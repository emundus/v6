<template>
  <span :id="'translationTool'">
    <modal
        :name="'translationTool'"
        height="auto"
        transition="fade"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
        @opened="checkSetup"
        @closed="beforeClose"
    >
      <div class="em-modal-header">
        <div class="em-flex-space-between em-flex-row em-pointer" @click.prevent="$modal.hide('translationTool')">
          <div class="em-w-max-content em-flex-row">
            <span class="material-icons-outlined">arrow_back</span>
            <span class="em-ml-8">{{ translate('COM_EMUNDUS_ONBOARD_ADD_RETOUR') }}</span>
          </div>
          <div v-if="saving" class="em-flex-row em-flex-start">
            <div class="em-loader em-mr-8"></div>
            <p class="em-font-size-14 em-flex-row">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_PROGRESS') }}</p>
          </div>
          <p class="em-font-size-14" v-if="!saving && last_save != null">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_LAST') + last_save}}</p>
        </div>
      </div>

      <div class="em-modal-content">
        <div class="em-modal-menu__sidebar">
          <div v-for="(menu,index) in menus" :key="'menu_' + menu.index" @click="currentMenu = menu.index" class="translation-menu-item em-p-16 em-flex-row em-flex-space-between pointer" :class="currentMenu === menu.index ? 'em-modal-menu__current' : ''">
            <p class="em-font-size-16">{{translate(menu.title)}}</p>
            <div v-if="menu.index === 3 && orphelins_count > 0" class="em-notifications-yellow"></div>
          </div>
        </div>

        <transition name="fade">
          <Global v-if="currentMenu === 1" v-show="!setup_success" class="em-modal-component" @updateOrphelinsCount="updateOrphelinsCount"></Global>
          <Translations v-if="currentMenu === 2" v-show="!setup_success" class="em-modal-component" @updateSaving="updateSaving" @updateLastSaving="updateLastSaving"></Translations>
          <Orphelins v-if="currentMenu === 3" v-show="!setup_success" class="em-modal-component"></Orphelins>
        </transition>

        <img v-if="setup_success" alt="checked-animation" class="em-success-animation" :src="'/images/emundus/animations/checked.gif'" />
      </div>

      <div v-if="loading">
        <div class="em-page-loader" v-if="!setup_success"></div>
        <p class="em-page-loader-text" v-if="!setup_success">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SETUP_PROGRESSING') }}</p>
        <p class="em-page-loader-text em-fade-loader" v-if="setup_success">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_SETUP_SUCCESS') }}</p>
      </div>
    </modal>
  </span>
</template>

<script>
import Global from "./Global";
import Translations from "./Translations";
import Orphelins from "./Orphelins";

import translationsService from "com_emundus/src/services/translations";

export default {
  name: "translationTool",
  props: {
		showModalOnLoad: {
			type: Number,
			default: 0
		},
	  defaultMenuIndex: {
		  type: Number,
		  default: 1
	  }
  },
  components: {Orphelins, Translations, Global},
  data() {
    return {
      orphelins_count: 0,
      currentMenu: 0,
      menus: [
        {
          title: "COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_GLOBAL",
          index: 1
        },
        {
          title: "COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS",
          index: 2
        },
        {
          title: "COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_ORPHELINS",
          index: 3
        },
      ],

      loading: false,
      setup_success: false,
      saving: false,
      last_save: null,
    }
  },
	created() {
		const data = this.$store.getters['global/datas'];

		if (this.showModalOnLoad === 0) {
			if (data.showModalOnLoad !== undefined) {
				this.showModalOnLoad = Number(data.showModalOnLoad.value) || 0;
			}
		}

		if (data.hasOwnProperty('defaultMenuIndex')) {
			this.defaultMenuIndex = Number(data.defaultMenuIndex.value) || 1;
		}

		if (this.showModalOnLoad > 0) {
			setTimeout(() => {
				this.$modal.show('translationTool');
			}, 1000);
		}
	},
  methods:{
    beforeClose(event) {
      this.$emit('resetMenuIndex');
    },

    updateOrphelinsCount(count) {
      this.orphelins_count = count;
    },

    checkSetup(){
      translationsService.checkSetup().then((response) => {
        if(response.data === 0){
          this.loading = true;
          translationsService.configureSetup().then((result) => {
            if(result.data === 1){
              this.loading = false;
              this.setup_success = true;
              this.currentMenu = this.defaultMenuIndex;
              setTimeout(() => {
                this.setup_success = false;
              },2700)
            }
          });
        } else {
          this.currentMenu = this.defaultMenuIndex;
        }
      })
    },

    updateSaving(saving){
      this.saving = saving;
    },

    updateLastSaving(last_save){
      this.last_save = last_save;
    }
  }
}
</script>

<style scoped lang="scss">
</style>
