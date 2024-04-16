<template>
  <div id="ranking-list">
    <header class="em-flex-space-between flex flex-row mt-4 mb-2">
      <div id="header-left" class="flex flex-row items-center">
        <div id="nb-files" class="mr-2">{{ translate('COM_EMUNDUS_NB_FILES') + ' ' }} {{ rankings.nbFiles }}</div>

        <div id="pagination" class="ml-2 flex flex-row items-center">
          <select v-model="pagination.perPage" @change="updatePerPage">
            <option v-for="option in pagination.perPageOptions" :key="option" :value="option">{{ translate('COM_EMUNDUS_DISPLAY') }} {{ option }}</option>
          </select>
        </div>
      </div>
      <div id="header-right" class="flex flex-row" v-show="nbPagesMax > 1">
        <!-- pagination navigation -->
        <span id="prev" class="material-icons-outlined cursor-pointer" @click="changePage('-1')">keyboard_arrow_left</span>
        <span id="position"> {{ pagination.page }} / {{ nbPagesMax }}</span>
        <span id="next" class="material-icons-outlined cursor-pointer" @click="changePage('1')">keyboard_arrow_right</span>
      </div>
    </header>
    <div id="btns-section" class="flex flex-row justify-end mb-8">
      <button v-if="rankingsToLock" id="ask-to-lock-ranking" class="em-secondary-button w-fit cursor-pointer" @click="askToLockRankings">
        <span class="material-icons-outlined em-mr-4">lock</span>
        {{ translate('COM_EMUNDUS_CLASSEMENT_ASK_LOCK_RANKING') }}
      </button>
      <button v-if="!ismyRankingLocked && rankings.myRanking.length > 0" id="lock-ranking" class="em-primary-button em-ml-4 w-fit cursor-pointer" @click="lockRanking">
        <span class="material-icons-outlined em-mr-4">check_circle_outline</span>
        {{ translate('COM_EMUNDUS_CLASSEMENT_LOCK_RANKING') }}
      </button>
    </div>
    <p class="w-full alert mb-2" v-if="ordering.orderBy !== 'default'">{{ translate('COM_EMUNDUS_RANKING_CANNOT_DRAG_AND_DROP') }}</p>
    <div v-if="rankings.myRanking && rankings.myRanking.length > 0" id="ranking-lists-container" class="em-flex-row em-flex-space-between">
      <div id="my-ranking-list"
           class="w-full mr-2"
           :class="{'dragging': dragging}"
      >
        <table id="ranked-files" class="w-full">
          <thead>
            <tr>
              <th>
                <span class="material-icons-outlined" v-if="ismyRankingLocked">lock</span>
                <span class="material-icons-outlined" v-else>lock_open</span>
              </th>
              <th>{{ translate('COM_EMUNDUS_CLASSEMENT_FILE') }}</th>
              <th @click="reorder('default')">
                <div class="flex flex-row items-center">
                  <span>{{ translate('COM_EMUNDUS_CLASSEMENT_YOUR_RANKING') }}</span>
                  <div v-if="ordering.orderBy === 'default'">
                    <span class="material-icons-outlined" v-if="ordering.order === 'ASC'">arrow_drop_up</span>
                    <span class="material-icons-outlined" v-else>arrow_drop_down</span>
                  </div>
                </div>
              </th>
              <th>{{ translate('COM_EMUNDUS_RANKING_FILE_STATUS') }}</th>
            </tr>
          </thead>
          <!-- only ranked files -->
          <draggable
              name="my_ranking"
              tag="tbody"
              v-model="rankedFiles"
              id="ranked-files-list"
              group="ranked-files-list"
              :sort="true"
              class="draggables-list"
              @start="dragging = true"
              @end="onDragEnd"
              handle=".handle"
          >
            <tr v-for="file in rankedFiles" :key="file.id" :data-file-id="file.id" class="ranked-file">
              <td>
                <span class="material-icons-outlined" v-if="file.locked == 1">lock</span>
                <span class="material-icons-outlined" v-else>lock_open</span>
                <span class="material-icons-outlined handle" v-if="file.locked != 1 && ordering.orderBy === 'default'">drag_indicator</span>
              </td>
              <td class="em-flex-column file-identifier em-pointer" @click="openClickOpenFile(file)">
                <span>{{ file.applicant }}</span>
                <span class="em-neutral-600-color em-font-size-14">{{ file.fnum }}</span>
              </td>
              <td v-if="!ismyRankingLocked && file.locked != 1">
                <select v-model="file.rank" @change="onChangeRankValue(file)">
                  <option value="-1">{{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}</option>
                  <option v-for="i in maxRankValueAvailable" :key="i">{{ i }}</option>
                </select>
              </td>
              <td v-else>
                <span v-if="file.rank > 0">{{ file.rank }}</span>
                <span v-else> {{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }} </span>
              </td>
              <td><span v-html="getStatusTag(file.status)"></span></td>
            </tr>
          </draggable>
          <draggable
              v-model="unrankedFiles"
              tag="tbody"
              id="unranked-files-list"
              class="draggables-list"
              :group="{name: 'ranked-files-list', pull: true, put: false}"
              :sort="false"
              @start="dragging = true"
              @end="onDragEnd"
          >
            <tr v-for="file in unrankedFiles" :key="file.id" :data-file-id="file.id" class="unranked-file">
              <td>
                <span class="material-icons-outlined" v-if="file.locked">lock</span>
                <span class="material-icons-outlined" v-else>lock_open</span>
                <span class="material-icons-outlined handle" v-if="file.locked != 1 && ordering.orderBy === 'default'">drag_indicator</span>
              </td>
              <td class="em-flex-column file-identifier em-pointer" @click="openClickOpenFile(file)">
                <span>{{ file.applicant }}</span>
                <span class="em-neutral-600-color em-font-size-14">{{ file.fnum }}</span>
              </td>
              <td v-if="!ismyRankingLocked && file.locked != 1">
                <select v-model="file.rank" @change="onChangeRankValue(file)">
                  <option value="-1">{{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}</option>
                  <option v-for="i in (maxRankValueAvailableForNotRanked)" :key="i">{{ i }}</option>
                </select>
              </td>
              <td v-else>
                {{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}
              </td>
              <td><span v-html="getStatusTag(file.status)"></span></td>
            </tr>
          </draggable>
        </table>
      </div>
      <div v-if="rankings.otherRankings.length > 0" id="other-ranking-lists" class="w-full em-border-neutral-300">
        <table class="w-full">
          <thead>
          <template v-for="hierarchy in rankings.otherRankings" :key="hierarchy.hierarchy_id">
            <th @click="reorder(hierarchy.hierarchy_id)" :title="hierarchy.label">
              <div class="flex flex-row items-center">
                <span>{{ hierarchy.label }}</span>
                <div v-if="ordering.orderBy === hierarchy.hierarchy_id">
                  <span class="material-icons-outlined" v-if="ordering.order == 'ASC'">arrow_drop_up</span>
                  <span class="material-icons-outlined" v-else>arrow_drop_down</span>
                </div>
              </div>
            </th>
            <th :title="translate('COM_EMUNDUS_RANKING_RANKER') + ' ' + hierarchy.label" class="border-right">
              <div><span>{{ translate('COM_EMUNDUS_RANKING_RANKER') + ' ' + hierarchy.label }}</span></div>
            </th>
          </template>
          </thead>
          <tbody>
          <!-- 1 ligne par fichier, 2 colonnes par hiérarchie (1 de classement, et une pour connaître le classeur) -->
          <tr v-for="file in orderedRankings" :key="file.id">
            <template v-for="hierarchy in rankings.otherRankings" :key="file.id + '-' + hierarchy.hierarchy_id">
              <td>
                  <span class="material-icons-outlined em-mr-4"
                        v-if="rankings.otherRankings.groupedByFiles[file.id]
                        && rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id]
                        && rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id].locked == 1">
                    lock
                  </span>
                <span v-else class="material-icons-outlined em-mr-4">lock_open</span>
                <span v-if="rankings.otherRankings.groupedByFiles[file.id]
                    && rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id]
                    && rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id].rank != -1">
                    {{ rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id].rank }}
                  </span>
                <span v-else>{{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}</span>
              </td>
              <td class="border-right">
                  <span
                      v-if="rankings.otherRankings.groupedByFiles[file.id] && rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id]">
                    {{
                      hierarchy.rankers[rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id].ranker_id].name
                    }}
                  </span>
                <span v-else>-</span>
              </td>
            </template>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-else id="empty-lists">
      <p>{{ translate('COM_EMUNDUS_RANKING_NO_FILES') }}</p>
    </div>
    <transition name="fade">
      <compare-files
          v-if="defaultFile != null && context !== 'modal'"
          :user="user"
          :default-file="defaultFile"
          :default-comparison-file="selectedOtherFile"
          :files="rankings.myRanking"
          :tabs="fileTabs"
          title="COM_EMUNDUS_CLASSEMENT_MODAL_COMPARISON_HEADER_TITLE"
          @comparison-file-changed="onComparisonFileChanged"
      >
        <template v-slot:before-default-file-tabs>
          <div class="em-flex-row em-ml-8 em-mt-8">
            <label class="em-mr-4"> {{ translate('COM_EMUNDUS_CLASSEMENT_RANKING_SELECT_LABEL') }} </label>
            <select name="default-file-select" v-model="defaultFile.rank" @change="onChangeRankValue(defaultFile)">
              <option value="-1">{{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}</option>
              <option v-for="i in (maxRankValueAvailable)" :key="i">{{ i }}</option>
            </select>
          </div>
        </template>
        <template v-slot:before-compare-file-tabs>
          <div class="em-flex-row em-ml-8 em-mt-8">
            <label class="em-mr-4"> {{ translate('COM_EMUNDUS_CLASSEMENT_RANKING_SELECT_LABEL') }} </label>
            <select v-if="selectedOtherFile" v-model="selectedOtherFile.rank"
                    @change="onChangeRankValue(selectedOtherFile)">
              <option value="-1">{{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}</option>
              <option v-for="i in (maxRankValueAvailable)" :key="i">{{ i }}</option>
            </select>
          </div>
        </template>
        <template v-slot:files-to-compare-with>
          <classement :key="subRankingKey" @other-selected-file="onSelectOtherFile" :hierarchy_id="hierarchy_id"
                      :user="user" context="modal" :showOtherHierarchies="false"></classement>
        </template>
      </compare-files>
    </transition>
    <modal name="askToLockRankings" id="ask-to-lock-rankings-modal">
      <div class="em-flex-column em-p-16">
        <div class="swal2-header em-mb-16">
          <h2 id="swal2-title" class="swal2-title em-swal-title">{{ translate('COM_EMUNDUS_CLASSEMENT_ASK_LOCK_RANKING') }}</h2>
        </div>
        <div class="swal2-content em-mt-16" style="z-index: 2;">
          <label>{{ translate('COM_EMUNDUS_CLASSEMENT_ASK_HIERARCHIES_LOCK_RANKING') }}</label>
          <multiselect
              v-model="askedHierarchiesToLockRanking"
              label="label"
              track-by="hierarchy_id"
              :options="rankings.otherRankings"
              :multiple="true"
              :searchable="true"
              :close-on-select="true"
              :clear-on-select="true"
          ></multiselect>

          <label class="em-mt-16">{{ translate('COM_EMUNDUS_CLASSEMENT_ASK_USERS_LOCK_RANKING') }}</label>
          <multiselect
              v-model="askedUsersToLockRanking"
              label="name"
              track-by="user_id"
              :options="otherRankingsRankers"
              :multiple="true"
              :searchable="true"
              :close-on-select="true"
              :clear-on-select="true"
          ></multiselect>
        </div>
        <div class="swal2-actions">
          <button id="cancelAskLockRanking" class="swal2-cancel em-swal-cancel-button swal2-styled"
                  @click="closeAskRanking">
            {{ translate('COM_EMUNDUS_CLASSEMENT_CANCEL_ASK_LOCK_RANKING') }}
          </button>
          <button id="confirmAskLockRanking" class="swal2-confirm em-swal-confirm-button swal2-styled"
                  @click="confirmAskLockRanking">{{ translate('COM_EMUNDUS_CLASSEMENT_CONFIRM_ASK_LOCK_RANKING') }}
          </button>
        </div>
      </div>
    </modal>
  </div>
</template>

<script>
import translate from "../mixins/translate";
import rankingService from "../services/ranking.js";
import fileService from "../services/file.js";

import CompareFiles from "../components/Files/CompareFiles.vue";
import draggable from "vuedraggable";
import Multiselect from "vue-multiselect";
import Swal from "sweetalert2";

export default {
  name: 'Classement',
  components: {Multiselect, CompareFiles, draggable},
  props: {
    user: {
      type: Number,
      required: true
    },
    hierarchy_id: {
      type: Number,
      required: true
    },
    context: {
      type: String,
      default: 'page'
    },
    fileTabsStr: {
      type: String,
      default: ''
    },
    specificTabs: {
      type: String,
      default: ''
    },
    showOtherHierarchies: {
      type: Boolean,
      default: true
    },
  },
  mixins: [translate],
  data() {
    return {
      rankings: {
        nbFiles: 0,
        myRanking: [],
        otherRankings: [],
        maxRankValue: 0,
      },
      defaultFile: null,
      selectedOtherFile: null,
      locked: false,
      subRankingKey: 0,
      askedHierarchiesToLockRanking: [],
      askedUsersToLockRanking: [],
      fileTabs: [],
      loading: false,
      dragging: false,
      pagination: {
        page: 1,
        perPage: 10,
        perPageOptions: [5, 10, 25, 50, 100]
      },
      ordering: {
        orderBy: 'default',
        order: 'ASC'
      },
      emundusStatus: []
    }
  },
  created() {
    // check session value for pagination options
    const perPage = sessionStorage.getItem('rankingPerPage');
    if (perPage && !isNaN(perPage)) {
      if (this.pagination.perPageOptions.includes(parseInt(perPage))) {
        this.pagination.perPage = parseInt(perPage);
      } else {
        this.pagination.perPage = 10;
      }
    }

    this.getEmundusStatus();
    this.getRankings();
    this.getOtherHierarchyRankings();
    this.addFilterEventListener();

    if (this.fileTabsStr.length > 0) {
      // explode the string to get the tabs
      let tmpTabs = this.fileTabsStr.split(',');

      tmpTabs.forEach(tab => {
        switch(tab) {
          case 'forms':
            this.fileTabs.push({
              label: this.translate('COM_EMUNDUS_FILES_APPLICANT_FILE'),
              name: 'application',
              access: '1'
            });
            break;
          case 'attachments':
            this.fileTabs.push({
              label: this.translate('COM_EMUNDUS_FILES_ATTACHMENTS'),
              name: 'attachments',
              access: '4'
            });
            break;
          case 'comments':
            this.fileTabs.push({
              label: this.translate('COM_EMUNDUS_FILES_COMMENTS'),
              name: 'comments',
              access: '10'
            });
            break;
          case 'evaluation':
            this.fileTabs.push({
              label: this.translate('COM_EMUNDUS_FILES_EVALUATION'),
              name: 'evaluation',
              access: '5'
            });
            break;
          case 'decision':
            this.fileTabs.push({
              label: this.translate('COM_EMUNDUS_FILES_DECISION'),
              name: 'decision',
              access: '29'
            });
            break;
          case 'admission':
            this.fileTabs.push({
              label: this.translate('COM_EMUNDUS_FILES_ADMISSION'),
              name: 'admission',
              access: '32'
            });
            break;
        }
      });
    }

    if (this.specificTabs.length > 0) {
      let tmpTabs = JSON.parse(this.specificTabs);
      tmpTabs.forEach(tab => {
        const uniqueName = tab.label.toLowerCase().replace(/ /g, '-');

        this.fileTabs.push({
          label: tab.label,
          name: uniqueName,
          access: 1,
          url: tab.url
        });
      });
    }
  },
  methods: {
    addFilterEventListener() {
      window.addEventListener('emundus-start-apply-filters', () => {
        this.loading = true;
      });

      window.addEventListener('emundus-apply-filters-success', () => {
        this.getRankings();
        this.getOtherHierarchyRankings();
        document.querySelector('.em-page-loader').classList.add('hidden');
        this.loading = false;
      });
    },
    changePage(direction) {
      const oldPage = this.pagination.page;

      if (direction === '-1' && this.pagination.page > 1) {
        this.pagination.page--;
      } else if (direction === '1' && this.pagination.page < this.nbPagesMax) {
        this.pagination.page++;
      }

      if (oldPage !== this.pagination.page) {
        this.getRankings();
      }
    },
    updatePerPage() {
      sessionStorage.setItem('rankingPerPage', this.pagination.perPage);
      this.getRankings(true);
    },
    /**
     * @param orderBy
     */
    reorder(orderBy) {
      if (this.ordering.orderBy === orderBy) {
        this.ordering.order = this.ordering.order === 'ASC' ? 'DESC' : 'ASC';
      }

      this.ordering.orderBy = orderBy;

      this.getRankings();
    },

    /**
     *
     */
    getEmundusStatus() {
      fileService.getAllStatus().then((response) => {
        this.emundusStatus = response.states
      });
    },

    /**
     * @param resetPage {boolean} - if true, reset the page to 1, needed when changing the number of files per page
     * @returns {Promise<void>}
     */
    async getRankings(resetPage = false) {
      if (resetPage) {
        this.pagination.page = 1;
      }

      return await rankingService.getMyRanking(this.pagination, this.ordering).then(response => {
        if (response.status) {
          this.rankings.myRanking = response.data.data;
          this.rankings.nbFiles = response.data.total;
          this.rankings.maxRankValue = response.data.maxRankValue == -1 ? 0 : response.data.maxRankValue;
        }
      });
    },
    getOtherHierarchyRankings() {
      if (this.showOtherHierarchies) {
        rankingService.getOtherHierarchyRankings().then(response => {
          if (response.status) {
            this.rankings.otherRankings = response.data;

            // create a list of the files with all ranking values for each hierarchy
            let rankingGroupedByFiles = {};
            response.data.forEach(hierarchy => {
              hierarchy.files.forEach(file => {
                if (!rankingGroupedByFiles[file.id]) {
                  rankingGroupedByFiles[file.id] = {};
                }

                rankingGroupedByFiles[file.id][hierarchy.hierarchy_id] = file;
              });
            });

            this.rankings.otherRankings.groupedByFiles = rankingGroupedByFiles;
          }
        });
      }
    },
    onChangeRankValue(file) {
      if (file.locked == 1) {
        Swal.fire({
          title: this.translate('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_TITLE'),
          text: this.translate('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_LOCKED'),
          icon: 'error',
          customClass: {
            title: 'em-swal-title',
            confirmButton: 'em-swal-confirm-button',
          },
        });
        this.getRankings();
      } else {
        this.subRankingKey++;
        rankingService.updateRanking(file.id, file.rank, this.hierarchy_id).then(response => {
          if (!response.status) {
            Swal.fire({
              title: this.translate('COM_EMUNDUS_RANKING_UPDATE_RANKING_ERROR_TITLE'),
              text: this.translate(response.msg),
              icon: 'error',
              customClass: {
                title: 'em-swal-title',
                confirmButton: 'em-swal-confirm-button',
              },
            });
          }

          this.getRankings().then(() => {
            if (this.defaultFile && this.defaultFile.id) {
              this.defaultFile = this.rankings.myRanking.find(f => f.id === this.defaultFile.id);
            }

            if (this.selectedOtherFile && this.selectedOtherFile.id) {
              this.selectedOtherFile = this.rankings.myRanking.find(f => f.id === this.selectedOtherFile.id);
            }
          });
        });
      }
    },
    askToLockRankings() {
      if(this.rankingsToLock && this.rankings.myRanking.length > 0) {
        this.$modal.show('askToLockRankings', {
          rankingsToLock: this.rankingsToLock
        });
      }
    },
    confirmAskLockRanking() {
      if (this.askedUsersToLockRanking.length > 0 || this.askedHierarchiesToLockRanking) {
        let userIds = this.askedUsersToLockRanking.map((user) => {
          return user.user_id
        });

        let hierarchyIds = this.askedHierarchiesToLockRanking.map((hierarchy) => {
          return hierarchy.hierarchy_id
        });

        rankingService.askToLockRankings(userIds, hierarchyIds).then((response) => {
          if (response.status) {
            this.$modal.hide('askToLockRankings');

            // response data contains the list of emails that have been asked to lock the ranking
            if (response.data.length > 0) {
              const emails_html = response.data.map(email => `<li>${email}</li>`).join('');
              Swal.fire({
                title: this.translate('COM_EMUNDUS_RANKING_LOCK_RANKING_ASK_CONFIRM_SUCCESS_TITLE'),
                html: `<ul>${emails_html}</ul>`,
                icon: 'success',
                delay: 5000,
                customClass: {
                  title: 'em-swal-title',
                  confirmButton: 'em-swal-confirm-button',
                },
              });
            }
          }
        });

        this.closeAskRanking();
      }
    },
    closeAskRanking() {
      this.$modal.hide('askToLockRankings');
      this.askedUsersToLockRanking = [];
      this.askedHierarchiesToLockRanking = [];
    },
    lockRanking() {
      Swal.fire({
        title: this.translate('COM_EMUNDUS_RANKING_LOCK_RANKING_CONFIRM_TITLE'),
        text: this.translate('COM_EMUNDUS_RANKING_LOCK_RANKING_CONFIRM_TEXT'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: this.translate('COM_EMUNDUS_RANKING_LOCK_RANKING_CONFIRM_YES'),
        cancelButtonText: this.translate('COM_EMUNDUS_RANKING_LOCK_RANKING_CONFIRM_NO'),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
      }).then((result) => {
        if (result.value) {
          this.lockRankingConfirmed();
        }
      });
    },
    lockRankingConfirmed() {
      rankingService.lockRanking(this.hierarchy_id, 1).then(response => {
        if (response.status) {
          this.rankings.myRanking.forEach(file => {
            file.locked = 1;
          });
        }
      });
    },
    openClickOpenFile(file) {
      if (this.context === 'modal') {
        // dispatch event to open the file
        window.dispatchEvent(new CustomEvent('openSecondaryFile', {detail: {file: file}}));
        this.$emit('other-selected-file', file);
      } else {

        this.defaultFile = file;
        // wait for defaultFile to be set
        setTimeout(() => {
          this.$modal.show('compareFiles');
        }, 100);
      }
    },
    onSelectOtherFile(file) {
      this.selectedOtherFile = file;
    },
    onComparisonFileChanged(defaultFile, selectedFileToCompareWith) {
      this.defaultFile = defaultFile;
      this.selectedOtherFile = selectedFileToCompareWith;
    },
    /**
     * Drag & drop functions
     */
    onDragEnd(e) {
      this.dragging = false;
      if (e.to.id != 'ranked-files-list') {
        return;
      }

      const itemId = e.item.dataset.fileId;

      if (itemId) {

        // find the file in the list
        let file = this.rankings.myRanking.find(f => f.id == itemId);

        if (file) {
          // get rank value at the new index
          // index can be inside of rankedFiles or unrankedFiles
          let newIndex = e.newIndex;
          let newRank = -1;

          if (newIndex < this.rankedFiles.length) {
            newRank = this.rankedFiles[newIndex].rank;
          } else {
            // if new index is superior to the number of ranked files by 1, then the new rank is the max rank value available for not ranked files
            // else do not change the rank value
            newRank = this.maxRankValueAvailableForNotRanked;
          }

          if (file.rank != newRank) {
            file.rank = newRank;
            this.onChangeRankValue(file);
          }
        }
      }
    },
    getStatusTag(statusId) {
      let status = this.emundusStatus.find(s => s.step == statusId);

      if (status) {
        return `<span class="label label-${status.class}">${status.value}</span>`;
      }

      return '';
    }
  },
  computed: {
    nbPagesMax() {
      return this.rankings.nbFiles > 0 ? Math.ceil(this.rankings.nbFiles / this.pagination.perPage) : 1;
    },
    maxRankValue() {
      return this.rankings.maxRankValue;
    },
    maxRankValueAvailable() {
      // max rank value available is the max rank in the list + 1, if all of them are at -1, then it's 1
      if (this.maxRankValue === 0) {
        return 1;
      }

      // max rank can not be higher than the number of files
      if (this.maxRankValue > this.rankings.nbFiles) {
        return this.rankings.nbFiles;
      }

      return this.maxRankValue;
    },
    maxRankValueAvailableForNotRanked() {
      if (this.maxRankValueAvailable != this.rankings.nbFiles && this.maxRankValue > 0) {
        return this.maxRankValueAvailable + 1;
      } else {
        return this.maxRankValueAvailable;
      }
    },
    unrankedFiles() {
      return this.ordering.orderBy === 'default' ? this.rankings.myRanking.filter(file => file.rank == -1) : [];

    },
    rankedFiles() {
      return this.ordering.orderBy === 'default' ? this.rankings.myRanking.filter(file => file.rank != -1) : this.rankings.myRanking;
    },
    orderedRankings() {
      // rankedFiles first, then unrankedFiles
      return this.rankedFiles.concat(this.unrankedFiles);
    },
    ismyRankingLocked() {
      return this.rankings.myRanking.length > 0 ? this.rankings.myRanking.every(file => file.locked == 1) : false;
    },
    rankingsToLock() {
      let rankingToLock = false;

      this.rankings.otherRankings.forEach(hierarchy => {
        if (hierarchy && hierarchy.files) {
          const found = hierarchy.files.find(file => {
            return file.locked == 0;
          });

          if (found) {
            rankingToLock = true;
          }
        }
      });

      return rankingToLock;
    },
    otherRankingsRankers() {
      let rankers = [];
      let ranker_ids = [];

      this.rankings.otherRankings.forEach((ranking) => {
        Object.keys(ranking.rankers).forEach(ranker_id => {
          if (!ranker_ids.includes(ranker_id)) {
            ranker_ids.push(ranker_id);
            rankers.push(ranking.rankers[ranker_id]);
          }
        });
      });

      return rankers;
    }
  }
}
</script>

<style lang="scss">
.alert.mb-2 {
  margin-bottom: var(--em-spacing-2) !important;
}

#ranking-list {
  #ranking-lists-container {
    align-items: flex-start;

    .file-identifier {
      align-items: flex-start;
    }

    tr, td {
      height: 64px;
      white-space: nowrap;
    }

    table:not(#unranked-files) th {
      height: 98px;

    }

    #my-ranking-list, #other-ranking-lists {
      border-radius: 4px;
      border-spacing: 0;
      border-collapse: separate;
    }

    #other-ranking-lists {
      overflow: auto;

      th div {
        max-height: 2.5em;
        overflow: hidden;
        text-overflow: ellipsis;
        font-weight: bold;
      }

      th.border-right, td.border-right {
        border-right: 1px solid var(--neutral-300);
      }
    }

    table#ranked-files {
      border-bottom: 0;
    }

    #my-ranking-list {
      border: solid var(--main-200);

      thead th {
        background-color: var(--main-100) !important;
      }

      tbody td, tbody tr {
        background-color: var(--main-50);
        border: 0;
      }
    }
  }

  button.em-primary-button {
    span {
      color: var(--neutral-0);
    }

    &:hover {
      span {
        color: var(--main-500);
      }
    }
  }

  button.em-secondary-button {
    span {
      color: var(--em-coordinator-secondary-color);
    }

    &:hover {
      span {
        color: var(--neutral-0);
      }
    }
  }

  .handle:hover {
    cursor: grab;
  }

  .dragging {
    cursor: grabbing;
  }

  .dragging #ranked-files tbody#ranked-files-list {
    border: 4px dashed var(--main-200);
  }

  .dragging #unranked-files-list td {
    background-color: var(--grey-bg-color) !important;
  }
}

#ask-to-lock-rankings-modal .v--modal {
  overflow: unset !important;
  border-radius: .3125em !important;
  height: fit-content !important;
}
</style>