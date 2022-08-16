<template>
  <div>
    <div class="container-2 w-container" style="max-width: unset;min-width: 200px">
      <transition :name="'slide-down'" type="transition">
        <div>
          <div>
              <div v-if="data.type !== 'email'">
                  <div class="filter-subtitle">
                    <div>{{ translations.Filter }}</div>
                  </div>
                  <nav aria-label="filter" class="em-flex-col-start">
                    <a @click="filter('all');updateFilter('all');"
                       :class="filtre == 'all' ? 'selected' : ''"
                       class="action-submenu">
                      {{ translations.FilterAll }}
                    </a>
                    <a v-if="data.type == 'campaign'"
                       @click="filter('yettocome');updateFilter('yettocome');"
                       :class="filtre == 'yettocome' ? 'selected' : ''"
                       class="action-submenu">
                      {{ translations.FilterYetToCome }}
                    </a>
                    <a v-if="data.type == 'campaign'"
                       @click="filter('ongoing');updateFilter('ongoing');"
                       :class="filtre == 'ongoing' ? 'selected' : ''"
                       class="action-submenu">
                      {{ translations.FilterOnGoing }}
                    </a>
                    <a v-if="data.type == 'campaign'"
                       @click="filter('Terminated');updateFilter('Terminated');"
                       :class="filtre == 'Terminated' ? 'selected' : ''"
                       class="action-submenu">
                      {{ translations.FilterClose }}
                    </a>
                    <a @click="filter('Publish');updateFilter('Publish');"
                       :class="filtre == 'Publish' ? 'selected' : ''"
                       class="action-submenu"
                       v-if="data.type !== 'formulaire'">
                      {{ translations.FilterPublish }}
                    </a>
                    <a @click="filter('Unpublish');updateFilter('Unpublish');"
                       :class="filtre == 'Unpublish' ? 'selected' : ''"
                       class="action-submenu"
                       v-if="data.type !== 'formulaire'">
                      {{ translations.FilterUnpublish }}
                    </a>
                    <a @click="filter('Unpublish');updateFilter('Unpublish');"
                       :class="filtre == 'Unpublish' ? 'selected' : ''"
                       class="action-submenu"
                       v-if="data.type === 'formulaire'">
                      {{ translations.Archived }}
                    </a>
                  </nav>
              </div>

              <div class="mt-1">
                  <div class="filter-subtitle">
                    <div>{{ translations.NbResults }}</div>
                  </div>
                  <nav aria-label="Nb Results" class="em-flex-col-start">
                    <a @click="nbresults(999999);updateDisplay('max');"
                       :class="afficher == 'max' ? 'selected' : ''"
                       class="action-submenu">
                      {{ translations.AllResults }}
                    </a>
                    <a @click="nbresults(10);updateDisplay(10);"
                       :class="afficher == 10 ? 'selected' : ''"
                       class="action-submenu">
                      10
                    </a>
                    <a @click="nbresults(25);updateDisplay(25);"
                       :class="afficher == 25 ? 'selected' : ''"
                       class="action-submenu">
                      25
                    </a>
                    <a @click="nbresults(50);updateDisplay(50);"
                       :class="afficher == 50 ? 'selected' : ''"
                       class="action-submenu">
                      50
                    </a>
                    <a @click="nbresults(100);updateDisplay(100);"
                       :class="afficher == 100 ? 'selected' : ''"
                       class="action-submenu">
                      100
                    </a>
                  </nav>
              </div>
          </div>

        </div>
      </transition>
    </div>
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
        translations: {
          NbResults: this.translate("COM_EMUNDUS_ONBOARD_RESULTS"),
          AllResults: this.translate("COM_EMUNDUS_ONBOARD_ALL_RESULTS"),
          Sort: this.translate("COM_EMUNDUS_ONBOARD_SORT"),
          SortCreasing: this.translate("COM_EMUNDUS_ONBOARD_SORT_CREASING"),
          SortDecreasing: this.translate("COM_EMUNDUS_ONBOARD_SORT_DECREASING"),
          Filter: this.translate("COM_EMUNDUS_ONBOARD_FILTER"),
          FilterAll: this.translate("COM_EMUNDUS_ONBOARD_FILTER_ALL"),
          FilterOpen: this.translate("COM_EMUNDUS_ONBOARD_FILTER_OPEN"),
          FilterClose: this.translate("COM_EMUNDUS_ONBOARD_FILTER_CLOSE"),
          FilterPublish: this.translate("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
          FilterUnpublish: this.translate("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
          FilterYetToCome: this.translate("COM_EMUNDUS_CAMPAIGN_YET_TO_COME"),
          FilterOnGoing: this.translate("COM_EMUNDUS_CAMPAIGN_ONGOING"),
          Rechercher: this.translate("COM_EMUNDUS_ONBOARD_SEARCH"),
          Archived: this.translate("COM_EMUNDUS_ONBOARD_ARCHIVED"),
        },
        filtre: "all",
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
  };
</script>

<style lang="scss" scoped>
  .search {
    width: 245px;
    position: relative;
    display: flex;
  }

  .searchTerm {
    width: 100%;
    border: 1px solid #de6339;
    border-right: none;
    padding: 5px;
    padding-left: 10px;
    height: 43px;
    border-radius: 5px;
    border-top-right-radius: 0 !important;
    border-bottom-right-radius: 0 !important;
    outline: none;
    color: #de6339;
  }

  .searchTerm::placeholder {
    color: #de6339;
  }

  .searchButton {
    width: 40px;
    height: 43px;
    border: 1px solid #de6339;
    background: #de6339;
    text-align: center;
    color: white;
    border-radius: 0 5px 5px 0;
    cursor: pointer;
    font-size: 20px;
  }

  .searchButton svg {
    margin-top: 30%;
  }

  div nav a:hover {
    cursor: pointer;
  }

  .action-submenu {
    padding: 7px !important;
    background-color: transparent;
    -webkit-transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
    transition: background-color 200ms cubic-bezier(0.55, 0.085, 0.68, 0.53);
    font-size: 12px;
    color: black;
    font-family: Lato, 'Helvetica Neue', Arial, Helvetica, sans-serif !important;
    &:hover {
       color: #20835F;
     }
  }

  .filters-action {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .filters-menu-space-between {
    width: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 0;
  }

  .filters-menu{
    width: auto;
    display: flex;
    align-items: center;
    justify-content: end;
  }

  .filter-subtitle {
    font-size: 14px;
    font-family: Lato, "Helvetica Neue", Arial, Helvetica, sans-serif !important;
    border-bottom: solid 1px #dedede;
    padding-bottom: 8px;
    color: #868585;
    font-weight: bold;
  }

  @media (max-width: 991px) {
    .search {
      margin-top: 10px;
      margin-left: 10px;
    }
  }
</style>
