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
        <button id="lock-ranking" class="em-primary-button em-ml-4">
          <span class="material-icons-outlined">check_circle_outline</span>
          {{ translate('COM_EMUNDUS_CLASSEMENT_LOCK_RANKING') }}
        </button>
      </div>
    </header>
    <div id="ranking-lists-container" class="em-flex-row em-flex-space-between">
      <div id="my-ranking-list">
        <table>
          <thead>
            <th>
              <span class="material-icons-outlined" v-if="locked">lock_open</span>
              <span class="material-icons-outlined" v-else>lock</span>
            </th>
            <th>{{ translate('COM_EMUNDUS_CLASSEMENT_FILE') }}</th>
            <th>{{ translate('COM_EMUNDUS_CLASSEMENT_YOUR_RANKING') }}</th>
          </thead>
          <tbody name="my_ranking" is="transition-group">
            <!-- only ranked files -->
            <tr v-for="file in rankedFiles" :key="file.id" class="ranked-file">
              <td>
                <span class="material-icons-outlined" v-if="file.locked">lock</span>
                <span class="material-icons-outlined" v-else>lock_open</span>
              </td>
              <td>{{ file.applicant }}</td>
              <td>
                <select v-model="file.rank" @change="onChangeRankValue(file)">
                  <option value="-1">{{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}</option>
                  <option v-for="i in maxRankValueAvailable" :key="i">{{ i }}</option>
                </select>
              </td>
            </tr>
            <!-- non ranked files -->
            <tr v-for="file in unrankedFiles" :key="file.id" class="unranked-file">
              <td>
                <span class="material-icons-outlined" v-if="file.locked">lock</span>
                <span class="material-icons-outlined" v-else>lock_open</span>
              </td>
              <td>{{ file.applicant }}</td>
              <td>
                <select v-model="file.rank" @change="onChangeRankValue(file)">
                  <option value="-1">{{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}</option>
                  <option v-for="i in (maxRankValueAvailable+1)" :key="i">{{ i }}</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div id="other-ranking-lists">

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
    this.getRankings()
  },
  methods: {
    getRankings() {
      rankingService.getMyRanking().then(response => {
        if (response.status) {
          this.rankings.nbFiles = response.data.length;
          this.rankings.myRanking = response.data;
        } else {

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
  }
}
</script>

<style scoped>
.my_ranking-enter-active, .my_ranking-leave-active {
  transition: all 1s;
}
.my_ranking-enter, .my_ranking-leave-to{
  opacity: 0;
  transform: translateX(30px);
}
.my_ranking-move {
  transition: transform 1s;
}
</style>