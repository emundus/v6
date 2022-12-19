<template>
  <span :id="'contentTool'">
    <modal
        :name="'contentTool'"
        height="auto"
        transition="fade"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
        @closed="beforeClose"
    >
      <div class="em-modal-header">
        <div class="em-flex-space-between em-flex-row em-pointer" @click.prevent="$modal.hide('contentTool')">
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
          <div v-for="menu in menus" :key="'menu_' + menu.index" 
            @click="currentMenu = menu.index"
            class="translation-menu-item em-p-16 em-flex-row em-flex-space-between pointer" 
            :class="currentMenu === menu.index ? 'em-modal-menu__current' : ''"
          >
            <p class="em-font-size-16">{{translate(menu.title)}}</p>
          </div>
        </div>

        <transition name="fade" mode="out-in">
          <EditArticle v-if="selectedMenu.type === 'article'" :key="currentMenu" :article_id="selectedMenu.id" :article_alias="selectedMenu.alias" class="em-modal-component" @updateSaving="updateSaving" @updateLastSaving="updateLastSaving"></EditArticle>
          <EditFooter v-else-if="selectedMenu.type === 'footer'" class="em-modal-component" @updateSaving="updateSaving" @updateLastSaving="updateLastSaving"></EditFooter>
        </transition>
      </div>

      <div v-if="loading">
      </div>
    </modal>
  </span>
</template>

<script>
/* COMPONENTS */
import EditArticle from "./EditArticle";
import EditFooter from "./EditFooter";

export default {
  name: "contentTool",
  props: { },
  components: {EditFooter, EditArticle},
  data() {
    return {
      currentMenu: 1,
      menus: [
        {
          type: "article",
          id: 52,
          alias: null,
          title: "COM_EMUNDUS_ONBOARD_CONTENT_TOOL_HOMEPAGE",
          index: 1
        },
        {
          type: "article",
          alias: "mentions-legales",
          title: "COM_EMUNDUS_ONBOARD_CONTENT_TOOL_LEGAL_MENTION",
          index: 2
        },
        {
          type: "article",
          alias: "politique-de-confidentialite-des-donnees",
          title: "COM_EMUNDUS_ONBOARD_CONTENT_TOOL_DATAS",
          index: 3
        },
        {
          type: "article",
          alias: "gestion-de-vos-droits",
          title: "COM_EMUNDUS_ONBOARD_CONTENT_TOOL_RIGHTS",
          index: 4
        },
        {
          type: "article",
          alias: "gestion-des-cookies",
          title: "COM_EMUNDUS_ONBOARD_CONTENT_TOOL_COOKIES",
          index: 5
        },
        {
          type: "footer",
          title: "COM_EMUNDUS_ONBOARD_CONTENT_TOOL_FOOTER",
          index: 6
        },
      ],

      loading: false,
      saving: false,
      last_save: null,
    }
  },
  methods:{
    beforeClose(event) {
      this.$emit('resetMenuIndex');
    },


    updateSaving(saving){
      this.saving = saving;
    },

    updateLastSaving(last_save){
      this.last_save = last_save;
    }
  },
  computed: {
    selectedMenu() {
      return this.menus.find(menu => menu.index === this.currentMenu);
    }
  }
}
</script>

<style scoped lang="scss">
</style>
