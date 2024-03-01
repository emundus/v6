<template>
  <div id="ranking-list">
    <header class="em-flex-space-between em-flex-row em-mb-8">
      <div id="header-left">
        <div id="nb-files">{{ translate('COM_EMUNDUS_NB_FILES') + ' ' }} {{ nbFiles }}</div>
        <div id="pagination"></div>
      </div>
      <div id="header-left" class="em-flex-row">
        <button id="ask-to-lock-ranking" class="em-secondary-button">
          <span class="material-icons-outlined">lock</span>
          {{ translate('COM_EMUNDUS_CLASSEMENT_ASK_LOCK_RANKING') }}
        </button>
        <button v-if="!ismyRankingLocked" id="lock-ranking" class="em-primary-button em-ml-4" @click="lockRanking">
          <span class="material-icons-outlined">check_circle_outline</span>
          {{ translate('COM_EMUNDUS_CLASSEMENT_LOCK_RANKING') }}
        </button>
      </div>
    </header>
    <div id="ranking-lists-container" class="em-flex-row em-flex-space-between">
      <div id="my-ranking-list" class="em-w-100">
        <table class="em-w-100">
          <thead>
          <th>
            <span class="material-icons-outlined" v-if="ismyRankingLocked">lock</span>
            <span class="material-icons-outlined" v-else>lock_open</span>
          </th>
          <th>{{ translate('COM_EMUNDUS_CLASSEMENT_FILE') }}</th>
          <th>{{ translate('COM_EMUNDUS_CLASSEMENT_YOUR_RANKING') }}</th>
          </thead>
          <tbody name="my_ranking" is="transition-group">
          <!-- only ranked files -->
          <tr v-for="file in rankedFiles" :key="file.id" class="ranked-file">
            <td>
              <span class="material-icons-outlined" v-if="file.locked == 1">lock</span>
              <span class="material-icons-outlined" v-else>lock_open</span>
            </td>
            <td class="em-flex-column file-identifier">
              <span>{{ file.applicant }}</span>
              <span>{{ file.fnum }}</span>
            </td>
            <td v-if="!ismyRankingLocked">
              <select v-model="file.rank" @change="onChangeRankValue(file)">
                <option value="-1">{{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}</option>
                <option v-for="i in maxRankValueAvailable" :key="i">{{ i }}</option>
              </select>
            </td>
            <td v-else>
              <span>{{ file.rank }}</span>
            </td>
          </tr>
          <!-- non ranked files -->
          <tr v-for="file in unrankedFiles" :key="file.id" class="unranked-file">
            <td>
              <span class="material-icons-outlined" v-if="file.locked">lock</span>
              <span class="material-icons-outlined" v-else>lock_open</span>
            </td>
            <td class="em-flex-column file-identifier">
              <span>{{ file.applicant }}</span>
              <span>{{ file.fnum }}</span>
            </td>
            <td v-if="!ismyRankingLocked">
              <select v-model="file.rank" @change="onChangeRankValue(file)">
                <option value="-1">{{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}</option>
                <option v-for="i in (maxRankValueAvailable+1)" :key="i">{{ i }}</option>
              </select>
            </td>
            <td v-else>
              {{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}
            </td>
          </tr>
          </tbody>
        </table>
      </div>
      <div v-if="rankings.otherRankings.length > 0" id="other-ranking-lists" class="em-w-100">
        <table class="em-w-100">
          <thead>
          <th v-for="hierarchy in rankings.otherRankings" :key="hierarchy.hierarchy_id">
            {{ hierarchy.label }}
          </th>
          </thead>
          <tbody>
          <tr v-for="file in rankings.myRanking" :key="file.id">
            <td v-for="hierarchy in rankings.otherRankings" :key="hierarchy.hierarchy_id">
              <span class="material-icons-outlined" v-if="rankings.otherRankings.groupedByFiles[file.id] && rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id] && rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id].locked == 1">lock</span>
              <span v-else class="material-icons-outlined">lock_open</span>
              <span v-if="rankings.otherRankings.groupedByFiles[file.id] && rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id] && rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id].rank != -1">
                {{ rankings.otherRankings.groupedByFiles[file.id][hierarchy.hierarchy_id].rank }}
              </span>
              <span v-else>{{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}</span>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import translate from "../mixins/translate";
import rankingService from "../services/ranking.js";

export default {
  name: 'Classement',
  props: {
    user: {
      type: Number,
      required: true
    },
    hierarchy_id: {
      type: Number,
      required: true
    }
  },
  mixins: [translate],
  data() {
    return {
      rankings: {
        nbFiles: 0,
        myRanking: [],
        otherRankings: []
      },
      locked: false
    }
  },
  created() {
    this.getRankings();
    this.getOtherHierarchyRankings();
  },
  methods: {
    getRankings() {
      rankingService.getMyRanking().then(response => {
        if (response.status) {
          this.rankings.myRanking = response.data;
          this.rankings.nbFiles = response.data.length;
        }
      });
    },
    getOtherHierarchyRankings() {
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
    },
    onChangeRankValue(file) {
      rankingService.updateRanking(file.id, file.rank, this.hierarchy_id).then(response => {
        if (response.status) {
          this.getRankings();
        } else {

        }
      });
    },
    lockRanking() {
      rankingService.lockRanking(this.hierarchy_id, 1).then(response => {
        if (response.status) {
          // lock every file in the list
          this.rankings.myRanking.forEach(file => {
            file.locked = 1;
          });
        }
      });
    }
  },
  computed: {
    maxRankValueAvailable() {
      // max rank value available is the max rank in the list + 1, if all of them are at -1, then it's 1
      let maxRank = 0;

      this.rankings.myRanking.forEach(file => {
        if (file.rank > maxRank) {
          maxRank = Number(file.rank);
        }
      });

      if (maxRank === 0) {
        return 1;
      }

      // max rank can not be higher than the number of files
      if (maxRank > this.rankings.nbFiles) {
        return this.rankings.nbFiles;
      }

      return maxRank;
    },
    unrankedFiles() {
      return this.rankings.myRanking.filter(file => file.rank == -1);
    },
    rankedFiles() {
      return this.rankings.myRanking.filter(file => file.rank != -1);
    },
    ismyRankingLocked() {
      return this.rankings.myRanking.length > 0 ? this.rankings.myRanking.every(file => file.locked == 1) : false;
    }
  }
}
</script>

<style scoped>
.my_ranking-enter-active, .my_ranking-leave-active {
  transition: all 1s;
}

.my_ranking-enter, .my_ranking-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.my_ranking-move {
  transition: transform 1s;
}

#ranking-lists-container {
  align-items: flex-start;

  .file-identifier {
    align-items: flex-start;
  }

  tr {
    height: 56px;
  }

  th {
    height: 39px;
  }
}
</style>