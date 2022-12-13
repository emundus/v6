<template>
  <div id="list">
    <list-head v-if="type !== 'files'" :data="actions"></list-head>

    <div class="search em-mb-16 em-flex-row">
      <span class="material-icons-outlined em-mr-8">search</span>
      <input class="searchTerm"
             :placeholder="translate('COM_EMUNDUS_ONBOARD_SEARCH')"
             v-model="recherche"
             @keyup="cherche(recherche)"
      />
    </div>

    <div class="em-flex-row em-w-auto">
      <div class="em-list-filters" v-if="type === 'campaign'">
        <select v-model="selectedProgram" name="selectProgram" class="list-vue-select" @change="validateFilters">
          <option value="all">{{ translate('COM_EMUNDUS_ONBOARD_ALL_PROGRAMS') }} </option>
          <option v-for="program in allPrograms" :value="program.code" :key="program.code">{{ program.label }}</option>
        </select>
      </div>
      <div class="em-list-filters em-ml-8" v-else-if="type === 'campaign'">
        <select v-model="selectedSession" name="selectedSession" class="list-vue-select" @change="validateFilters">
          <option value="all">{{ translate('COM_EMUNDUS_ONBOARD_ALL_SESSIONS') }} </option>
          <option v-for="session in allSessions" :value="session" :key="session">{{ session }}</option>
        </select>
      </div>
      <div class="em-list-filters" v-else-if="type === 'email'">
        <select class="list-vue-select" v-model="menuEmail">
          <option value="0">{{ translate('COM_EMUNDUS_ONBOARD_ALL') }}</option>
          <option v-for="(cat, index) in notEmptyEmailCategories" :value="cat" :key="'cat_' + index">{{ cat }}</option>
        </select>
      </div>

      <filters
          :data="actions"
          :selected="selecedItems"
          :updateTotal="updateTotal"
          :filter="filter"
          :sort="sort"
          :validateFilters="validateFilters"
          :nbresults="nbresults"
      ></filters>
    </div>

    <transition :name="'slide-down'" type="transition">
      <div :class="countPages == 1 ? 'noPagination' : 'pagination-pages'" v-show="!loading">
        <ul class="pagination" v-if="total > 0">
          <a @click="nbpages(pages - 1)" class="pagination-arrow arrow-left">
            <em class="fas fa-chevron-left"></em>
          </a>
          <li v-show="countPages <= 10 || index < 4 || index > countPages - 3 || (index > pages - 3 && index < pages + 3) || index == pages - 3 || index == pages + 3"
              v-for="index in countPages"
              :key="index"
              class="pagination-number">
            <a @click="nbpages(index)" class="pagination-number" :class="index == pages ? 'current-number' : ''">
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
	    <div v-if="type !== 'form' && type !== 'formulaire'">
		    <list-body
				    :type="type"
				    :actions="actions"
				    :params="params"
				    :items="list"
				    @validateFilters="validateFilters"
				    @updateLoading="updateLoading"
		    ></list-body>
	    </div>
	    <div v-else>
		    <nav id="form-nav">
			    <ul class="em-flex-row-start">
				    <li
						    class="em-pointer em-p-8 em-font-weight-600 em-p-16"
						    :class="{
									'em-main-500-color em-border-bottom-main-500 ': selectedListTab === 'formulaire',
						      'em-border-bottom-neutral-300': selectedListTab !== 'formulaire'
								}"
						    @click="selectedListTab = 'formulaire'"
				    >
					    {{ translate('COM_EMUNDUS_FORM_MY_FORMS') }}
				    </li>
				    <li
						    class="em-pointer em-p-8 em-font-weight-600 em-p-16"
						    :class="{
									'em-main-500-color em-border-bottom-main-500': selectedListTab === 'formModels',
									'em-border-bottom-neutral-300': selectedListTab !== 'formModels'
								}"
						    @click="selectedListTab = 'formModels'"
				    >
					    {{ translate('COM_EMUNDUS_FORM_PAGE_MODELS') }}
				    </li>
			    </ul>
		    </nav>
		    <list-body
				    v-if="selectedListTab === 'formModels'"
				    type="formModels"
				    :actions="{}"
				    :params="{}"
				    :items="formModelsItems"
				    @validateFilters="validateFilters"
				    @updateLoading="updateLoading"
		    ></list-body>
		    <list-body
				    v-else
				    :type="type"
				    :actions="actions"
				    :params="params"
				    :items="list"
				    @validateFilters="validateFilters"
				    @updateLoading="updateLoading"
		    ></list-body>
	    </div>
    </div>

    <div v-show="total == 0 && type != 'files' && !loading" class="noneDiscover" v-html="noneDiscoverTranslation"></div>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import axios from "axios";
