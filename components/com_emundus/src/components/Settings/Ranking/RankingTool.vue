<template>
  <span :id="'rankingTool'" :class="'full-width-modal'">
    <modal
        :name="'rankingTool'"
        height="auto"
        transition="fade"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
        @closed="beforeClose"
    >
      <div class="em-modal-header em-flex-row">
        <div class="em-flex-space-between em-flex-row em-pointer" @click.prevent="$modal.hide('rankingTool')">
          <div class="em-w-max-content em-flex-row">
            <span class="material-icons-outlined">navigate_before</span>
            <span class="em-ml-8 em-text-neutral-900">{{ translate('COM_EMUNDUS_ONBOARD_ADD_RETOUR') }}</span>
          </div>
          <div v-if="saving" class="em-flex-row em-flex-start">
            <div class="em-loader em-mr-8"></div>
            <p class="em-font-size-14 em-flex-row">{{
                translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_PROGRESS')
              }}
            </p>
          </div>
          <p class="em-font-size-14"
             v-if="!saving && last_save != null">{{ translate('COM_EMUNDUS_ONBOARD_TRANSLATION_TOOL_TRANSLATIONS_AUTOSAVE_LAST') + last_save }}
          </p>
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
          <div class="em-modal-component">
            <h2>{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_GENERAL') }}</h2>
            
            <h3>{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_HIERARCHIES') }}</h3>
            <div id="hierarchies" class="mt-2">
              <div v-for="hierarchy in hierarchies" :key="hierarchy.id" class="p-2 border-1 shadow gap-2">
                <div class="flex flex-row justify-between items-center">
                  <input :id="'hierarchy_label' + hierarchy.id" name="hierarchy_label" type="text" v-model="hierarchy.label" />
                  <span class="material-icons-outlined cursor-pointer" @click="deleteHierarchy(hierarchy.id)">delete</span>
                </div>
                <div>
                  <div class="profiles">

                  </div>
                  <div class="edit_status">

                  </div>
                  <div class="visible_status">

                  </div>
                  <div class="visible_hierarchies">

                  </div>
                </div>
                <div class="flex flex-row justify-end mt-2">
                  <button class="em-primary-button w-fit" @click="saveHierarchy(hierarchy.id)"> {{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_SAVE_HIERARCHY') }}</button>
                </div>
              </div>
              <div v-if="!newHierarchy" class="flex flex-row justify-end mt-2">
                <button class="em-primary-button w-fit" @click="addHierarchy"> {{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_ADD_HIERARCHY') }}</button>
              </div>
            </div>
          </div>
      </div>

      <div v-if="loading">
      </div>
    </modal>
  </span>
</template>

<script>
import rankingService from '@/services/ranking';
import userService from '@/services/user';

export default {
  name: 'RankingTool',
  props: {},
  data() {
    return {
      hierarchies: [],
      profiles: [],
      currentMenu: 1,
      menus: [
        {
          title: "COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_GENERAL",
          index: 1
        },
      ],
      loading: false,
      saving: false,
      last_save: null,
      default_hierarchy: {
        id: "tmp",
        parent_id: "0",
        label: "Nouvelle hiérarchie",
        published: "1",
        status: "0",
        package_by: "jos_emundus_setup_campaigns.id",
        package_start_date_field: "",
        package_end_date_field: ""
      }
    }
  },
  created() {
    this.initialise();
  },
  methods: {
    async initialise() {
      this.loading = true;
      await this.getHierarchies();
      await this.getProfiles();
      this.loading = false;
    },
    getHierarchies() {
      return rankingService.getHierarchies().then(response => {
        this.hierarchies = response.data;
      });
    },
    getProfiles() {
      return userService.getNonApplicantProfiles().then(response => {
        this.profiles = response.data;
      });
    },
    addHierarchy() {
      if (!newHierarchy) {
        this.hierarchies.push(this.default_hierarchy);
      }
    },
    saveHierarchy(hierarchyId) {
      if (hierarchyId) {
        const hierarchyToSave = this.hierarchies.find((hierarchy) => {
          return hierarchy.id === hierarchyId;
        });

        if (hierarchyToSave) {
          rankingService.saveHierarchy(hierarchyToSave).then((response) => {
            if (response.status) {
              this.getHierarchies();
            }
          });
        }
      }
    },
    deleteHierarchy(hierarchyId) {
      this.hierarchies = this.hierarchies.filter((hierarchy) => {
        return hierarchy.id !== hierarchyId;
      });

      if (hierarchyId !== 'tmp') {
        rankingService.deleteHierarchy(hierarchyId).then((response) => {
          if (!response.data) {
            rankingService.getHierarchies();
          }
        });
      } 
    },
    beforeClose() {
      this.$emit('resetMenuIndex');
    }
  },
  computed: {
    newHierarchy() {
      this.hierarchies.find((hierarchy) => {
        return hierarchy.id === 'tmp';
      });
    }
  }
}
</script>

<style scoped>

</style>