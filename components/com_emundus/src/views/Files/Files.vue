<template>
  <div class="em-ml-32 em-files">
    <Application v-if="currentFile" :file="currentFile" :type="$props.type" :user="$props.user" :ratio="$props.ratio" @getFiles="getFiles(true)" />

    <div class="em-mb-12 em-flex-row em-flex-space-between">
      <h4>{{ translate('COM_EMUNDUS_FILES_'+type.toUpperCase()) }}</h4>
    </div>

    <div v-if="files">
      <div class="em-flex-row em-flex-space-between em-mb-16">
        <div class="em-flex-row">
          <div class="em-flex-row">
            <span>{{ translate('COM_EMUNDUS_FILES_TOTAL') }}</span>
            <span class="em-ml-4">{{ total_count }}</span>
          </div>
          <span class="em-ml-8 em-mr-8">|</span>
          <div class="em-flex-row">
            <span>{{ translate('COM_EMUNDUS_FILES_DISPLAY_PAGE') }}</span>
            <select class="em-select-no-border em-ml-8" style="width: max-content; height: fit-content;" v-model="limit">
              <option>10</option>
              <option>25</option>
              <option>50</option>
              <option>100</option>
            </select>
          </div>
        </div>
        <template v-if="pages !== null">
          <div class="em-flex-row" v-if="pages.length > 1">
            <span>{{ translate('COM_EMUNDUS_FILES_PAGE') }}</span>
            <select class="em-select-no-border em-ml-8" style="width: 40px;height: 20px;" v-model="page">
              <option v-for="no_page in pages" :value="no_page">{{ displayPage(no_page) }}</option>
            </select>
            <span class="em-ml-8">{{ translate('COM_EMUNDUS_FILES_PAGE_ON') }}</span>
            <span class="em-ml-8 em-mr-8">{{ pages.length }}</span>
          </div>
        </template>
      </div>
    </div>

    <div v-if="files">
      <tabs v-if="$props.type === 'evaluation'" :tabs="tabs" @updateTab="updateTab"></tabs>
      <hr/>

	    <div v-if="!filtersLoading" class="em-flex-row em-flex-space-between em-mb-16">
		    <div id="filters" class="em-flex-row-start">
			    <div id="default-filters" v-if="defaultFilters.length > 0" v-click-outside="onDefaultFiltersClickOutside">
				    <div class="em-tabs em-pointer em-flex-row em-s-justify-content-center" @click="openedFilters = !openedFilters">
					    <span>{{ translate('COM_EMUNDUS_FILES_FILTER') }}</span>
					    <span class="material-icons-outlined">filter_list</span>
				    </div>
				    <ul :class="{'hidden': !openedFilters, 'em-input': true}">
					    <li v-for="filter in defaultFilters" :key="filter.id" @click="addFilter(filter)" class="em-pointer">{{ filter.label }}</li>
				    </ul>
			    </div>
			    <div id="applied-filters" v-if="filters.length > 0" class="em-flex-row">
				    <div v-for="filter in filters" :key="filter.key" class="applied-filter em-ml-8 em-flex-row">
					    <label class="filter-label em-mr-8" :for="filter.id + '-' + filter.key" :title="filter.label">{{ filter.label }}</label>
					    <select class="em-mr-8" v-model="filter.selectedOperator">
							    <option v-for="operator in filter.operators" :key="operator.value" :value="operator.value">{{ operator.label }}</option>
					    </select>
					    <input v-if="filter.type == 'field'" :name="filter.id + '-' + filter.key" type="text" :placeholder="filter.label" v-model="filter.selectedValue"/>
					    <input v-else-if="filter.type == 'date'" :name="filter.id + '-' + filter.key" type="date" v-model="filter.selectedValue">
					    <multiselect
							  v-else-if="filter.type == 'select'"
								v-model="filter.selectedValue"
							  label="label"
							  track-by="value"
							  :options="filter.values"
							  :multiple="true"
							  :taggable="false"
							  select-label=""
							  :placeholder="filter.label"
							  selected-label=""
							  deselect-label=""
							  :close-on-select="true"
							  :clear-on-select="false"
							  :searchable="true"
							  :allow-empty="true"
							  width="250px"
					    ></multiselect>
					    <span class="material-icons-outlined em-pointer em-red-500-color" @click="removeFilter(filter)">close</span>
				    </div>
			    </div>
		    </div>
		    <div v-if="defaultFilters.length > 0" class="em-flex-row">
			    <span class="material-icons-outlined em-mr-16 em-red-500-color" :class="{'em-pointer': filters.length > 0, 'em-pointer-disbabled': filters.length < 1 }" :alt="translate('COM_EMUNDUS_FILES_RESET_FILTERS')" @click="resetFilters">filter_alt_off</span>
			    <button class="em-primary-button em-pointer" @click="applyFilters">{{ translate('COM_EMUNDUS_FILES_APPLY_FILTER') }}</button>
		    </div>
	    </div>
	    <div v-else class="em-flex-row em-flex-space-between em-mb-16">
			    <skeleton height="40px" width="96px" class="em-border-radius-8"></skeleton>
			    <skeleton height="40px" width="120px" class="em-border-radius-8"></skeleton>
	    </div>
    </div>

    <div class="em-flex-row em-align-start" v-if="files && columns && files.length > 0" :key="reloadFiles">
      <div id="table_columns_move_right" :class="moveRight ? '' : 'em-disabled-state'" class="table-columns-move em-flex-column em-mr-4" @click="scrollToRight">
        <span class="material-icons-outlined em-pointer" style="font-size: 16px">arrow_back</span>
      </div>

      <el-table
          ref="tableFiles"
          style="width: 100%"
          height="calc(100vh - 250px)"
          :data="files"
          @select-all="selectRow"
          @select="selectRow">
        <el-table-column
            fixed
            type="selection"
            width="55">
        </el-table-column>
        <el-table-column
            fixed
            :label="translate('COM_EMUNDUS_ONBOARD_FILE')"
            width="270">
          <template slot-scope="scope">
            <div @click="openApplication(scope.row)" class="em-pointer">
              <p class="em-font-weight-500">{{ scope.row.applicant_name }}</p>
              <span class="em-text-neutral-500 em-font-size-14">{{ scope.row.fnum }}</span>
            </div>
          </template>
        </el-table-column>

        <template v-for="column in columns" v-if="column.show_in_list_summary == 1">
          <el-table-column
              v-if="column.name === 'status'"
              min-width="180">
            <template slot="header" slot-scope="scope" >
              <span :title="translate('COM_EMUNDUS_ONBOARD_STATUS')" class="em-neutral-700-color">{{translate('COM_EMUNDUS_ONBOARD_STATUS')}}</span>
            </template>
            <template slot-scope="scope">
              <p :class="'label label-'+scope.row.status_color" class="em-status">{{ scope.row.status }}</p>
            </template>
          </el-table-column>

          <el-table-column
              v-else-if="column.name === 'assocs'"
              min-width="180">
            <template slot="header" slot-scope="scope" >
              <span :title="translate('COM_EMUNDUS_FILES_ASSOCS')" class="em-neutral-700-color">{{translate('COM_EMUNDUS_FILES_ASSOCS')}}</span>
            </template>
            <template slot-scope="scope">
              <div class="em-group-assoc-column">
                <span v-for="group in scope.row.assocs" :class="group.class" class="em-status em-mb-4">{{ group.label }}</span>
              </div>
            </template>
          </el-table-column>

          <el-table-column
              v-else
              min-width="180">
            <template slot="header" slot-scope="scope" >
              <span :title="column.label" class="em-neutral-700-color">{{column.label}}</span>
            </template>
            <template slot-scope="scope">
              <p v-html="scope.row[column.name]"></p>
            </template>
          </el-table-column>
        </template>
      </el-table>

      <div id="table_columns_move_left" v-if="moveLeft" class="table-columns-move em-flex-column em-ml-4" @click="scrollToLeft">
        <span class="material-icons-outlined em-pointer" style="font-size: 16px">arrow_forward</span>
      </div>
    </div>

    <div v-if="files && columns && files.length === 0">
      <h6>{{ translate('COM_EMUNDUS_ONBOARD_NOFILES') }}</h6>
    </div>

    <div v-if="rows_selected.length > 0" class="selected-rows-tip">
      <div class="selected-rows-tip__content em-flex-row">
        <span v-if="rows_selected.length === 1">{{ rows_selected.length }} {{ translate('COM_EMUNDUS_FILES_ELEMENT_SELECTED') }} :</span>
        <span v-else-if="rows_selected.length > 1">{{ rows_selected.length }} {{ translate('COM_EMUNDUS_FILES_ELEMENTS_SELECTED') }} :</span>
        <a class="em-pointer em-ml-16" @click="toggleSelection()">{{ translate('COM_EMUNDUS_FILES_UNSELECT') }}</a>
        <a class="em-pointer em-ml-16" @click="openInNewTab()">{{ translate('COM_EMUNDUS_FILES_OPEN_IN_NEW_TAB') }}</a>
      </div>

    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import Tabs from "@/components/Files/Tabs.vue";
