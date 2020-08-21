<template>
  <div id="list" :style="type == 'email' ? 'margin-top: 13em' : ''">
    <actions
      v-if="type != 'files'"
      :data="actions"
      :selected="selecedItems"
      :updateTotal="updateTotal"
      :filter="filter"
      :sort="sort"
      :cherche="cherche"
      :chercheGo="chercheGo"
      :validateFilters="validateFilters"
      :nbresults="nbresults"
      :isEmpty="isEmpty"
      :coordinatorAccess="coordinatorAccess"
    ></actions>

    <ul class="form-section email-sections" v-if="type == 'email' && !loading">
      <li>
        <a :class="menuEmail === 0 ? 'form-section__current' : ''" @click="menuEmail = 0;indexEmail = 0">Modèles</a>
      </li>
      <li>
        <a :class="menuEmail === 1 ? 'form-section__current' : ''" @click="menuEmail = 1;indexEmail = 0">Systèmes</a>
      </li>
    </ul>

    <transition :name="'slide-down'" type="transition">
      <h2 v-show="total > 0">{{ Total }} : {{ total }}</h2>
    </transition>

    <transition :name="'slide-down'" type="transition">
    <div :class="countPages == 1 ? 'noPagination' : 'pagination-pages'">
      <ul class="pagination" v-if="total > 0">
        <a @click="nbpages(pages - 1)" class="pagination-arrow arrow-left">
          <em class="fas fa-chevron-left"></em>
        </a>
        <li v-show="countPages <= 10 ||
            index < 4 ||
            index > countPages - 3 ||
            (index > pages - 3 && index < pages + 3) ||
            index == pages - 3 ||
            index == pages + 3"
          v-for="index in countPages"
          :key="index"
          class="pagination-number">
          <a @click="nbpages(index)"
            class="pagination-number"
            :class="index == pages ? 'current-number' : ''">
            {{ countPages > 10 ? index < 4 || index > countPages - 3 || (index > pages - 3 && index < pages + 3)
            ? index
            : "..."
            : index }}
          </a>
        </li>
        <a @click="nbpages(pages + 1)" class="pagination-arrow arrow-right">
          <em class="fas fa-chevron-right"></em>
        </a>
      </ul>
    </div>
    </transition>

    <div v-show="total > 0 || type == 'files'">
      <transition :name="'slide-down'" type="transition">
        <div v-show="total > 0" class="buttonSelectDeselect">
          <button @click="!isEmpty ? selectAllItem() : deselectItem()"
            class="btn-selectAll"
            :class="[isEmpty ? 'active' : '']">
          </button>
          <div v-show="!isEmpty" id="buttonLabelSelect">
            {{ Select }} ({{ pages < countPages ? limit : total - limit * countPages + limit }})
          </div>
          <div v-show="isEmpty" id="buttonLabelDeselect">{{ Deselect }}</div>
        </div>
      </transition>

      <transition-group :name="'slide-down'" type="transition">
        <div v-if="type != 'files'" v-for="(data, index) in list" :key="index" class="col-md-6">
          <component v-bind:is="type" :data="data" :selectItem="selectItem" />
        </div>
      </transition-group>

      <div v-if="type == 'files'">
        <component v-bind:is="type" />
      </div>

      <div :class="countPages == 1 ? 'noPagination' : 'pagination-pages'">
        <ul class="pagination" v-if="total > 0">
          <a @click="nbpages(pages - 1)" class="pagination-arrow arrow-left">
            <em class="fas fa-chevron-left"></em>
          </a>
          <li
            v-show="
              countPages <= 10 ||
                index < 4 ||
                index > countPages - 3 ||
                (index > pages - 3 && index < pages + 3) ||
                index == pages - 3 ||
                index == pages + 3
            "
            v-for="index in countPages"
            :key="index"
            class="pagination-number"
          >
            <a
              @click="nbpages(index)"
              class="pagination-number"
              :class="index == pages ? 'current-number' : ''"
              >{{
                countPages > 10
                  ? index < 4 || index > countPages - 3 || (index > pages - 3 && index < pages + 3)
                    ? index
                    : "..."
                  : index
              }}</a
            >
          </li>
          <a @click="nbpages(pages + 1)" class="pagination-arrow arrow-right">
            <em class="fas fa-chevron-right"></em>
          </a>
        </ul>
      </div>
    </div>

    <div v-show="total == 0 && type != 'files' && !loading" class="noneDiscover">
      {{
        this.type == "campaign"
          ? noCampaign
          : this.type == "program"
          ? noProgram
          : this.type == "email"
          ? noEmail
          : this.type == "formulaire"
          ? noForm
          : noFiles
      }}
    </div>
    <div class="loading-form" v-if="loading">
      <RingLoader :color="'#de6339'" />
    </div>
  </div>
</template>

<script>
import axios from "axios";
import program from "../components/list_components/programItem";
import campaign from "../components/list_components/camapaignItem";
import email from "../components/list_components/emailItem";
import formulaire from "../components/list_components/formItem";
import files from "../components/list_components/files";
import actions from "../components/list_components/action_menu";
import { list } from "../store";

import "../assets/css/webflow.css";
import "../assets/css/normalize.css";
import "../assets/css/emundus-webflow.css";
import "../assets/css/codemirror.css";
import "../assets/css/codemirror.min.css";
import "../assets/css/views_emails.css";
import "../assets/css/date-time.css";

import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/js/all.js";

const qs = require("qs");

