<template>
  <div id="accordion-collapse" v-if="activeMenuItem.type === 'sectionComponent'"
       class="flex flex-col justify-between w-full p-5 font-medium rtl:text-right text-black border border-gray-200 rounded-[15px] bg-white mb-3 gap-3 shadow"
       data-accordion-target="#accordion-collapse-body-1" aria-expanded="true"
       aria-controls="accordion-collapse-body-1">

    <div @click="handleSectionComponent(activeMenuItem)" class="flex items-center justify-between cursor-pointer">
      <div class="flex">
        <h1 id="accordion-collapse-heading-1" class="user-select-none flex flex-row justify-between">
          <span :id="'Subtile'"
                class="text-2xl user-select-none">{{ translate(activeMenuItem.sectionTitle) }}</span>
        </h1>
      </div>
      <!-- The expand icon of the section wich rotate-->
      <span class="material-icons-outlined scale-150 user-select-none" :id="'SubtitleArrow'"
            name="SubtitleArrows"
      >expand_more</span>
    </div>

    <!-- The content of the section -->
    <div name="SubMenuContent-componentSection" class="flex flex-col"
         v-if="activeSectionComponent===activeMenuItem.sectionTitle">
      <Info v-if="activeMenuItem.helptext" :text="activeMenuItem.helptext"></Info>
      <component :ref="'content_'+activeMenuItem.name" :is="activeMenuItem.component"
                 :key="'component_'+activeMenuItem.name" v-bind="activeMenuItem.props"
                 @needSaving="handleNeedSaving"/>
    </div>
  </div>
</template>

<script>
import EditEmailJoomla from "@/components/Settings/EditEmailJoomla.vue";

import Multiselect from 'vue-multiselect';
import SidebarMenu from "@/components/Menus/SidebarMenu.vue";
import Content from "@/components/Settings/Content.vue";
import Addons from "@/components/Settings/Addons.vue";
import Info from "@/components/info.vue";
import Swal from "sweetalert2";
import SectionComponent from "@/components/Settings/SectionComposant.vue";

export default {
  name: 'SectionComponent',
  components: {
    SectionComponent,
    Content,
    SidebarMenu,
    EditEmailJoomla,
    Multiselect,
    Addons,
    Info,
  },
  props: ['activeMenuItem', 'activeSectionComponent'],

  methods: {
    handleSectionComponent(item) {
      this.$emit('handleSectionComponent', item);
    },
    handleNeedSaving(needSaving , element) {
      this.$emit('needSaving', needSaving , element );
    }
  },
}
</script>