import { Table,TableColumn } from 'element-ui';

/** SERVICES **/
import filesService from 'com_emundus/src/services/files';
import errors from '@/mixins/errors';
import Application from '@/components/Files/Application';
import multiselect from 'vue-multiselect';
import Skeleton from '../../components/Skeleton';

export default {
  name: 'Files',
  components: {
	  Skeleton,
    Application,
    Tabs,
    'el-table': Table,
    'el-table-column': TableColumn,
	  multiselect
  },
  props: {
    type: {
			String,
	    default: ''
    },
    ratio: {
      type: String,
      default: '66/33'
    },
    user: {
      type: String,
      required: true,
    },
  },
  mixins: [errors],
  data: () => ({
    loading: false,
	  filtersLoading: false,
    moveRight: false,
    moveLeft: true,
    scrolling: null,
    reloadFiles: 0,

    total_count: 0,
    tabs: [
      {
        label: 'COM_EMUNDUS_FILES_TO_EVALUATE',
        name: 'to_evaluate',
        total: 0,
        selected: false,
      },
      {
        label: 'COM_EMUNDUS_FILES_EVALUATED',
        name: 'evaluated',
        total: 0,
        selected: false,
      },
      {
        label: 'COM_EMUNDUS_FILES_ALL',
        name: 'all',
        total: 0,
        selected: false,
      },
    ],
    selected_tab: 0,
    files: null,
    columns: null,
    page: null,
    pages: null,
    limit: null,
	  defaultFilters: [],
	  filters: [],
	  openedFilters: false,
    currentFile: null,
    rows_selected: [],
  }),
  created(){
		this.addKeyupEnterEventlistener();

    this.getLimit();
    this.getPage();
    if(this.$props.type === 'evaluation') {
      filesService.getSelectedTab(this.$props.type).then((tab) => {
        this.tabs.forEach((value, i) => {
          if (value.name === tab.data) {
            this.tabs[i].selected = true;
            this.selected_tab = i;
          }
        });

        this.getFiles();
      });
    }

  },
  methods: {
	  addKeyupEnterEventlistener(){
		  window.document.addEventListener('keyup', (e) => {
			  if (e.key === 'Enter'){
				  e.preventDefault();
					e.stopPropagation();
			  }
		  });
	  },
    getLimit(){
      filesService.getLimit(this.$props.type).then((limit) => {
        if(limit.status == 1) {
          this.limit = limit.data;
        } else {
          this.displayError('COM_EMUNDUS_ERROR_OCCURED',limit.msg);
        }
      });
    },
    getPage(){
      filesService.getPage(this.$props.type).then((page) => {
        if(page.status == 1) {
          this.page = page.data;
        } else {
          this.displayError('COM_EMUNDUS_ERROR_OCCURED',page.msg);
        }
      });
    },
    getFiles(refresh = false){
      document.querySelector('body.layout-evaluation').style.overflow= 'visible';
      this.loading = true;

      let fnum = window.location.href.split('#')[1];
      if(typeof fnum == 'undefined'){
        fnum = '';
      }

      if (this.$props.type === 'evaluation') {
        filesService.getFiles(this.$props.type,refresh,this.limit,this.page).then((files) => {
          if(files.status == 1) {
            this.total_count = files.total;
            if(typeof files.data.all !== 'undefined') {
              this.files = files.data.all;
            } else {
              this.files = [];
            }
            this.tabs.forEach((tab,i) => {
              if(files[tab.name]){
                this.tabs[i].total = files[tab.name].total;
              }
            })

            filesService.getColumns(this.$props.type).then((columns) => {
              this.columns = columns.data;

              if(fnum !== ''){
                this.openModal(fnum);
              }

	            this.getFilters();
              this.loading = false;
              this.reloadFiles++;

              let total_pages = Math.ceil(this.tabs[this.selected_tab].total/this.limit);
              this.pages = Array.from(Array(total_pages).keys())
            });


          } else {
            this.loading = false;
            this.displayError('COM_EMUNDUS_ERROR_OCCURED',files.msg);
          }
        });
      }
    },
	  getFilters() {
			this.filtersLoading = true;
			filesService.getFilters().then((response) => {
				if (response.status == 1) {
					if (this.filters.length == 0 && response.data.applied_filters.length > 0 ) {
						response.data.applied_filters.forEach((applied_filter) => {
							const filter = response.data.default_filters.find((default_filter) => {
								return default_filter.id == applied_filter.id;
							});

							this.addFilter(filter, applied_filter.selectedValue, applied_filter.selectedOperator);
						});
					}

					this.defaultFilters = response.data.default_filters;
					this.filtersLoading = false;
				} else {
					this.displayError('COM_EMUNDUS_ERROR_OCCURED', response.msg);
					this.filtersLoading = false;
				}
			});
	  },
	  addFilter(filter, selectedValue = null, selectedOperator = null) {
			this.filters.push({
				id: filter.id,
				key: Math.random(),
				type: filter.type,
				values: filter.values,
				label: filter.label,
				selectedValue: selectedValue,
				operators: filter.operators,
				selectedOperator: selectedOperator === null ? filter.operators[0].value : selectedOperator
			});
	  },
	  removeFilter(filterToRemove) {
		  this.filters.find((filter, index) => {
				if (filter.key == filterToRemove.key) {
					this.filters.splice(index, 1);
				}
		  });
	  },
	  resetFilters() {
			if (this.filters.length > 0) {
				this.filters = [];

				filesService.applyFilters(this.filters).then((response) => {
					this.getFiles(true);
				});
			}
	  },
	  applyFilters()
	  {
			const filtersToApply = this.filters.map((filter) => {
				if (filter.selectedValue !== null) {
					return {
						id: filter.id,
						type: filter.type,
						selectedValue: filter.selectedValue,
						selectedOperator: filter.selectedOperator
					}
				}
			});

		  filesService.applyFilters(filtersToApply).then((response) => {
			  this.getFiles(true);
		  });
	  },
    updateLimit(limit){
      this.loading = true;
      filesService.updateLimit(limit).then((result) => {
        if(result.status == 1) {
          this.page = 0;
          this.getFiles(true);
        } else {
          this.loading = false;
          this.displayError('COM_EMUNDUS_ERROR_OCCURED',result.msg);
        }
      });
    },
    prevPage(){
      this.page--;
      this.updatePage();
    },
    nextPage(){
      this.page++;
      this.updatePage();
    },
    updatePage(page){
      filesService.updatePage(page).then((result) => {
        if(result.status == 1) {
          this.getFiles(true);
        } else {
          this.loading = false;
          this.displayError('COM_EMUNDUS_ERROR_OCCURED',result.msg);
        }
      });
    },

    openModal(file){
      this.currentFile = file;

      setTimeout(() => {
        this.$modal.show("application-modal");
      },500)
    },
    updateTab(tab){
      this.selected_tab = this.tabs.map(e => e.name).indexOf(tab);

      filesService.setSelectedTab(tab).then(() => {
        this.getLimit();
        this.getPage();
        this.getFiles(true);
      });
    },
    openApplication(row){
      this.openModal(row);
    },
    selectRow(selection,row){
      this.rows_selected = selection;
    },
    toggleSelection(){
      this.$refs.tableFiles.clearSelection();
      this.rows_selected = [];
    },
    openInNewTab(){
      this.rows_selected.forEach((row) => {
        window.open(window.location.href+'#'+row.fnum, '_blank');
      });
    },
    scrollToLeft(){
      this.moveRight = true;

      let tableScroll = document.getElementsByClassName('el-table__body-wrapper')[0];
      tableScroll.scrollLeft += 180;
    },
    scrollToRight(){
      let tableScroll = document.getElementsByClassName('el-table__body-wrapper')[0];
      tableScroll.scrollLeft -= 180;
      if(tableScroll.scrollLeft == 0){
        this.moveRight = false;
      }
    },

    stopScrolling(){
      clearInterval(this.scrolling);
      this.scrolling = null;
    },

    displayPage(page) {
      return page + 1;
    },

	  onDefaultFiltersClickOutside()
	  {
			if (this.openedFilters) {
				this.openedFilters = false;
			}
	  }
  },
  watch: {
    limit: function(value, oldVal){
      if(oldVal !== null && !this.loading) {
        this.updateLimit(value);
      }
    },
    page: function(value, oldVal){
      if(oldVal !== null && !this.loading) {
        this.updatePage(value);
      }
    }
  },
}
</script>

