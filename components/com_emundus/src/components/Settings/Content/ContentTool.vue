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

        <transition name="fade" mode="out-in" v-if="selectedMenu">
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
import client from "com_emundus/src/services/axiosClient";
import mixin from "com_emundus/src/mixins/mixin";

export default {
  name: "contentTool",
  props: { },
  components: {EditFooter, EditArticle},
  mixins: [mixin],
  data() {
    return {
      currentMenu: 1,
      menus: [],

      loading: false,
      saving: false,
      last_save: null,
    }
  },
  created() {
    let index = 1;

    client().get("index.php?option=com_emundus&controller=settings&task=gethomearticle").then(response => {
      this.menus.push({
        type: "article",
        id: response.data.data,
        title: "COM_EMUNDUS_ONBOARD_CONTENT_TOOL_HOMEPAGE",
        index: index
      });

      client().get("index.php?option=com_emundus&controller=settings&task=getrgpdarticles").then(response => {
        response.data.data.forEach((article) => {
          index++;
          if(article.id) {
            this.menus.push({
              type: "article",
              id: parseInt(article.id),
              title: article.title,
              index: index
            });
          } else {
            this.menus.push({
              type: "article",
              alias: article.alias,
              title: article.title,
              index: index
            });
          }
        });

        index++;
        this.menus.push({
          type: "footer",
          title: "COM_EMUNDUS_ONBOARD_CONTENT_TOOL_FOOTER",
          index: index
        });
      });
    });
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
