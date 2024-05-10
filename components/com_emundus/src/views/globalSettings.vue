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
        <Content :ref="'content_'+activeMenuItem.name" :key="'json_'+activeMenuItem.name" v-if="activeMenuItem.type === 'JSON'" :json_source="'settings/'+activeMenuItem.source" @needSaving="handleNeedSaving" />

        <component :ref="'content_'+activeMenuItem.name" v-else :is="activeMenuItem.component" :key="'component_'+activeMenuItem.name" v-bind="activeMenuItem.props" @needSaving="handleNeedSaving" />
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>

</template>

<script>
import EditEmailJoomla from "@/components/Settings/EditEmailJoomla.vue";

import Multiselect from 'vue-multiselect';
import SidebarMenu from "@/components/Menus/SidebarMenu.vue";
import Content from "@/components/Settings/Content.vue";
import Addons from "@/components/Settings/Addons.vue";
import Swal from "sweetalert2";


export default {
  name: "globalSettings",
  components: {
    Content,
    SidebarMenu,
    EditEmailJoomla,
    Multiselect,
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

  created() {},
  mounted() {
    //this.URLMenu();
  },

  methods: {
    handleNeedSaving(needSaving) {
      this.$store.commit("settings/setNeedSaving",needSaving);
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
          }, 100);
        }
      } else {
        this.handleMenu(0, this.menus[0]);
      }
    },

    handleMenu(item) {
      if(this.$store.state.settings.needSaving) {
        Swal.fire({
          title: this.translate('COM_EMUNDUS_ONBOARD_WARNING'),
          text: this.translate('COM_EMUNDUS_ONBOARD_SETTINGS_GENERAL_UNSAVED'),
          showCancelButton: true,
          confirmButtonText: this.translate('COM_EMUNDUS_ONBOARD_SETTINGS_GENERAL_SAVE'),
          cancelButtonText: this.translate('COM_EMUNDUS_ONBOARD_CANCEL_UPDATES'),
          reverseButtons: true,
          customClass: {
            title: 'em-swal-title',
            cancelButton: 'em-swal-cancel-button',
            confirmButton: 'em-swal-confirm-button',
          }
        }).then((result) => {
          this.handleNeedSaving(false);

          if (result.value) {
            this.saveSection(this.activeMenuItem, item);
          } else {
            this.activeMenuItem = item;
          }
        });
      } else {
        this.activeMenuItem = item;
      }
    },

    saveSection(menu, item = null) {
      let vue_component = this.$refs['content_'+menu.name];
      if(Array.isArray(vue_component)) {
        vue_component = vue_component[0];
      }

      if(typeof vue_component.saveMethod !== 'function') {
        console.error('The component '+menu.name+' does not have a saveMethod function')
        return
      }

      vue_component.saveMethod().then((response) => {
        if(response === true) {
          if(item !== null) {
            this.activeMenuItem = item;
          }
        }
      });
    },
  },
  watch: {
    activeMenuItem: function (val,oldVal) {
      if(oldVal !== null) {
        sessionStorage.setItem('tchooz_settings_selected_section/' + document.location.hostname, null);
      }
    }
  }
};

</script>

<style>
#header-b,#g-navigation {
  display: none;
}
</style>