<style lang="scss" scoped>
.em-files{
  width: 98% !important;
  margin: auto;
}
.table-columns-move{
  height: calc(100vh - 250px);
  border-radius: 8px;
  background: white;
  width: 24px;
}
select.em-select-no-border{
  background-color: transparent !important;
}

#filters {
	align-items: flex-start;

	#default-filters {
		position: relative;

		ul {
			position: absolute;
			top: 50px;
			z-index: 5;
			background-color: white;
			margin: 0;
			padding: 0;
			list-style-type: none;
			min-width: 300px;
			max-height: 500px;
			overflow-y: scroll;

			li {
				padding: 8px;
				transition: all .3s;

				&:hover {
					background: ghostwhite;
				}
			}
		}
	}

	#applied-filters {
		max-width: 90%;
		flex-wrap: wrap;
		row-gap: 16px;

		.multiselect {
			height: 40px !important;

			.multiselect__tags {
				height: 40px !important;

				.multiselect__tags-wrap {
					height: 24px !important;
				}
			}
		}
	}
}

.filter-label {
	min-width: 100px;
	max-width: 220px;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.em-group-assoc-column{
  display: flex;
  flex-direction: column;
  overflow-y: scroll;
  height: 75px;
  scrollbar-width: none;
}
.em-group-assoc-column::-webkit-scrollbar{
  display: none;
}
</style>