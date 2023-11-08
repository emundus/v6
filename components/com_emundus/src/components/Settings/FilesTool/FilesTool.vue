<template>
  <span :id="'filesTool'">
    <modal
        :name="'filesTool'"
        height="auto"
        transition="fade"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
        @closed="beforeClose"
    >
      <div class="em-modal-header">
        <div class="em-flex-space-between em-flex-row em-pointer" @click.prevent="$modal.hide('filesTool')">
          <div class="em-w-max-content em-flex-row">
            <span class="material-icons-outlined">arrow_back</span>
            <span class="em-ml-8">{{ translate('COM_EMUNDUS_ONBOARD_ADD_RETOUR') }}</span>
          </div>
          <div v-if="saving" class="em-flex-row em-flex-start">
            <div class="em-loader em-mr-8"></div>
            <p class="em-font-size-14 em-flex-row">{{
                translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_PROGRESS')
              }}</p>
          </div>
          <p class="em-font-size-14"
             v-if="!saving && last_save != null">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_LAST') + last_save }}</p>
        </div>
      </div>

      <div class="em-modal-content">
        <div class="em-modal-menu__sidebar">
          <div v-for="menu in menus" :key="'menu_' + menu.index" @click="currentMenu = menu.index"
               class="translation-menu-item em-p-16 em-flex-row em-flex-space-between pointer"
               :class="currentMenu === menu.index ? 'em-modal-menu__current' : ''">
            <p class="em-font-size-16">{{ translate(menu.title) }}</p>
          </div>
        </div>

        <transition name="fade" mode="out-in">
          <EditStatus v-if="currentMenu === 1" :key="currentMenu" class="em-modal-component"
                      @updateSaving="updateSaving" @updateLastSaving="updateLastSaving"/>
          <EditTags v-if="currentMenu === 2" :key="currentMenu" class="em-modal-component" @updateSaving="updateSaving"
                    @updateLastSaving="updateLastSaving"/>
          <EditApplicants v-if="currentMenu === 3" :key="currentMenu" class="em-modal-component"
                          @updateSaving="updateSaving" @updateLastSaving="updateLastSaving"/>
        </transition>
      </div>

      <div v-if="loading">
      </div>
    </modal>
  </span>
</template>

<script>
/* COMPONENTS */
import EditStatus from "./EditStatus";
import EditTags from "./EditTags";
import EditApplicants from "./EditApplicants";

export default {
  name: "filesTool",
  props: {},
  components: {EditApplicants, EditTags, EditStatus},
  data() {
    return {
      currentMenu: 1,
      menus: [
        {
          title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_STATUS",
          index: 1
        },
        {
          title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_TAGS",
          index: 2
        },
        {
          title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_APPLICANTS",
          index: 3
        },
      ],

      loading: false,
      saving: false,
      last_save: null,
    }
  },
  methods: {
    beforeClose(event) {
      this.$emit('resetMenuIndex');
    },


    updateSaving(saving) {
      this.saving = saving;
    },

    updateLastSaving(last_save) {
      this.last_save = last_save;
    }
  }
}
</script>

<style scoped lang="scss">
</style>
