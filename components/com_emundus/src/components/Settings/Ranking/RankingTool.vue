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

            <hr>

            <h3>{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_HIERARCHIES') }}</h3>
            <div id="hierarchies" class="mt-4">
              <div v-for="hierarchy in hierarchies" :key="hierarchy.id" class="p-2 border-1 shadow gap-2 rounded mt-8">
                <div class="flex flex-row justify-between items-center">
                  <input :id="'hierarchy_label' + hierarchy.id" name="hierarchy_label" type="text" v-model="hierarchy.label" />
                  <span class="material-icons-outlined cursor-pointer" :alt="translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_DELETE_HIERARCHY')" @click="deleteHierarchy(hierarchy.id)">delete</span>
                </div>
                <div>
                  <div class="profiles mt-2">
                    <label>{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_HIERARCHY_PROFILES') }}</label>
                    <multiselect v-model="hierarchy.profiles" label="label" track-by="id" :options="profilesOpts"
                        :multiple="true"
                        :taggable="false"
                        :placeholder="translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_SELECT_VALUE')"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :searchable="false"
                        :allow-empty="true"></multiselect>
                  </div>
                  <div class="status mt-2">
                    <label>{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_HIERARCHY_EDIT_STATUS') }}</label>
                    <select v-model="hierarchy.status">
                      <option v-for="state in states" :value="state.step">
                        {{ state.value }}
                      </option>
                    </select>
                  </div>
                  <div class="visible_status mt-2">
                    <label>{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_HIERARCHY_VISIBLE_STATUS') }}</label>
                    <multiselect v-model="hierarchy.visible_status" label="value" track-by="step" :options="states"
                        :multiple="true"
                        :taggable="false"
                        :placeholder="translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_SELECT_VALUE')"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :searchable="false"
                        :allow-empty="true"
                    ></multiselect>
                  </div>
                  <div class="visible_hierarchies mt-2">
                    <label>{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_HIERARCHY_VISIBLE_HIERARCHIES') }}</label>
                    <multiselect v-model="hierarchy.visible_hierarchy_ids" label="label" track-by="id" :options="hierarchiesOpts"
                        :multiple="true"
                        :taggable="false"
                        :placeholder="translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_SELECT_VALUE')"
                        :close-on-select="true"
                        :clear-on-select="false"
                        :searchable="false"
                        :allow-empty="true"></multiselect>
                  </div>
                  <div class="published mt-2">
                    <label>{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_HIERARCHY_PUBLISHED') }}</label>
                    <div class="flex flex-row items-center gap-2">
                      <input type="radio" :id="'hierarchy-published-' + hierarchy.id + '-yes'" :name="'hierarchy-published-' + hierarchy.id" value="1" v-model="hierarchy.published"/>
                      <label class="!m-0" :for="'hierarchy-published-' + hierarchy.id + '-yes'">{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_YES') }}</label>
                    </div>
                    <div class="flex flex-row items-center gap-2">
                      <input type="radio" :id="'hierarchy-published-' + hierarchy.id + '-no'" :name="'hierarchy-published-' + hierarchy.id" value="0" v-model="hierarchy.published"/>
                      <label class="!m-0" :for="'hierarchy-published-' + hierarchy.id + '-no'">{{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_NO') }}</label>
                    </div>
                  </div>
                </div>
                <div class="flex flex-row justify-end mt-2">
                  <button class="em-primary-button w-fit" @click="saveHierarchy(hierarchy.id)"> {{ translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_SAVE_HIERARCHY') }}</button>
                </div>
              </div>
              <div v-if="!newHierarchy" class="flex flex-row justify-end mt-4">
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
import Multiselect from 'vue-multiselect';

import rankingService from '@/services/ranking';
import userService from '@/services/user';
import fileService from '@/services/file';

export default {
  name: 'RankingTool',
  props: {},
  components: {
    Multiselect
  },
  data() {
    return {
      hierarchies: [],
      profiles: [],
      states: [],
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
        package_end_date_field: "",
        visible_status: [],
        visible_hierarchy_ids: [],
        profiles: []
      }
    }
  },
  created() {
    this.initialise();
  },
  methods: {
    async initialise() {
      this.loading = true;
      await this.getProfiles();
      await this.getStates();
      await this.getHierarchies();
      this.loading = false;
    },
    getHierarchies() {
      return rankingService.getHierarchies().then(response => {
        let hierarchies = response.data;

        hierarchies.forEach((hierarchy) => {
          // visible status is currently an array of integers, we need to convert it to an array of objects
          hierarchy.visible_status = hierarchy.visible_status.length > 0 ? hierarchy.visible_status.map((status) => {
            return this.states.find((state) => {
              return state.step === status;
            });
          }) : [];

          // profiles is currently an array of integers, we need to convert it to an array of objects
          hierarchy.profiles = hierarchy.profiles.length > 0 ? hierarchy.profiles.map((profile_id) => {
            return this.profiles.find((profile) => {
              return profile.id === profile_id;
            });
          }) : [];

          // visible hierarchy ids is currently an array of integers, we need to convert it to an array of objects
          hierarchy.visible_hierarchy_ids = hierarchy.visible_hierarchy_ids.length > 0 ? hierarchy.visible_hierarchy_ids.map((id) => {
            return hierarchies.find((hierarchy_data) => {
              return hierarchy_data.id === id;
            });
          }) : [];
        });

        this.hierarchies = response.data;
      });
    },
    getProfiles() {
      return userService.getNonApplicantProfiles().then(response => {
        this.profiles = response.data;
      });
    },
    getStates() {
      return fileService.getAllStatus().then(response => {
        this.states = response.states;
      });
    },
    addHierarchy() {
      if (!this.newHierarchy) {
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
              Swal.fire({
                type: 'success',
                title: this.translate('COM_EMUNDUS_ONBOARD_SETTINGS_RANKING_HIERARCHY_SAVED'),
                showConfirmButton: false,
                timer: 1500
              });

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
      return this.hierarchies.find((hierarchy) => {
        return hierarchy.id === 'tmp';
      });
    },
    hierarchiesOpts() {
      return this.hierarchies.map((hierarchy) => {
        return {
          id: hierarchy.id,
          label: hierarchy.label
        }
      });
    },
    profilesOpts() {
      return this.profiles.map((profile) => {
        return {
          id: profile.id,
          label: profile.label
        }
      });
    }
  }
}
</script>

<style scoped>

</style>