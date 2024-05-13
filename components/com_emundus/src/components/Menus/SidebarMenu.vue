<template>
  <aside id="logo-sidebar"
         class="corner-bottom-left-background sticky left-0 top-0 h-screen bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition-all"
         :class="minimized === true ? 'w-16' : 'w-64'"
         aria-label="Sidebar">
    <div class="h-full pb-4 overflow-y-auto bg-white dark:bg-gray-800"
         @mouseover="showMinimized = true"
         @mouseleave="showMinimized = false"
    >
      <ul class="space-y-2 font-large list-none">
        <li class="flex items-center justify-between m-3">
              <span class="flex items-center px-2 rounded-lg group cursor-pointer" @click="window.history.back();">
                <!-- The back button icon -->
                <span class="material-icons-outlined user-select-none text-green-700">arrow_back</span>
                <!-- The back button label -->
                <span class="ms-1 text-green-700" v-if="minimized === false">{{ translate('BACK') }}</span>
              </span>
          <span class="material-icons-outlined absolute right-[-12px] !text-xl/5 bg-neutral-400 rounded-full cursor-pointer"
                :class="minimized ? 'rotate-180' : ''"
                v-show="showMinimized === true"
                @click="handleSidebarSize">chevron_left</span>
        </li>

        <li v-for="(menu, indexMenu) in menus" class="m-3" v-if="menu.published === true">
              <span :id="'Menu-'+indexMenu" @click="activeMenu = indexMenu;"
                    class="flex items-start p-2 cursor-pointer rounded-lg group user-select-none"
                    :class="activeMenu === indexMenu ? 'font-bold text-green-700 bg-[#008A351A]' : 'hover:bg-gray-100'"
              >
                <i class="material-icons-outlined font-bold" :class="activeMenu === indexMenu ? 'text-green-700' : ''"
                   name="icon-Menu"
                   :title="translate(menu.label)"
                   :id="'icon-'+indexMenu">{{ menu.icon }}</i>
                <span class="ms-2 font-bold"
                      v-if="minimized === false"
                      :class="activeMenu === indexMenu ? 'text-green-700' : ''">{{ translate(menu.label) }}</span>
              </span>
        </li>
      </ul>
    </div>

    <div class="tchoozy-corner-bottom-left-bakground-mask-image h-1/3	w-full absolute bottom-0 bg-main-500"></div>

  </aside>
</template>

<script>

export default {
  name: "SidebarMenu",
  components: {},
  props: {
    json_source: {
      type: String,
      required: true,
    },
  },

  mixins: [],

  data() {
    return {
      menus: [],

      activeMenu: null,
      minimized: false,
      showMinimized: false
    }
  },
  created() {
    this.menus = require('../../../data/' + this.$props.json_source);
    this.activeMenu = 0;

    const sessionMenu = sessionStorage.getItem('tchooz_selected_menu/'+this.$props.json_source.replace('.json','')+ '/' + document.location.hostname);
    if (sessionMenu) {
      this.activeMenu = parseInt(sessionMenu);
    }
  },
  mounted() {
  },
  methods: {
    handleSidebarSize() {
      this.minimized = !this.minimized;
    },
  },
  watch: {
    activeMenu: function (val) {
      sessionStorage.setItem('tchooz_selected_menu/'+this.$props.json_source.replace('.json','')+ '/' + document.location.hostname, val);
      this.$emit('menuSelected', this.menus[val])
    }
  },
}
</script>

<style scoped>
</style>