<template>
  <span :id="'styleTool'" :class="'full-width-modal'">
    <modal
        :name="'styleTool'"
        height="auto"
        transition="fade"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
        @closed="beforeClose"
    >

      <div class="em-modal-header">
        <div class="em-flex-space-between em-flex-row em-pointer" @click.prevent="$modal.hide('styleTool')">
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
          <div v-for="menu in menus" :key="'menu_' + menu.index"
               @click="currentMenu = menu.index"
               class="translation-menu-item em-p-16 em-flex-row em-flex-space-between pointer"
               :class="currentMenu === menu.index ? 'em-modal-menu__current' : ''"
          >
            <p class="em-font-size-16">{{ translate(menu.title) }}</p>
          </div>
        </div>

        <transition name="fade">
          <General v-if="currentMenu === 1" class="em-modal-component"></General>
        </transition>
      </div>

      <div v-if="loading">
      </div>
    </modal>
  </span>
</template>

<script>
/* COMPONENTS */
import General from "./General";

export default {
  name: "styleTool",
  props: {},
  components: {General},
  data() {
    return {
      currentMenu: 1,
      menus: [
        {
          title: "COM_EMUNDUS_ONBOARD_SETTINGS_MENU_STYLE",
          index: 1
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
