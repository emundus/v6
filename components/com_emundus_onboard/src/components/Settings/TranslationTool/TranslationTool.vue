<template>
  <span :id="'translationTool'">
    <modal
        :name="'translationTool'"
        height="auto"
        transition="fade"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
        @closed="beforeClose"
    >
      <div class="em-modal-header">
        <div class="em-flex-row-start em-pointer" @click.prevent="$modal.hide('translationTool')">
          <span class="material-icons-outlined">arrow_back</span><span class="em-ml-8">{{ translate('COM_EMUNDUS_ONBOARD_ADD_RETOUR') }}</span>
        </div>
      </div>

      <div class="em-modal-content">
        <div class="em-modal-menu__sidebar">
          <div v-for="(menu,index) in menus" :key="'menu_' + menu.index" @click="currentMenu = menu.index">
            <p class="em-font-size-16 em-mb-8 em-p-16 pointer" :class="currentMenu === menu.index ? 'em-modal-menu__current' : ''">{{translate(menu.title)}}</p>
          </div>
        </div>

        <transition name="fade">
          <Global v-if="currentMenu === 1" class="em-modal-component"></Global>
          <Translations v-if="currentMenu === 2" class="em-modal-component"></Translations>
        </transition>
      </div>
    </modal>
  </span>
</template>

<script>
import Global from "./Global";
import Translations from "./Translations";

export default {
  name: "translationTool",
  props: { },
  components: {Translations, Global},
  data() {
    return {
      currentMenu: 1,
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
      ]
    }
  },
  methods:{
    beforeClose(event) {
      this.$emit('resetMenuIndex');
    },
  }
}
</script>

<style scoped lang="scss">
</style>
