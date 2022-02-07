<template>
  <div id="list">
    <list-header
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
    ></list-header>

    <div class="filters-menu">
      <div class="search">
        <input class="searchTerm"
               :placeholder="translations.Rechercher"
               v-model="recherche"
               @keyup="cherche(recherche) || debounce"
               @keyup.enter="chercheGo(recherche)"/>
      </div>
      <v-popover :popoverArrowClass="'custom-popover-arrow'">
        <button class="tooltip-target b3 card-button"></button>

        <template slot="popover">
          <filters
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
          ></filters>
        </template>
      </v-popover>
    </div>

    <ul class="form-section email-sections" v-if="type == 'email' && !loading && total != 0 && email_categories.length > 0">
      <li>{{translations.Categories}} : </li>
      <li>
        <a :class="menuEmail === 0 ? 'form-section__current' : ''" @click="menuEmail = 0">{{translations.All}}</a>
      </li>
      <li v-for="(cat, index) in email_categories" v-if="cat != ''">
        <a :class="menuEmail === cat ? 'form-section__current' : ''" @click="menuEmail = cat">{{cat}}</a>
      </li>
      <!--<li>
        <a :class="menuEmail === 1 ? 'form-section__current' : ''" @click="menuEmail = 1">{{System}}</a>
      </li>-->
    </ul>
    <ul class="form-section email-sections" v-if="(type == 'formulaire'|| type == 'grilleEval')  && !loading ">
      <!--<li>Types : </li>-->

      <li>
        <a :class="typeForAdd === 'form'||type === 'formulaire' ? 'form-section__current' : ''" @click="typeForAdd = 'form' ; type='formulaire'">{{translations.candidature}}</a>
      </li>
      <li>
        <a :class="typeForAdd === 'grilleEval' ? 'form-section__current' : ''" @click="typeForAdd = 'grilleEval' ; type='grilleEval'">{{translations.evaluations}}</a>
      </li>
      <!--<li v-for="(cat, index) in email_categories" v-if="cat != ''">
        <a :class="menuEmail === cat ? 'form-section__current' : ''" @click="menuEmail = cat">{{cat}}</a>
      </li>-->

      <!--<li>
        <a :class="menuEmail === 1 ? 'form-section__current' : ''" @click="menuEmail = 1">{{System}}</a>
      </li>-->
    </ul>

<!--    <transition :name="'slide-down'" type="transition">
      <h2 v-show="total > 0">{{ Total }} : {{ total }}</h2>
    </transition>-->

    <transition :name="'slide-down'" type="transition">
    <div :class="countPages == 1 ? 'noPagination' : 'pagination-pages'" v-show="!loading">
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
<!--      <transition :name="'slide-down'" type="transition">
        <div v-show="total > 0" class="buttonSelectDeselect">
          <button @click="!isEmpty ? selectAllItem() : deselectItem()"
            class="btn-selectAll"
            :title="Select"
            :class="[isEmpty ? 'active' : '']">
          </button>
          <div v-show="!isEmpty" id="buttonLabelSelect">
            {{ Select }} ({{ pages < countPages ? limit : total - limit * countPages + limit }})
          </div>
          <div v-show="isEmpty" id="buttonLabelDeselect">{{ Deselect }}</div>
        </div>
      </transition>-->

      <transition-group :name="'slide-down'" type="transition" style="display: inline-block;margin: 16px 0;width: 100%">
        <div v-if="type != 'files' && type != 'email'" v-for="(data, index) in list" :key="index" class="col-sm-12 col-lg-4 mb-2">
          <component v-bind:is="type" :data="data" :actions="actions" :selectItem="selectItem" @validateFilters="validateFilters()" @updateLoading="updateLoading" :actualLanguage="actualLanguage"/>
        </div>

        <div v-if="type == 'email' && menuEmail == 0" v-for="(data, index) in list" :key="index" class="col-sm-12 col-lg-4 mb-2">
          <component v-bind:is="type" :data="data" :actions="actions" :selectItem="selectItem" @validateFilters="validateFilters()" @updateLoading="updateLoading" :models="list" />
        </div>

        <div v-if="type == 'email' && menuEmail != 1 && menuEmail != 0 && menuEmail == data.category" v-for="(data, index) in list" :key="index" class="col-sm-12 col-lg-4 mb-2">
          <component v-bind:is="type" :data="data" :actions="actions" :selectItem="selectItem" @validateFilters="validateFilters()" @updateLoading="updateLoading" />
        </div>

        <div v-if="type == 'email' && menuEmail == 1 && data.type == 1" v-for="(data, index) in list" :key="index" class="col-sm-12 col-lg-4 mb-2">
          <component v-bind:is="type" :data="data" :actions="actions" :selectItem="selectItem" @validateFilters="validateFilters()" @updateLoading="updateLoading" />
        </div>
      </transition-group>

      <div :class="countPages == 1 ? 'noPagination' : 'pagination-pages'" v-show="!loading">
        <ul class="pagination" v-if="total > 0" style="position: absolute;bottom: 0;width: 100%;">
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
      <RingLoader :color="'#12DB42'" />
    </div>
