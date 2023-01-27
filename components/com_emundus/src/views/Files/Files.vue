<template>
  <div class="em-mt-16 em-ml-32 em-files">
    <Application v-if="currentFile" :file="currentFile" :type="$props.type" :user="$props.user" :ratio="$props.ratio" @getFiles="getFiles(true)" />

    <div class="em-mb-12 em-flex-row em-flex-space-between">
      <p class="em-h4">{{ translate('COM_EMUNDUS_FILES_'+type.toUpperCase()) }}</p>
<!--      <span class="material-icons-outlined" @click="getFiles(true)">refresh</span>-->
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
            <select class="em-select-no-border em-ml-8" style="width: 40px;height: 20px;" v-model="limit">
              <option>10</option>
              <option>25</option>
              <option>50</option>
              <option>100</option>
            </select>
          </div>
        </div>
        <div class="em-flex-row" v-if="displayPagination">
          <span>{{ translate('COM_EMUNDUS_FILES_PAGE') }} {{ displayPage }}</span>
          <span class="em-ml-8 em-mr-8">|</span>
          <span class="material-icons-outlined em-pointer" v-if="page != 0" @click="prevPage">chevron_left</span>
          <span class="material-icons-outlined em-pointer" @click="nextPage">navigate_next</span>
        </div>
      </div>

    </div>

    <div v-if="files">
      <tabs v-if="$props.type === 'evaluation'" :tabs="tabs" @updateTab="updateTab"></tabs>
      <hr/>

	    <div class="em-flex-row em-flex-space-between em-mb-16">
		    <div class="em-flex-row">
			    <div id="default-filters" v-if="defaultFilters.length > 0">
				    <div class="em-tabs em-pointer em-flex-row em-s-justify-content-center" @click="openedFilters = !openedFilters">
					    <span>{{ translate('COM_EMUNDUS_FILES_FILTER') }}</span>
					    <span class="material-icons-outlined">filter_list</span>
				    </div>
				    <ul :class="{'hidden': !openedFilters, 'em-input': true}">
					    <li v-for="filter in defaultFilters" :key="filter.id" @click="addFilter(filter)" class="em-pointer">{{ filter.label }}</li>
				    </ul>
			    </div>
			    <div id="applied-filters" v-if="filters.length > 0" class="em-flex-row">
				    <div v-for="filter in filters" :key="filter.key" class="em-ml-8">
					    <input v-if="filter.type == 'field'" type="text" :placeholder="filter.label" v-model="filter.selectedValue"/>
					    <input v-else-if="filter.type == 'date'" type="date" v-model="filter.selectedValue">
					    <select v-else-if="filter.type == 'select'" v-model="filter.selectedValue">
						    <option v-for="value in filter.values" :key="value.value" :value="value.value">{{ value.label }}</option>
					    </select>
				    </div>
			    </div>
		    </div>
		    <div>
			    <span class="em-primary-button em-pointer" :class="{'disbaled': filters.length < 1}" @click="applyFilters">{{ translate('COM_EMUNDUS_FILES_APPLY_FILTER') }}</span>
		    </div>
	    </div>
    </div>

    <div class="em-flex-row" v-if="files && columns && files.length > 0">
      <div id="table_columns_move_right" :class="moveRight ? '' : 'em-disabled-state'" class="table-columns-move em-flex-column em-mr-4" @click="scrollToRight">
        <span class="material-icons" style="font-size: 16px">arrow_back</span>
      </div>

      <el-table
          ref="tableFiles"
          style="width: 100%"
          height="500"
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
            width="270"
        >
          <template slot-scope="scope">
            <div @click="openApplication(scope.row)" class="em-pointer">
              <p class="em-font-weight-500">{{ scope.row.applicant_name }}</p>
              <span class="em-text-neutral-500 em-font-size-14">{{ scope.row.fnum }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column
            width="180"
            v-if="display_status"
            :label="translate('COM_EMUNDUS_ONBOARD_STATUS')"
        >
          <template slot-scope="scope">
            <p :class="'label-text-'+scope.row.status_color + ' label-'+scope.row.status_color" class="em-status">{{ scope.row.status }}</p>
          </template>
        </el-table-column>
        <el-table-column
            v-for="column in columns"
            v-if="column.show_in_list_summary == 1"
            min-width="180">
          <template slot="header" slot-scope="scope">
            <span :title="column.label" class="em-neutral-700-color">{{column.label}}</span>
          </template>
          <template slot-scope="scope">
            <p>{{scope.row[column.name]}}</p>
          </template>
        </el-table-column>
<!--        <el-table-column width="50" fixed="right" class-name="em-open-application-cell">
          <template slot-scope="scope">
            <span class="material-icons-outlined em-pointer" @click="openModal(scope.row)" style="color: black">open_in_new</span>
          </template>
        </el-table-column>-->
      </el-table>

      <div id="table_columns_move_left" v-if="moveLeft" class="table-columns-move em-flex-column em-ml-4" @click="scrollToLeft">
        <span class="material-icons" style="font-size: 16px">arrow_forward</span>
      </div>
    </div>

    <div v-if="files && columns && files.length === 0">
      <span class="em-h6">{{ translate('COM_EMUNDUS_ONBOARD_NOFILES') }}</span>
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
import errors from "@/mixins/errors";
import Application from "@/components/Files/Application";

export default {
  name: "Files",
  components: {
    Application,
    Tabs,
    'el-table': Table,
    'el-table-column': TableColumn
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
    moveRight: false,
    moveLeft: true,
    scrolling: null,

    total_count: 0,
    tabs: [
      {
        label: 'COM_EMUNDUS_FILES_TO_EVALUATE',
        name: 'to_evaluate',
        total: 0,
        page: 0,
        limit: 10
      },
      {
        label: 'COM_EMUNDUS_FILES_EVALUATED',
        name: 'evaluated',
        total: 0,
        page: 0,
        limit: 10
      },
      {
        label: 'COM_EMUNDUS_FILES_ALL',
        name: 'all',
        total: 0,
        page: 0,
        limit: 10
      },
    ],
	  tab: 'to_evaluate',
    files: null,
    columns: null,
    display_status: false,
    page: null,
    limit: null,
	  defaultFilters: [],
	  filters: [],
	  openedFilters: false,
    currentFile: null,
    rows_selected: [],
  }),
  created(){
    this.getLimit();
    this.getPage();
    this.getFiles();
  },
  methods: {
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
      this.loading = true;

      let fnum = window.location.href.split('#')[1];
      if(typeof fnum == 'undefined'){
        fnum = '';
      }

      if (this.$props.type === 'evaluation') {
        filesService.getFiles(this.$props.type,refresh,this.limit,this.page).then((files) => {
          if(files.status == 1) {
            this.total_count = files.total;
            this.files = files.data.all;
            this.tabs.forEach((tab,i) => {
              if(files[tab.name]){
                this.tabs[i].total = files[tab.name].total;
              }
            })

            filesService.getColumns(this.$props.type).then((columns) => {
              this.columns = columns.data;

              Object.values(this.columns).forEach((column) => {
                if(column.name === 'status'){
                  this.display_status = true;
                }
              });

              if(fnum !== ''){
                this.openModal(fnum);
              }

	            this.getDefaultFilters();
              this.loading = false;
            });
          } else {
            this.loading = false;
            this.displayError('COM_EMUNDUS_ERROR_OCCURED',files.msg);
          }
        });
      }
    },
	  getDefaultFilters() {
			filesService.getDefaultFilters().then((response) => {
				if (response.status == 1) {
					this.defaultFilters = response.data;
				}
			});
	  },
	  addFilter(filter) {
			this.filters.push({
				id: filter.id,
				key: Math.random(),
				type: filter.type,
				values: filter.values,
				label: filter.label,
				selectedValue: null
			});
	  },
	  applyFilters()
	  {
			filesService.applyFilters(this.filters, this.tab).then((response) => {
				this.getFiles(true);
			})
	  },
    updateLimit(limit){
      this.loading = true;
      filesService.updateLimit(limit).then((result) => {
        if(result.status == 1) {
          this.getFiles(true);
        } else {
          this.loading = false;
          this.displayError('COM_EMUNDUS_ERROR_OCCURED',result.msg);
        }

        this.loading = false;
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
    updatePage(){
      this.loading = true;
      filesService.updatePage(this.page).then((result) => {
        if(result.status == 1) {
          this.getFiles(true);
        } else {
          this.loading = false;
          this.displayError('COM_EMUNDUS_ERROR_OCCURED',result.msg);
        }

        this.loading = false;
      });
    },

    openModal(file){
      this.currentFile = file;

      setTimeout(() => {
        this.$modal.show("application-modal");
      },500)
    },
    updateTab(tab){
      this.loading =true;
	    this.tab = tab.name
	    filesService.setSelectedTab(tab).then(() => {
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

      /*if(this.scrolling == null) {
        this.scrolling = setInterval(() => {
          let tableScroll = document.getElementsByClassName('el-table__body-wrapper')[0];
          tableScroll.scrollLeft += 100;
        }, 10);
      }*/
    },
    scrollToRight(){
      let tableScroll = document.getElementsByClassName('el-table__body-wrapper')[0];
      tableScroll.scrollLeft -= 180;
      if(tableScroll.scrollLeft == 0){
        this.moveRight = false;
      }

      /*if(this.scrolling == null) {
        this.scrolling = setInterval(() => {
          let tableScroll = document.getElementsByClassName('el-table__body-wrapper')[0];
          tableScroll.scrollLeft -= 150;
          if(tableScroll.scrollLeft == 0){
            this.moveRight = false;
          }
        }, 10);
      }*/
    },

    stopScrolling(){
      clearInterval(this.scrolling);
      this.scrolling = null;
    }
  },
  watch: {
    limit: function(value, oldVal){
      console.log(oldVal);
      if(oldVal !== null) {
        this.updateLimit(value);
      }
    }
  },
  computed: {
    displayPage() {
      return this.page + 1;
    },
    displayPagination() {
      return this.files.length * (this.page + 1) < this.total_count;
    }
  }
}
</script>

<style lang="scss" scoped>
.em-files{
  width: calc(100% - 75px) !important;
  margin-left: auto;
}
.table-columns-move{
  height: 500px;
  border-radius: 8px;
  background: white;
  width: 24px;
}
select.em-select-no-border{
  background-color: transparent !important;
}

#default-filters {
	position: relative;

	ul {
		position: absolute;
		top: 50px;
		z-index: 4;
		background-color: white;
		margin: 0;
		padding: 0;
		list-style-type: none;

		li {
			padding: 8px;
		}
	}
}
</style>