<template>
  <div>
    <div id="accordion-collapse" v-for="(section, indexSection) in sections"
         class="flex flex-col justify-between w-full p-5 font-medium rtl:text-right text-black border border-gray-200 rounded-[15px] bg-white mb-3 gap-3 shadow"
         data-accordion-target="#accordion-collapse-body-1" aria-expanded="true"
         aria-controls="accordion-collapse-body-1">

      <div @click="handleSection(indexSection)" class="flex items-center justify-between cursor-pointer">
        <div class="flex">
          <h1 id="accordion-collapse-heading-1" class="user-select-none flex flex-row justify-between">
          <span :id="'Subtile'+indexSection" class="text-2xl user-select-none">{{
              translate(section.label)
            }}</span>
          </h1>
        </div>
        <!-- The expand icon of the section wich rotate-->
        <span class="material-icons-outlined scale-150 user-select-none" :id="'SubtitleArrow'+indexSection"
              name="SubtitleArrows"
              :class="activeSection === indexSection ? 'rotate-180' : ''">expand_more</span>
      </div>

      <!-- The content of the section -->
      <div name="SubMenuContent" class="flex flex-col" v-if="activeSection === indexSection">
        <component :is="section.component" :key="activeSection" v-bind="section.props" @needSaving="handleNeedSaving">
        </component>
      </div>
    </div>
  </div>

</template>

<script>
import SiteSettings from "@/components/Settings/SiteSettings.vue";
import EditTheme from "@/components/Settings/Style/EditTheme.vue";
import EditStatus from "@/components/Settings/FilesTool/EditStatus.vue";
import EditTags from "@/components/Settings/FilesTool/EditTags.vue";
import General from "@/components/Settings/Style/General.vue";
import Orphelins from "@/components/Settings/TranslationTool/Orphelins.vue";
import Translations from "@/components/Settings/TranslationTool/Translations.vue";

export default {
  name: "Content",
  components: {
    SiteSettings,
    EditTheme,
    EditStatus,
    EditTags,
    General,
    Orphelins,
    Translations,
  },
  props: {
    json_source: {
      type: String,
      required: true,
    },
  },

  mixins: [],

  data() {
    return {
      sections: [],

      activeSection: null,
      needSaving: false,
    }
  },
  created() {
    this.sections = require('../../../data/' + this.$props.json_source);
    this.activeSection = 0;
  },
  mounted() {
  },
  methods: {
    saveSection(section) {
      console.log(section)
    },
    handleNeedSaving(needSaving) {
      this.needSaving = needSaving;
      this.$emit('needSaving', needSaving)
    },
    handleSection(index) {
      //TODO: If need saving is true, show a modal to confirm the saving
      this.activeSection = index
    }
  },
  watch: {
    activeSection: function (val) {
      this.$emit('sectionSelected', this.sections[val])
    }
  },
}
</script>

<style scoped>

</style>