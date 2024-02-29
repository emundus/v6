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
          <tbody>
            <tr v-for="file in rankings.myRanking" :key="file.id">
              <td>
                <span class="material-icons-outlined" v-if="file.locked">lock</span>
                <span class="material-icons-outlined" v-else>lock_open</span>
              </td>
              <td>{{ file.applicant }}</td>
              <td>
                <select v-model="file.rank">
                  <option value="-1">{{ translate('COM_EMUNDUS_CLASSEMENT_NOT_RANKED') }}</option>
                  <option v-for="i in rankings.nbFiles" :key="i">{{ i }}</option>
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
    }
  }
}
</script>

<style scoped>

</style>