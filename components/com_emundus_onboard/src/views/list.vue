<template>
  <div id="list">
    <list-head
      v-if="type !== 'files'"
      :data="actions"
    >
    </list-head>

    <div :class="{
      'filters-menu-campaign': type == 'campaign',
      'filters-menu': type !== 'campaign',
    }">
      <select 
        v-if="type == 'campaign'"
        v-model="selectedProgram"
        name="selectProgram"
        id="pet-select"
        class="selectProgram"
        @change="validateFilters"
      >
        <option value="all">{{translations.AllPrograms}} </option>
        <option 
          v-for="program in allPrograms" 
          :value="program.code" 
          :key="program.code"
        >
          {{program.label}}
        </option>
      </select>
      <div class="search-container">
        <div class="search">
          <input class="searchTerm"
            :placeholder="translations.Rechercher"
            v-model="recherche"
            @keyup="cherche(recherche) || debounce"
            @keyup.enter="chercheGo(recherche)"
          />
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
    </div>

    <ul class="form-section email-sections"
        v-if="type == 'email' && !loading && total != 0 && email_categories.length > 0">
      <li>{{translations.Categories}} :</li>
      <li>
        <a :class="menuEmail === 0 ? 'form-section__current' : ''" @click="menuEmail = 0">{{translations.All}}</a>
      </li>
      <li v-for="(cat, index) in notEmptyEmailCategories" :key="'cat_' + index">
        <a :class="menuEmail === cat ? 'form-section__current' : ''" @click="menuEmail = cat">{{cat}}</a>
      </li>
    </ul>
    <ul class="form-section email-sections" v-if="(type === 'formulaire'|| type === 'grilleEval')  && !loading ">
      <li>
        <a :class="typeForAdd === 'form'||type === 'formulaire' ? 'form-section__current' : ''"
           @click="typeForAdd = 'form' ; type='formulaire'">Candidature</a>
      </li>
      <li>
        <a :class="typeForAdd === 'grilleEval' ? 'form-section__current' : ''"
           @click="typeForAdd = 'grilleEval' ; type='grilleEval'">Grilles d'évaluation</a>
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
               {{ paginationNumber(index) }}
            </a>
          </li>
          <a @click="nbpages(pages + 1)" class="pagination-arrow arrow-right">
            <em class="fas fa-chevron-right"></em>
          </a>
        </ul>
      </div>
    </transition>

    <div v-show="total > 0 || type == 'files'">

      <list-body
        :type="type"
        :actions="actions"
        @validateFilters="validateFilters"
		    @updateLoading="updateLoading"
      ></list-body>

      <div :class="countPages == 1 ? 'noPagination' : 'pagination-pages'" v-show="!loading">
        <ul class="pagination" v-if="total > 0" style="position: absolute;bottom: 0;width: 100%;">
          <a @click="nbpages(pages - 1)" class="pagination-arrow arrow-left">
            <em class="fas fa-chevron-left"></em>
          </a>
          <li
            v-for="index in countPages"
            v-show="
              countPages <= 10 ||
              index < 4 ||
              index > countPages - 3 ||
              (index > pages - 3 && index < pages + 3) ||
              index == pages - 3 ||
              index == pages + 3"
            :key="index"
            class="pagination-number"
          >
            <a
              @click="nbpages(index)"
              class="pagination-number"
              :class="index == pages ? 'current-number' : ''"
            >
              {{ paginationNumber }}
            </a>
          </li>
          <a @click="nbpages(pages + 1)" class="pagination-arrow arrow-right">
            <em class="fas fa-chevron-right"></em>
          </a>
        </ul>
      </div>
    </div>

    <div v-show="total == 0 && type != 'files' && !loading" class="noneDiscover">
      {{ noneDiscoverTranslation }}
    </div>
    <div class="loading-form" v-if="loading">
      <RingLoader :color="'#12DB42'"/>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import filters from "../components/list_components/filters_menu";
import ListHead from "../components/List/ListHead.vue";
import ListBody from "../components/List/ListBody.vue";
import { list } from "../store/store";
import { global } from "../store/global";

