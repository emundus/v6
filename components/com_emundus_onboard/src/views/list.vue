<template>
  <div id="list">
    <list-head
      v-if="type !== 'files'"
      :data="actions"
    >
    </list-head>

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
              v-if="type !== 'files'"
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

    <list-body></list-body>

    <ul class="form-section email-sections" v-if="type == 'email' && !loading && total != 0 && email_categories.length > 0">
      <li>{{translations.Categories}} : </li>
      <li>
        <a :class="menuEmail === 0 ? 'form-section__current' : ''" @click="menuEmail = 0">{{translations.All}}</a>
      </li>
      <li v-for="(cat, index) in notEmptyEmailCategories" :key="'cat_' + index">
        <a :class="menuEmail === cat ? 'form-section__current' : ''" @click="menuEmail = cat">{{cat}}</a>
      </li>
    </ul>
    <ul class="form-section email-sections" v-if="(type === 'formulaire'|| type === 'grilleEval')  && !loading ">
      <li>
        <a :class="typeForAdd === 'form'||type === 'formulaire' ? 'form-section__current' : ''" @click="typeForAdd = 'form' ; type = 'formulaire'">Candidature</a>
      </li>
      <li>
        <a :class="typeForAdd === 'grilleEval' ? 'form-section__current' : ''" @click="typeForAdd = 'grilleEval' ; type = 'grilleEval'">Grilles d'évaluation</a>
      </li>
    </ul>

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
      <transition-group :name="'slide-down'" type="transition" style="display: inline-block;margin: 16px 0;width: 100%">
        <div v-for="(data, index) in listTypeNotEmailNotFiles" :key="index" class="col-sm-12 col-lg-4 mb-2">
          <component v-bind:is="type" :data="data" :actions="actions" :selectItem="selectItem" @validateFilters="validateFilters()" @updateLoading="updateLoading" :actualLanguage="actualLanguage"/>
        </div>

        <div v-for="(data, index) in listEmailMenu0" :key="index" class="col-sm-12 col-lg-4 mb-2">
          <component v-bind:is="type" :data="data" :actions="actions" :selectItem="selectItem" @validateFilters="validateFilters()" @updateLoading="updateLoading" :models="list" />
        </div>

        <div v-for="(data, index) in listEmailMenuEmailCategory" :key="index" class="col-sm-12 col-lg-4 mb-2">
          <component v-bind:is="type" :data="data" :actions="actions" :selectItem="selectItem" @validateFilters="validateFilters()" @updateLoading="updateLoading" />
        </div>

        <div v-if="type === 'email' && menuEmail == 1 && data.type == 1">
          <div v-for="(data, index) in listEmailMenu1Type1" :key="index" class="col-sm-12 col-lg-4 mb-2">
            <component v-bind:is="type" :data="data" :actions="actions" :selectItem="selectItem" @validateFilters="validateFilters()" @updateLoading="updateLoading" />
          </div>
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
        this.type === "campaign"
          ? translations.noCampaign
          : this.type === "program"
          ? translations.noProgram
          : this.type === "email"
          ? translations.noEmail
          : this.type === "formulaire"
          ? translations.noForm
          : translations.noFiles
      }}
    </div>
    <div class="loading-form" v-if="loading">
      <RingLoader :color="'#12DB42'" />
    </div>
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
import ListHead from "../components/List/ListHead.vue";
import ListBody from "../components/List/ListBody.vue";
import { list } from "../store/store";
import { global } from "../store/global";

export default {
  components: {
    program,
    campaign,
    email,
    formulaire,
    files,
    filters,
    ListHead,
    ListBody,
    grilleEval
  },

  name: "list",
  data: () => ({
    datas: {},
    type: "",
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
  }),

  computed: {
    list() {
      return list.getters.list;
    },

    listTypeNotEmailNotFiles() {
      if (this.type != "email" && this.type != "files") {
        return list.getters.list;
      }
      
      return [];
    },

    listEmailMenu0() {
      if (this.type == "email" && this.menuEmail == 0) {
        return list.getters.list
      }

      return [];
    },

    listEmailMenuEmailCategory() {
      if (this.type == "email" && this.menuEmail != 1 && this.menuEmail != 0) {
        return list.getters.list.filter(item => this.menuEmail == item.category);
      }

      return [];
    },

    listEmailMenu1Type1() {
      if (this.menuEmail == 1 && this.type == "email") {
        return list.getters.list.filter(item => item.type == 1);
      }

      return [];
    },

    isEmpty: () => {
      return list.getters.isSomething;
    },

    notEmptyEmailCategories() {
      return this.email_categories.filter(item => item !== '');
    },
  },

  created() {
    this.datas = global.getters.datas;
    this.type = this.datas.type.value;

    axios({
      method: "get",
      url: "index.php?option=com_emundus_onboard&controller=form&task=getActualLanguage",
    }).then(response => {
      this.actualLanguage=response.data.msg;
    });
    this.actions.type = this.type;
    this.typeForAdd = this.type;
    if (this.typeForAdd === "form") {
      this.type = "formulaire";
    }
    this.actions.add_url =  'index.php?option=com_emundus_onboard&view=' + this.typeForAdd + '&layout=add'
    this.validateFilters();
  },

  watch: {
    type:function (val){
      this.actions.type = val;
      this.typeForAdd = val === 'formulaire' ? 'form' : val;

      if (this.typeForAdd === "form") {
        this.type = "formulaire";
      }

      if (this.typeForAdd !== "files") {
        let view= this.typeForAdd === 'grilleEval' ? 'form' : this.typeForAdd
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
      this.updateLoading(true);
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
      let controller = this.typeForAdd === 'grilleEval' ? 'form' : this.typeForAdd
      if (this.type !== "files") {
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
                if(this.type === 'email'){
                  axios.get("index.php?option=com_emundus_onboard&controller=email&task=getemailcategories")
                    .then(catrep => {
                      this.email_categories = catrep.data.data;
                  });
                }
                this.updateLoading(false);
              }).catch(e => {
                console.log(e);
                this.updateLoading(false);
              });
          }).catch(e => {
            console.log(e);
            this.updateLoading(false);
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
}

</script>

<style scoped>
  .loading-form{
    top: unset;
  }
</style>
