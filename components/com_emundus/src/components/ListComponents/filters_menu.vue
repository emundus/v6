<template>
  <div>
    <div class="container-2 w-container" style="max-width: unset;min-width: 200px">
      <transition :name="'slide-down'" type="transition">
        <div>
<!--          <div class="search">
            <input class="searchTerm"
                   :placeholder="Rechercher"
                   v-model="recherche"
                   @keyup="cherche(recherche) || debounce"
                   @keyup.enter="chercheGo(recherche)"/>
            <a @click="chercheGo(recherche)" class="searchButton"><em class="fa fa-search"></em></a>
          </div>-->

          <div>
<!--              <div>
                <div class="filter-subtitle">
                  <div>{{ Sort }}</div>
                </div>
                <nav aria-label="sort" class="actions-dropdown">
                  <a @click="sort('DESC');updateSort('DESC');"
                     class="action-submenu"
                     :class="tri == 'DESC' ? 'selected' : ''">
                    {{ SortCreasing }}
                  </a>
                  <a @click="sort('ASC');updateSort('ASC');"
                     :class="tri == 'ASC' ? 'selected' : ''"
                     class="action-submenu">
                    {{ SortDecreasing }}
                  </a>
                </nav>
              </div>-->

              <div>
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
                       @click="filter('notTerminated');updateFilter('notTerminated');"
                       :class="filtre == 'notTerminated' ? 'selected' : ''"
                       class="action-submenu">
                      {{ translations.FilterOpen }}
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
    <div class="loading-form" style="top: 10vh" v-if="loading">
      <Ring-Loader :color="'#12db42'" />
    </div>
  </div>
</template>

<script>
  import axios from "axios";
  import Swal from "sweetalert2";
  import "sweetalert2/src/sweetalert2.scss";
  ;
  import "@fortawesome/fontawesome-free/css/all.css";
  import "@fortawesome/fontawesome-free/js/all.js";

  const qs = require("qs");

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
       color: #16AFE1;
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
