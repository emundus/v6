<template>
  <div>
    <transition :name="'slide-down'" type="transition">
      <div class="em-flex-row">
          <div class="em-flex-row em-list-filters em-ml-8" v-if="data.type === 'campaign'">
            <span class="material-icons-outlined em-ml-4">schedule</span>
            <select v-model="filtre" name="selectFiltre" class="list-vue-select">
              <option value="all">{{ translate("COM_EMUNDUS_ONBOARD_FILTER_ALL") }}</option>
              <option value="yettocome">{{ translate("COM_EMUNDUS_CAMPAIGN_YET_TO_COME") }}</option>
              <option value="ongoing">{{ translate("COM_EMUNDUS_CAMPAIGN_ONGOING") }}</option>
              <option value="Terminated">{{ translate("COM_EMUNDUS_ONBOARD_FILTER_CLOSE") }}</option>
              <option value="Publish">{{ translate("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH") }}</option>
              <option value="Unpublish">{{ translate("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH") }}</option>
            </select>
          </div>
          <div class="em-flex-row em-list-filters em-ml-8">
            <span class="material-icons-outlined em-ml-4">sort</span>
            <select v-model="afficher" name="selectAfficher" class="list-vue-select">
              <option value="max">{{ translate("COM_EMUNDUS_ONBOARD_ALL_RESULTS") }}</option>
              <option value="10">{{ translate("COM_EMUNDUS_ONBOARD_RESULTS") }} 10</option>
              <option value="25">{{ translate("COM_EMUNDUS_ONBOARD_RESULTS") }} 25</option>
              <option value="50">{{ translate("COM_EMUNDUS_ONBOARD_RESULTS") }} 50</option>
              <option value="100">{{ translate("COM_EMUNDUS_ONBOARD_RESULTS") }} 100</option>
            </select>
          </div>
      </div>
    </transition>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
export default {
  name: "action_menu",

  props: {
    data: Object,
    updateTotal: Function,
    filter: Function,
    sort: Function,
    cherche: Function,
    chercheGo: Function,
    validateFilters: Function,
    nbresults: Function,
  },

  computed: {
    checkItem() {
      return this.$store.getters['lists/selectedItems'];
    }
  },

  data() {
    return {
      recherche: "",
      filterHover: false,
      actionHover: false,
      sortHover: false,
      resultsHover: false,
      loading: false,

      filtre: "Publish",
      tri: "DESC",
      afficher: 25,
    };
  },

  methods: {
    updateFilter(filter) {
      this.filtre = filter;
    },

    updateSort(sort) {
      this.tri = sort;
    },

    updateDisplay(lim) {
      this.afficher = lim;
    },
  },
  watch:{
    filtre: function(value){
      this.filter(value);
      this.updateFilter(value);
    },
    afficher: function(value){
      if(value === 'max'){
        this.nbresults(999999);
      } else {
        this.nbresults(value);
      }
      this.updateDisplay(value);
    }
  }
};
</script>

<style lang="scss" scoped>
.em-list-filters{
  background: white;
  border: solid 1px #e0e0e5;
  border-radius: 5px;
  padding: 4px;
  .list-vue-select{
    height: 35px;
    margin-bottom: 0;
    border: unset;
    background: transparent;
    color: var(--neutral-800);
    &:focus{
      outline: unset;
    }
  }
}
</style>