export default {
  components: {
    program,
    campaign,
    email,
    formulaire,
    files,
    actions
  },

  name: "list",
  props: {
    type: String,
    actualLanguage: String,
    coordinatorAccess: Number
  },
  data: () => ({
    selecedItems: [],
    actions: {
      type: "",
      add_url: ""
    },
    loading: false,

    Select: Joomla.JText._("COM_EMUNDUS_ONBOARD_SELECT"),
    Deselect: Joomla.JText._("COM_EMUNDUS_ONBOARD_DESELECT"),
    Total: Joomla.JText._("COM_EMUNDUS_ONBOARD_TOTAL"),
    noCampaign: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOCAMPAIGN"),
    noProgram: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOPROGRAM"),
    noEmail: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOEMAIL"),
    noForm: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOFORM"),
    noFiles: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOFILES"),
    total: 0,
    filtersCount: "",
    filters: "",
    filtersCountFilter: "&filterCount=",
    filtersCountSearch: "&rechercheCount=",
    filtersFilter: "&filter=",
    filtersSort: "&sort=",
    filtersSearch: "&recherche=",
    filtersLim: "&lim=",
    filtersPage: "&page=",
    filtre: "",
    tri: "",
    search: "",
    limit: 25,
    pages: 1,
    countPages: 1,

    menuEmail: 0,
    indexEmail: 0,
  }),

  computed: {
    list() {
      return list.getters.list;
    },

    isEmpty: () => {
      return list.getters.isSomething;
    }
  },

  created() {
    this.actions.type = this.type;
    this.typeForAdd = this.type;
    if (this.typeForAdd == "form") {
      this.type = "formulaire";
    }
    if (this.typeForAdd != "files") {
      this.actions.add_url =  'index.php?option=com_emundus_onboard&view=' + this.typeForAdd + '&layout=add'
    }
    this.validateFilters();
  },

  methods: {
    validateFilters() {
      this.loading = true;
      this.filtersCount = this.filtersCountFilter + this.filtersCountSearch;
      this.filters =
        this.filtersFilter +
        this.filtersSort +
        this.filtersSearch +
        this.filtersLim +
        this.filtersPage;

      this.allFilters(this.filtersCount, this.filters);
    },

    filter(filter) {
      this.filtersCountFilter = "&filterCount=" + filter;
      this.filtersFilter = "&filter=" + filter;
      this.filtre = filter;
      this.validateFilters();
    },

    sort(sort) {
      this.filtersSort = "&sort=" + sort;
      this.tri = sort;
      this.validateFilters();
    },

    cherche(recherche) {
      this.filtersCountSearch = "&rechercheCount=" + recherche;
      this.filtersSearch = "&recherche=" + recherche;
      this.search = recherche;
      this.validateFilters();
    },

    chercheGo(recherche) {
      this.cherche(recherche);
      this.validateFilters();
    },

    nbresults(lim) {
      this.filtersLim = "&lim=" + lim;
      this.limit = lim;
      this.validateFilters();
    },

    nbpages(page) {
      if (page >= 1 && page <= this.countPages) {
        this.filtersPage = "&page=" + page;
        this.pages = page;
        this.validateFilters();
      }
    },

    allFilters(filtersCount, filters) {
      if (this.type != "files") {
        axios.get("index.php?option=com_emundus_onboard&controller=" +
              this.typeForAdd +
              "&task=get" +
              this.typeForAdd +
              "count" +
              filtersCount
          ).then(response => {
            axios.get(
                "index.php?option=com_emundus_onboard&controller=" +
                  this.typeForAdd +
                  "&task=getall" +
                  this.typeForAdd +
                  filters
              ).then(rep => {
                this.total = response.data.data;
                list.commit("listUpdate", rep.data.data);
                if(typeof rep.data.forms_updating != 'undefined') {
                  list.commit("formsAccessUpdate", rep.data.forms_updating);
                }
                this.countPages = Math.ceil(this.total / this.limit);
                this.loading = false;
              }).catch(e => {
                console.log(e);
                this.loading = false;
              });
          }).catch(e => {
            console.log(e);
            this.loading = false;
          });
      }
    },

    updateTotal(total) {
      this.total = total;
    },
    selectAllItem() {
      return this.list.filter(function(element) {
        list.commit("selectItem", element.id);
      });
    },
    deselectItem() {
      list.commit("resetSelectedItemsList");
    },
    selectItem(id) {
      list.commit("selectItem", id);
    }
  }
};
</script>

<style scoped>
.noPagination {
  display: none;
}

.pagination-pages {
  text-align: center;
}

.pagination {
  list-style: none;
  padding: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
}
.pagination a,
.pagination li {
  border-radius: 50%;
  color: #212121;
  transition: 0.15s ease-in;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
}
.pagination a:hover {
  background: rgba(27, 31, 60, 0.8);
  color: white;
}
.pagination .pagination-number,
.pagination .pagination-arrow i {
  vertical-align: middle;
}
.pagination .pagination-number {
  font-family: sans-serif;
  font-size: 14px;
  text-align: center;
  line-height: 24px;
  width: 35px;
  height: 35px;
  margin-right: 10px;
}
.pagination .pagination-number.current-number {
  background: #1b1f3c;
  color: #fff;
  border: unset;
}

.pagination-arrow{
  width: 35px;
  height: 35px;
  align-items: center;
  display: flex !important;
  justify-content: center;
}

.arrow-left {
  margin-right: 10px !important;
}

h2 {
  color: #de6339 !important;
}

.noneDiscover {
  position: absolute;
  top: 35%;;
  font-size: 20px;
  color: #1b1f3c;
  width: 100%;
  margin: 0 auto;
  text-align: center;
}

  .loading-form{
    top: unset;
  }

.email-sections{
  width: 50%;
  margin: 0 auto;
}
</style>
