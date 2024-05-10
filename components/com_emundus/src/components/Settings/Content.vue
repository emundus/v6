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
        <component :ref="'component_'+section.name" :is="section.component" :key="activeSection" v-bind="section.props" @needSaving="handleNeedSaving">
        </component>
      </div>
    </div>
  </div>

</template>

<script>
import SiteSettings from "@/components/Settings/SiteSettings.vue";
import EditTheme from "@/components/Settings/Style/EditTheme.vue";
import EditStatus from "@/components/Settings/Files/EditStatus.vue";
import EditTags from "@/components/Settings/Files/EditTags.vue";
import General from "@/components/Settings/Style/General.vue";
import Orphelins from "@/components/Settings/Translation/Orphelins.vue";
import Translations from "@/components/Settings/Translation/Translations.vue";
import EditArticle from "@/components/Settings/Content/EditArticle.vue";
import EditFooter from "@/components/Settings/Content/EditFooter.vue";
import Swal from "sweetalert2";

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
    EditArticle,
    EditFooter
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
    async saveMethod() {
      await this.saveSection(this.sections[this.activeSection]);
      return true;
    },
    async saveSection(section, index = null) {
      let vue_component = this.$refs['component_'+section.name];
      if(Array.isArray(vue_component)) {
        vue_component = vue_component[0];
      }

      if(typeof vue_component.saveMethod !== 'function') {
        console.error('The component '+section.name+' does not have a saveMethod function')
        return
      }

      vue_component.saveMethod().then((response) => {
        if(response === true) {
          if(index !== null) {
            this.handleActiveSection(index);
          }
        }
      });
    },

    handleNeedSaving(needSaving) {
      this.$store.commit("settings/setNeedSaving",needSaving);
    },
    handleSection(index) {
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
            this.saveSection(this.sections[this.activeSection], index);
          } else{
            this.handleActiveSection(index);
          }
        });
      } else {
        this.handleActiveSection(index);
      }
    },
    handleActiveSection(index) {
      if(index === this.activeSection){
        this.activeSection = null
      }
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