import filters from "../components/ListComponents/filters_menu";
import ListHead from "../components/List/ListHead.vue";
import ListBody from "../components/List/ListBody.vue";
import formBuilderService from '../services/formbuilder';

export default {
  components: {
    filters,
    ListHead,
    ListBody,
  },
  name: "List",
  data: () => ({
    loading: false,
    actualLanguage: '',

    datas: {},
    all_datas: [],
    limited_datas: [],

    params: {},
    type: "",
    filter_columns: [],

    selecedItems: [],
    actions: {
      type: "",
      add_url: ""
    },
    allPrograms: [],
    allSessions: [],
    selectedProgram: 'all',
    selectedSession: 'all',
    actualProgramShowingCampaignName: 'Tous',

    recherche: "",
    timer: null,

    translations: {
      noCampaign: "COM_EMUNDUS_ONBOARD_NOCAMPAIGN",
      noProgram: "COM_EMUNDUS_ONBOARD_NOPROGRAM",
      noEmail: "COM_EMUNDUS_ONBOARD_NOEMAIL",
      noForm: "COM_EMUNDUS_ONBOARD_NOFORM",
      noFiles: "COM_EMUNDUS_ONBOARD_NOFILES",
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
	  formModelsItems: [],
	  selectedListTab: 'formulaire'
  }),
  created() {
    this.datas = this.$store.getters['global/datas'];
    this.type = this.datas.type.value;
    this.filtersFilter = "&filter=Publish"

    axios({
      method: "get",
      url: "index.php?option=com_emundus&controller=form&task=getActualLanguage",
    }).then(response => {
      this.actualLanguage = response.data.msg;
    });

    if (this.type === 'campaign') {
      // Get programs to filters
      axios.get("index.php?option=com_emundus&controller=programme&task=getallprogram")
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

      axios.get("index.php?option=com_emundus&controller=programme&task=getallsessions")
          .then(response => {
            this.allSessions = response.data.data;

            // sort all programs by label
            this.allSessions.sort((a, b) => {
              if (a.year < b.year) {
                return -1;
              }
              if (a.year > b.year) {
                return 1;
              }
              return 0;
            });

          }).catch(e => {
        console.log(e);
      });
    } else if (this.type == 'form') {
	    this.getFormModels();
    }
    this.getFiltersByType();
  },
  methods: {
    updateLoading(value) {
      this.loading = value;
    },

    getFiltersByType() {
      switch (this.type){
        case 'campaign':
          this.filter_columns = ['label', 'short_description'];
          break;
        case 'email':
          this.filter_columns = ['subject'];
          break;
        case 'form':
          this.filter_columns = ['label'];
          break;
        default:
      }
    },

    validateFilters() {
      this.updateLoading(true);
      this.filtersCount = this.filtersCountFilter + this.filtersCountSearch;
      this.filters = this.filtersFilter + this.filtersSort + this.filtersSearch + this.filtersLim + this.filtersPage;
      if (this.type === 'campaign') {
        this.filtersCount += "&program=" + this.selectedProgram + "&session=" + this.selectedSession;
        this.filters += "&program=" + this.selectedProgram + "&session=" + this.selectedSession;
      }

			if (this.type == 'formulaire') {
				this.getFormModels();
			}

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
        if (this.recherche) {
          let new_datas = this.all_datas.filter((item, index) => {
            let search_query = recherche.toLowerCase().split(" ");
            return this.filter_columns.some((filter) => {
              return search_query.every(v => item[filter].toLowerCase().includes(v))
            })
          });

          this.$store.commit("lists/listUpdate", new_datas);
          this.countPages = Math.ceil(new_datas.length / this.limit);
        } else {
          this.$store.commit("lists/listUpdate", this.limited_datas);
          this.countPages = Math.ceil(this.total / this.limit);
        }
      }, 300);
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
        return index < 4 || index > this.countPages - 3 || (index > this.pages - 3 && index < this.pages + 3) ? index : "...";
      }

      return index;
    },
    allFilters() {
      let controller = this.typeForAdd === 'grilleEval' ? 'form' : this.typeForAdd;

      if (this.type !== "files") {
          axios.get("index.php?option=com_emundus&controller=" + controller + "&task=getall" + this.typeForAdd + this.filters).then(rep => {
            if(typeof rep.data.data.datas !== 'undefined') {
              this.total = rep.data.data.count;
              if (this.all_datas.length === 0) {
                this.all_datas = rep.data.data.datas;
              }
              this.limited_datas = rep.data.data.datas;
            }
            this.$store.commit("lists/listUpdate", this.limited_datas);

            this.countPages = Math.ceil(this.total / this.limit);

            if (this.type == 'email') {
              axios.get("index.php?option=com_emundus&controller=email&task=getemailcategories")
		              .then(catrep => {
                    this.email_categories = catrep.data.data;
                  });
            }
            if (this.type == 'campaign') {
              this.$store.commit('campaign/setAllowPinnedCampaign',rep.data.allow_pinned_campaigns);
              this.limited_datas.forEach((campaign) => {
                if(campaign.pinned == 1){
                  this.$store.commit('campaign/setPinned',campaign.id);
                }
              })
            }
            this.loading = false;
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
      return this.list.filter(function (element) {
        this.$store.commit("lists/selectItem", element.id);
      });
    },
    deselectItem() {
      this.$store.commit("lists/resetSelectedItemsList");
    },
    selectItem(id) {
      this.$store.commit("lists/selectItem", id);
    },
	  getFormModels() {
			formBuilderService.getModels().then((response) => {
				if (response.status && Array.isArray(response.data)) {
					this.formModelsItems = [];
					response.data.forEach((model) => {
						this.formModelsItems.push({
							id: model.id,
							label: model.label[this.shortDefaultLang]
						});
					});
				}
			});

			return this.formModelsItems;
	  }
  },
	computed: {
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
		},
		notEmptyEmailCategories() {
			return this.email_categories.filter(category => category !== "");
		},
		list() {
			let listItems = this.$store.getters['lists/list'];

			if (this.params.email_category) {
				listItems = listItems.filter((item) => {
					if (this.params.email_category == 0) {
						return true;
					} else {
						return item.category === this.params.email_category;
					}
				});
			}

			return listItems;
		}
	},
	watch: {
		type: function (val) {
			this.actions.type = val;
			this.typeForAdd = val === 'formulaire' ? 'form' : val;

			if (this.typeForAdd === 'form') {
				this.type = 'formulaire';
			}

			let view= this.typeForAdd === 'grilleEval' ? 'form' : this.typeForAdd
			if (this.type == 'email' || this.type == 'campaign') {
				this.actions.add_url = 'index.php?option=com_emundus&view=' + this.type + 's&layout=add'
			} else {
				this.actions.add_url = 'index.php?option=com_emundus&view=' + view + '&layout=add'
			}

			this.validateFilters();
		},
		menuEmail: function (val) {
			this.type = "email";
			this.params = {
				email_category: val
			};
		},
	}
}