export default {
  components: {
    filters,
    ListHead,
    ListBody,
  },

  name: "List",
  data: () => ({
    datas: {},
    type: "",
    selecedItems: [],
    actions: {
      type: "",
      add_url: ""
    },
    loading: false,
    actualLanguage: '',
    allPrograms: [],
    selectedProgram: 'all',
    actualProgramShowingCampaignName: 'Tous',
    recherche: "",
    timer: null,

    translations: {
      Select: Joomla.JText._("COM_EMUNDUS_ONBOARD_SELECT"),
      Deselect: Joomla.JText._("COM_EMUNDUS_ONBOARD_DESELECT"),
      Total: Joomla.JText._("COM_EMUNDUS_ONBOARD_TOTAL"),
      noCampaign: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOCAMPAIGN"),
      noProgram: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOPROGRAM"),
      noEmail: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOEMAIL"),
      noForm: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOFORM"),
      noFiles: Joomla.JText._("COM_EMUNDUS_ONBOARD_NOFILES"),
      All: Joomla.JText._("COM_EMUNDUS_ONBOARD_ALL"),
      AllPrograms:Joomla.JText._('COM_EMUNDUS_ONBOARD_ALL_PROGRAMS'),
      programs: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM"),
      ortherPrograms: Joomla.JText._("COM_EMUNDUS_ONBOARD_OTHERCAMP_PROGRAM"),
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
    isEmpty: () => {
      return list.getters.isSomething;
    },
    noneDiscoverTranslation() {
      if (this.type === "campaign") {
        return this.translations.noCampaign;
      } else if (this.type === "program") {
        return this.translations.noProgram;
      } else if (this.type === "email") {
        return this.translations.noEmail;
      } else if (this.type === "formulaire") {
        return this.translations.noForm;
      } else {
        return this.translations.noFiles;
      }
    }
  },
  created() {
    this.datas = global.getters.datas;
    this.type = this.datas.type.value;

    axios({
      method: "get",
      url: "index.php?option=com_emundus_onboard&controller=form&task=getActualLanguage",
    }).then(response => {
      this.actualLanguage = response.data.msg;
    });

    axios.get("index.php?option=com_emundus_onboard&controller=program&task=getallprogram")
        .then(response => {
          this.allPrograms = response.data.data;

          // sort all programs by label
          this.allPrograms.sort((a, b) => {
            if (a.label < b.label) {
              return -1;
            }
            if (a.label > b.label) {
              return 1;
            }
            return 0;
          });

        }).catch(e => {
      console.log(e);
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
    },

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
        this.filtersPage +
        "&program=" + this.selectedProgram;

      this.allFilters();
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
    paginationNumber(index) {
      if (this.countPages > 10) {
        return index < 4 || index > this.countPages - 3 || (index > pages - 3 && index < pages + 3) ? index : "...";
      } 
      
      return index;
    },
    allFilters() {
      let controller = this.typeForAdd === 'grilleEval' ? 'form' : this.typeForAdd
      if (this.type !== "files") {
        axios.get("index.php?option=com_emundus_onboard&controller=" +
          controller +
          "&task=get" +
          this.typeForAdd +
          "count" +
          this.filtersCount
        ).then(response => {
          axios.get(
            "index.php?option=com_emundus_onboard&controller=" +
            controller +
            "&task=getall" +
            this.typeForAdd +
            this.filters
          ).then(rep => {
            this.total = response.data.data;
            list.commit("listUpdate", rep.data.data);

            this.countPages = Math.ceil(this.total / this.limit);
            if (this.type == 'email') {

              axios.get("index.php?option=com_emundus_onboard&controller=email&task=getemailcategories")
              .then(catrep => {
                this.email_categories = catrep.data.data;
              });

            }
            this.loading = false;
          }).catch(e => {
            console.log(e);
            this.updateLoading(false);
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
      return this.list.filter(function (element) {
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
h2 {
  color: #de6339 !important;
}

.loading-form {
  top: unset;
}

.dropbtn {
  background-color: #f9f9f9;
  color: #0f0f0f;
  /*padding: 16px;
  font-size: 16px;*/
  border: none;
  cursor: pointer;
  min-width: 160px;
  /*box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);*/
}

.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-content {
  position: absolute;
  background-color: #f9f9f9;
  box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
  z-index: 98;
  max-height: 0;
  min-width: 160px;
  transition: max-height 0.15s ease-out;
  overflow: hidden;
}

.dropdown-content a {
  color: black;
  background-color: #f9f9f9;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
}

.dropdown-content a:hover {
  background-color: #e2e2e2;
}

.dropdown:hover .dropdown-content {
  max-height: 500px;
  min-width: 160px;
  transition: max-height 0.25s ease-in;
}

.dropdown:hover .dropbtn {
  background-color: #f9f9f9;
  border-bottom: 1px solid #e0e0e0;
  transition: max-height 0.25s ease-in;
}

.selectProgram {
  outline: none;
  right: 0;
  height: 43px;
  margin-bottom: 0 !important;
}
.search-container{
  display: flex;
  align-items: center;
}


</style>
