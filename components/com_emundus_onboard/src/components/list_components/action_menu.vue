<template>
  <div class="section-sub-menu">
    <div class="container-2 w-container">
      <div class="w-row">
        <div class="column-2 w-col w-col-1">
          <div
            data-hover="1"
            data-delay="0"
            class="dropdown w-dropdown"
            @mouseover="actionHover = true"
            @mouseleave="actionHover = false"
          >
            <div
              v-show="isEmpty"
              class="dropdown-toggle-2 w-dropdown-toggle"
              style="margin-left: 1%; margin-right: -0.5;"
            >
              <div class="icon w-icon-dropdown-toggle"></div>
              <div>{{ Action }}</div>
            </div>
            <nav aria-label="action" v-if="actionHover" class="dropdown-list w-dropdown-list">
              <a v-on:click="publishSelected(checkItem)" class="action-submenu w-dropdown-link">{{
                ActionPublish
              }}</a>
              <a v-on:click="unpublishSelected(checkItem)" class="action-submenu w-dropdown-link">{{
                ActionUnpublish
              }}</a>
              <a
                v-if="data.type == 'campaign'"
                v-on:click="duplicateSelected(checkItem)"
                class="action-submenu w-dropdown-link"
                >{{ ActionDuplicate }}</a
              >
              <a v-on:click="deleteSelected(checkItem)" class="action-submenu w-dropdown-link">{{
                ActionDelete
              }}</a>
            </nav>
          </div>
        </div>

        <div class="column-flex w-clearfix w-col w-col-10">
          <a :href="data.add_url" class="bouton-ajouter w-inline-block">
            <div v-if="data.type === 'program'">
              {{ AddProgram }}
              <div class="addCampProgEmail"></div>
            </div>
            <div v-if="data.type === 'campaign'">
              {{ AddCampaign }}
              <div class="addCampProgEmail"></div>
            </div>
            <div v-if="data.type === 'email'">
              {{ AddEmail }}
              <div class="addCampProgEmail"></div>
            </div>
            <div v-if="data.type === 'formulaire'">
              {{ AddForm }}
              <div class="addCampProgEmail"></div>
            </div>
          </a>
        </div>

        <div class="column-2 w-col w-col-1" style="margin-left: -48%;margin-top: 2%;">
          <div class="search">
            <input
              class="searchTerm"
              :placeholder="Rechercher"
              v-model="recherche"
              @keyup="cherche(recherche) | debounce"
              @keyup.enter="chercheGo(recherche)"
            />
            <a @click="chercheGo(recherche)" class="searchButton"><em class="fa fa-search"></em></a>
          </div>
        </div>

        <div class="column-2 w-col w-col-1" style="margin-left: -20.5%">
          <div
            data-hover="1"
            data-delay="0"
            class="dropdown w-dropdown"
            @mouseover="sortHover = true"
            @mouseleave="sortHover = false"
          >
            <div class="dropdown-toggle-2 w-dropdown-toggle">
              <div class="icon w-icon-dropdown-toggle"></div>
              <div>{{ Sort }}</div>
            </div>
            <nav aria-label="sort" v-if="sortHover" class="dropdown-list w-dropdown-list">
              <a
                @click="
                  sort('DESC');
                  updateSort('DESC');
                "
                class="action-submenu w-dropdown-link"
                :class="tri == 'DESC' ? 'selected' : ''"
                >{{ SortCreasing }}</a
              >
              <a
                @click="
                  sort('ASC');
                  updateSort('ASC');
                "
                :class="tri == 'ASC' ? 'selected' : ''"
                class="action-submenu w-dropdown-link"
                >{{ SortDecreasing }}</a
              >
            </nav>
          </div>
        </div>

        <div class="column-2 w-col w-col-1" style="margin-left: -11%">
          <div
            data-hover="1"
            data-delay="0"
            class="dropdown w-dropdown"
            @mouseover="filterHover = true"
            @mouseleave="filterHover = false"
          >
            <div class="dropdown-toggle-2 w-dropdown-toggle">
              <div class="icon w-icon-dropdown-toggle"></div>
              <div>{{ Filter }}</div>
            </div>
            <nav aria-label="filter" v-if="filterHover" class="dropdown-list w-dropdown-list">
              <a
                @click="
                  filter('all');
                  updateFilter('all');
                "
                :class="filtre == 'all' ? 'selected' : ''"
                class="action-submenu w-dropdown-link"
                >{{ FilterAll }}</a
              >
              <a
                v-if="data.type == 'campaign'"
                @click="
                  filter('notTerminated');
                  updateFilter('notTerminated');
                "
                :class="filtre == 'notTerminated' ? 'selected' : ''"
                class="action-submenu w-dropdown-link"
                >{{ FilterOpen }}</a
              >
              <a
                v-if="data.type == 'campaign'"
                @click="
                  filter('Terminated');
                  updateFilter('Terminated');
                "
                :class="filtre == 'Terminated' ? 'selected' : ''"
                class="action-submenu w-dropdown-link"
                >{{ FilterClose }}</a
              >
              <a
                @click="
                  filter('Publish');
                  updateFilter('Publish');
                "
                :class="filtre == 'Publish' ? 'selected' : ''"
                class="action-submenu w-dropdown-link"
                >{{ FilterPublish }}</a
              >
              <a
                @click="
                  filter('Unpublish');
                  updateFilter('Unpublish');
                "
                :class="filtre == 'Unpublish' ? 'selected' : ''"
                class="action-submenu w-dropdown-link"
                >{{ FilterUnpublish }}</a
              >
            </nav>
          </div>
        </div>

        <div class="column-2 w-col w-col-1" style="margin-left: -1%">
          <div
            data-hover="1"
            data-delay="0"
            class="dropdown w-dropdown"
            @mouseover="resultsHover = true"
            @mouseleave="resultsHover = false"
          >
            <div class="dropdown-toggle-2 w-dropdown-toggle">
              <div class="icon w-icon-dropdown-toggle"></div>
              <div>{{ NbResults }}</div>
            </div>
            <nav aria-label="Nb Results" v-if="resultsHover" class="dropdown-list w-dropdown-list">
              <a
                @click="
                  nbresults(999999);
                  updateDisplay('max');
                "
                :class="afficher == 'max' ? 'selected' : ''"
                class="action-submenu w-dropdown-link"
                >{{ AllResults }}</a
              >
              <a
                @click="
                  nbresults(10);
                  updateDisplay(10);
                "
                :class="afficher == 10 ? 'selected' : ''"
                class="action-submenu w-dropdown-link"
                >10</a
              >
              <a
                @click="
                  nbresults(25);
                  updateDisplay(25);
                "
                :class="afficher == 25 ? 'selected' : ''"
                class="action-submenu w-dropdown-link"
                >25</a
              >
              <a
                @click="
                  nbresults(50);
                  updateDisplay(50);
                "
                :class="afficher == 50 ? 'selected' : ''"
                class="action-submenu w-dropdown-link"
                >50</a
              >
              <a
                @click="
                  nbresults(100);
                  updateDisplay(100);
                "
                :class="afficher == 100 ? 'selected' : ''"
                class="action-submenu w-dropdown-link"
                >100</a
              >
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import "sweetalert2/src/sweetalert2.scss";
import { list } from "../../store";
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
    isEmpty: Boolean
  },

  computed: {
    checkItem() {
      return list.getters.selectedItems;
    }
  },

  data() {
    return {
      recherche: "",
      filterHover: false,
      actionHover: false,
      sortHover: false,
      resultsHover: false,
      NbResults: Joomla.JText._("COM_EMUNDUSONBOARD_RESULTS"),
      AllResults: Joomla.JText._("COM_EMUNDUSONBOARD_ALL_RESULTS"),
      Action: Joomla.JText._("COM_EMUNDUSONBOARD_ACTION"),
      ActionPublish: Joomla.JText._("COM_EMUNDUSONBOARD_ACTION_PUBLISH"),
      ActionUnpublish: Joomla.JText._("COM_EMUNDUSONBOARD_ACTION_UNPUBLISH"),
      ActionDuplicate: Joomla.JText._("COM_EMUNDUSONBOARD_ACTION_DUPLICATE"),
      ActionDelete: Joomla.JText._("COM_EMUNDUSONBOARD_ACTION_DELETE"),
      Sort: Joomla.JText._("COM_EMUNDUSONBOARD_SORT"),
      SortCreasing: Joomla.JText._("COM_EMUNDUSONBOARD_SORT_CREASING"),
      SortDecreasing: Joomla.JText._("COM_EMUNDUSONBOARD_SORT_DECREASING"),
      Filter: Joomla.JText._("COM_EMUNDUSONBOARD_FILTER"),
      FilterAll: Joomla.JText._("COM_EMUNDUSONBOARD_FILTER_ALL"),
      FilterOpen: Joomla.JText._("COM_EMUNDUSONBOARD_FILTER_OPEN"),
      FilterClose: Joomla.JText._("COM_EMUNDUSONBOARD_FILTER_CLOSE"),
      FilterPublish: Joomla.JText._("COM_EMUNDUSONBOARD_FILTER_PUBLISH"),
      FilterUnpublish: Joomla.JText._("COM_EMUNDUSONBOARD_FILTER_UNPUBLISH"),
      AddCampaign: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_CAMPAIGN"),
      AddProgram: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_PROGRAM"),
      AddEmail: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_EMAIL"),
      AddForm: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_FORM"),
      AddFiles: Joomla.JText._("COM_EMUNDUSONBOARD_ADD_FILES"),
      Rechercher: Joomla.JText._("COM_EMUNDUSONBOARD_SEARCH"),
      filtre: "all",
      tri: "DESC",
      afficher: 25
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

    deleteSelected(id) {
      switch (this.data.type) {
        case "program":
          Swal.fire({
            title:
              "Attention! En supprimant un program, vous allez aussi effacer les campaigns et les dossiers de cette campagne",
            text: "You won't be able to revert this!",
            type: "warning",
            showCancelButton: true
          }).then(result => {
            if (result.value) {
              axios({
                method: "post",
                url: "index.php?option=com_emundus_onboard&controller=program&task=deleteprogram",
                data: qs.stringify({ id })
              })
                .then(response => {
                  list.commit("deleteSelected", id);
                  Swal.fire({
                    title: "Programme(s) effacé(s) ",
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                })
                .then(() => {
                  axios
                    .get(
                      "index.php?option=com_emundus_onboard&controller=program&task=getprogramcount"
                    )
                    .then(response => {
                      this.total = response.data.data;
                      this.updateTotal(this.total);
                    });
                })
                .catch(error => {
                  console.log(error);
                });
            }
          });
          break;

        case "campaign":
          Swal.fire({
            title:
              "Attention! En supprimant une campagne, vous allez aussi effacer les dossiers de cette campagne",
            text: "You won't be able to revert this!",
            type: "warning",
            showCancelButton: true
          }).then(result => {
            if (result.value) {
              axios({
                method: "post",
                url: "index.php?option=com_emundus_onboard&controller=campaign&task=deletecampaign",
                data: qs.stringify({ id })
              })
                .then(response => {
                  list.dispatch("deleteSelected", id).then(() => {
                    list.commit("resetSelectedItemsList");
                  });
                  Swal.fire({
                    title: "Campagne(s) effacée(s) ",
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                })
                .then(() => {
                  axios
                    .get(
                      "index.php?option=com_emundus_onboard&controller=campaign&task=getcampaigncount"
                    )
                    .then(response => {
                      this.total = response.data.data;
                      this.updateTotal(this.total);
                    });
                })
                .catch(error => {
                  console.log(error);
                });
            }
          });

          break;

        case "email":
          Swal.fire({
            title: "Attention! Vous êtes sur le point de supprimer un modèle d'email",
            text: "You won't be able to revert this!",
            type: "warning",
            showCancelButton: true
          }).then(result => {
            if (result.value) {
              axios({
                method: "post",
                url: "index.php?option=com_emundus_onboard&controller=email&task=deleteemail",
                data: qs.stringify({ id })
              })
                .then(response => {
                  list.dispatch("deleteSelected", id).then(() => {
                    list.commit("resetSelectedItemsList");
                  });
                  Swal.fire({
                    title: "Email(s) effacée(s) ",
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                })
                .then(() => {
                  axios
                    .get("index.php?option=com_emundus_onboard&controller=email&task=getemailcount")
                    .then(response => {
                      this.total = response.data.data;
                      this.updateTotal(this.total);
                    });
                })
                .catch(error => {
                  console.log(error);
                });
            }
          });

          break;
      }
    },

    unpublishSelected(id) {
      switch (this.data.type) {
        case "program":
          Swal.fire({
            title:
              "Attention! En dépubliant un program, vous allez aussi dépublier les campaigns liées",
            type: "warning",
            showCancelButton: true
          }).then(result => {
            if (result.value) {
              axios({
                method: "post",
                url:
                  "index.php?option=com_emundus_onboard&controller=program&task=unpublishprogram",
                data: qs.stringify({ id })
              })
                .then(response => {
                  list.commit("unpublish", id);
                  Swal.fire({
                    title: "Programme(s) dépublié(s) ",
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                })
                .catch(error => {
                  console.log(error);
                });
            }
          });
          break;

        case "campaign":
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=campaign&task=unpublishcampaign",
            data: qs.stringify({ id })
          })
            .then(response => {
              list.commit("unpublish", id);
              Swal.fire({
                title: "Campagne(s) dépubliée(s) ",
                type: "success",
                showConfirmButton: false,
                timer: 2000
              });
            })
            .catch(error => {
              console.log(error);
            });
          break;

        case "email":
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=email&task=unpublishemail",
            data: qs.stringify({ id })
          })
            .then(response => {
              list.commit("unpublish", id);
              Swal.fire({
                title: "Email(s) dépubliée(s) ",
                type: "success",
                showConfirmButton: false,
                timer: 2000
              });
            })
            .catch(error => {
              console.log(error);
            });
          break;
      }
    },

    publishSelected(id) {
      switch (this.data.type) {
        case "program":
          Swal.fire({
            title:
              "Attention! En publiant un program, vous allez aussi publier les campaigns liées",
            type: "warning",
            showCancelButton: true
          }).then(result => {
            if (result.value) {
              axios({
                method: "post",
                url: "index.php?option=com_emundus_onboard&controller=program&task=publishprogram",
                data: qs.stringify({ id })
              })
                .then(response => {
                  list.commit("publish", id);
                  Swal.fire({
                    title: "Programme(s) publié(s) ",
                    type: "success",
                    showConfirmButton: false,
                    timer: 2000
                  });
                })
                .catch(error => {
                  console.log(error);
                });
            }
          });
          break;

        case "campaign":
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=campaign&task=publishcampaign",
            data: qs.stringify({ id })
          })
            .then(response => {
              list.commit("publish", id);
              Swal.fire({
                title: "Campagne(s) publiée(s)",
                type: "success",
                showConfirmButton: false,
                timer: 2000
              });
            })
            .catch(error => {
              console.log(error);
            });
          break;

        case "email":
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=email&task=publishemail",
            data: qs.stringify({ id })
          })
            .then(response => {
              list.commit("publish", id);
              Swal.fire({
                title: "Email(s) publiée(s)",
                type: "success",
                showConfirmButton: false,
                timer: 2000
              });
            })
            .catch(error => {
              console.log(error);
            });
          break;
      }
    },

    duplicateSelected(id) {
      switch (this.data.type) {
        case "campaign":
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=campaign&task=duplicatecampaign",
            data: qs.stringify({ id })
          })
            .then(response => {
              list.commit("listUpdate", response.data.data);
              Swal.fire({
                title: "Campagne(s) dupliquée(s)",
                type: "success",
                showConfirmButton: false,
                timer: 2000
              });
            })
            .then(() => {
              axios
                .get(
                  "index.php?option=com_emundus_onboard&controller=campaign&task=getcampaigncount"
                )
                .then(response => {
                  this.total = response.data.data;
                  this.updateTotal(this.total);
                });
            })
            .catch(error => {
              console.log(error);
            });
          break;
      }
    }
  }
};
</script>

<style scoped>
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
  border-radius: 5px 0 0 5px;
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

.validate a {
  display: inline-block;
  padding: 6px 12px;
  margin-bottom: 0;
  font-size: 14px;
  font-weight: 400;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  cursor: pointer;
  border: 1px solid transparent;
  border-radius: 4px;
  color: white;
  background: #1b1f3c;
}

.selected {
  color: #de6339;
}

div nav a:hover {
  cursor: pointer;
}
</style>