</script>

<style scoped lang="scss">
#list{
  width: calc(100% - 75px) !important;
  margin-left: auto;
}
h2 {
  color: #de6339 !important;
}

.loading-form {
  top: unset;
}

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


.search-container{
  display: flex;
  align-items: center;
}

.searchTerm {
  font-size: 14px !important;
  padding: 8px 36px 8px 8px !important;
  outline: none;
  color: #9DBFAF;
  right: 0;
  height: 30px;
  position: relative;
  margin-bottom: 0 !important;
  border-top: unset;
  border-right: unset;
  border-left: unset;
  border-bottom: solid 1px black;
  background: transparent;
  border-radius: 0;
  font-family: var(--font);

  &:hover {
    border-top: unset;
    border-right: unset;
    border-left: unset;
    border-bottom: solid 1px #87D4B8;
    box-shadow: unset !important;
  }

  &:focus {
    border-top: unset;
    border-right: unset;
    border-left: unset;
    border-bottom: solid 1px #20835F;
    box-shadow: unset !important;
  }
}

#g-container-main .g-container{
  width: 90% !important;
}

.pagination {
  list-style: none;
  padding: 8px;
  display: flex !important;
  justify-content: center;
  align-items: center;
  a,li{
    border-radius: 50%;
    color: #212121;
    transition: 0.15s ease-in;
    cursor: pointer;
    text-decoration: none;
  }
  .pagination-number{
    font-family: sans-serif;
    padding: unset;
    font-size: 14px;
    text-align: center;
    display: flex !important;
    flex-direction: column;
    justify-content: center;
    line-height: 24px;
    width: 35px;
    height: 35px;
    margin-right: 10px;
    .current-number{
      background: #34B385;
      color: #fff;
      border: unset;
    }
  }
}
.pagination-pages{
  text-align: center;
}
.pagination a:hover {
  background: rgba(27, 31, 60, 0.8);
  color: white;
  text-decoration: unset;
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
.noneDiscover {
  font-size: 20px;
  color: #1b1f3c;
  width: 100%;
  margin: 3% 0;
  text-align: center;
}
.email-sections{
  width: 98% !important;
  margin: 3% auto !important;
}
.noPagination{
  display: none;
}
.material-icons, .material-icons-outlined{
  font-size: 24px !important;
}

#form-nav {
	ul {
		list-style-type: none;
		margin-left: 0;
		margin-bottom: 0;

		li {
			transition: all .3s;
		}
	}
}

</style>