<!--    <tasks></tasks>-->
  </div>
</template>

<script>
import axios from "axios";
import program from "../components/list_components/programItem";
import campaign from "../components/list_components/camapaignItem";
import email from "../components/list_components/emailItem";
import grilleEval from  "../components/list_components/evalgridItem"
import formulaire from "../components/list_components/formItem";
import files from "../components/list_components/files";
import filters from "../components/list_components/filters_menu";
import listHeader from "../components/list_components/list_header";
import tasks from "./tasks"
import { list } from "../store";
import Swal from "sweetalert2";

import "../assets/css/normalize.css";
import "../assets/css/emundus-webflow.scss";
import "../assets/css/bootstrap.css";
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
    filters,
    listHeader,
    tasks,
    grilleEval
  },

  name: "list",
  props: {
    type: String
  },
  data: () => ({
    selecedItems: [],
    actions: {
      type: "",
      add_url: ""
    },
    loading: false,
    actualLanguage:'',
    recherche: "",
    timer: null,

    translations:{
      Select: Joomla.JText._("COM_EMUNDUS_ONBOARD_SELECT"),
      Deselect: Joomla.JText._("COM_EMUNDUS_ONBOARD_DESELECT"),
      Total: Joomla.JText._("COM_EMUNDUS_ONBOARD_TOTAL"),
      noCampaign: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOCAMPAIGN"),
      noProgram: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOPROGRAM"),
      noEmail: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOEMAIL"),
      noForm: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOFORM"),
      noFiles: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOFILES"),
      All: Joomla.JText._("COM_EMUNDUS_ONBOARD_ALL"),
      System: Joomla.JText._("COM_EMUNDUS_ONBOARD_SYSTEM"),
      Categories: Joomla.JText._("COM_EMUNDUS_ONBOARD_CATEGORIES"),
      Rechercher: Joomla.JText._("COM_EMUNDUS_ONBOARD_SEARCH"),
      candidature: Joomla.JText._("COM_EMUNDUS_ONBOARD_CAMPAIGN"),
      evaluations: Joomla.JText._("COM_EMUNDUS_ONBOARD_EVALUATION"),
    },
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
    email_categories: [],
    tasks: '',
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
    axios({
      method: "get",
      url: "index.php?option=com_emundus_onboard&controller=form&task=getActualLanguage",


    }).then(response => {

      this.actualLanguage=response.data.msg;
    });
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
  watch:{
    type:function (val){

      this.actions.type = val;
      this.typeForAdd = val=='formulaire'? 'form': val;

      if (this.typeForAdd == "form") {
        this.type = "formulaire";
      }
      if (this.typeForAdd != "files") {
        let view= this.typeForAdd =='grilleEval' ?'form':this.typeForAdd
        this.actions.add_url =  'index.php?option=com_emundus_onboard&view=' + view  + '&layout=add'
      }
      this.validateFilters();
    }

  },

  methods: {
    updateLoading(value) {
      this.loading = value;
    },

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
      if (this.timer) {
        clearTimeout(this.timer);
        this.timer = null;
      }
      this.timer = setTimeout(() => {
        this.filtersCountSearch = "&rechercheCount=" + recherche;
        this.filtersSearch = "&recherche=" + recherche;
        this.search = recherche;
        this.validateFilters();
      }, 800);
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
      let controller=this.typeForAdd=='grilleEval'?'form':this.typeForAdd
      if (this.type != "files") {
        axios.get("index.php?option=com_emundus_onboard&controller=" +
              controller +
              "&task=get" +
              this.typeForAdd +
              "count" +
              filtersCount
          ).then(response => {


            axios.get(
                "index.php?option=com_emundus_onboard&controller=" +
                  controller +
                  "&task=getall" +
                  this.typeForAdd +
                  filters
              ).then(rep => {
                this.total = response.data.data;
                list.commit("listUpdate", rep.data.data);
                this.countPages = Math.ceil(this.total / this.limit);
                if(this.type == 'email'){
                  axios.get("index.php?option=com_emundus_onboard&controller=email&task=getemailcategories")
                    .then(catrep => {
                      this.email_categories = catrep.data.data;
                  });
                }
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
  h2 {
    color: #de6339 !important;
  }

  .loading-form{
    top: unset;
  }
</style>
