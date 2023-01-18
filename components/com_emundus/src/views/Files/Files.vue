<template>
  <div class="em-mt-16 em-ml-32 em-files">
    <Application v-if="currentFile" :file="currentFile" :type="$props.type" :user="$props.user" @getFiles="getFiles(true)" />

    <div class="em-mb-12 em-flex-row em-flex-space-between">
      <p class="em-h4">{{ translate('COM_EMUNDUS_FILES_'+type.toUpperCase()) }}</p>
      <span class="material-icons-outlined" @click="getFiles(true)">refresh</span>
    </div>

    <div v-if="files">
      <tabs v-if="$props.type === 'evaluation'" :counts="counts" @updateTab="updateTab"></tabs>
      <hr/>
    </div>

    <el-table
        ref="tableFiles"
        style="width: 100%"
        max-height="500"
        v-if="files && columns && currentTab && files[currentTab].length > 0"
        :data="files[currentTab]"
        @select-all="selectRow"
        @select="selectRow"
        @cell-click="openApplication">
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
          <div>
            <p class="em-font-weight-500">{{ scope.row.applicant_name }}</p>
            <p class="em-neutral-700-color em-font-size-14">{{ scope.row.fnum }}</p>
          </div>
        </template>
      </el-table-column>
      <el-table-column
          width="170"
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
          width="170">
        <template slot="header" slot-scope="scope">
          <span :title="column.label" class="em-neutral-700-color">{{column.label}}</span>
        </template>
        <template slot-scope="scope">
          <p>{{scope.row[column.name]}}</p>
        </template>
      </el-table-column>
      <el-table-column width="50" fixed="right" class-name="em-open-application-cell">
        <template slot-scope="scope">
          <span class="material-icons-outlined em-pointer" @click="openModal(scope.row)" style="color: black">open_in_new</span>
        </template>
      </el-table-column>
    </el-table>

    <div v-if="files && columns && currentTab && files[currentTab].length === 0">
      <span class="em-h6">{{ translate('COM_EMUNDUS_ONBOARD_NOFILES') }}</span>
    </div>

    <div v-if="rows_selected.length > 0" class="selected-rows-tip">
      <div class="selected-rows-tip__content em-flex-row">
        <span>{{ rows_selected.length }} {{ translate('COM_EMUNDUS_FILES_ELEMENTS_SELECTED') }} :</span>
        <a class="em-pointer em-ml-16" @click="toggleSelection()">{{ translate('COM_EMUNDUS_FILES_UNSELECT') }}</a>
        <a class="em-pointer em-ml-16" @click="openInNewTab()">{{ translate('COM_EMUNDUS_FILES_OPEN_IN_NEW_TAB') }}</a>
      </div>

    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import Tabs from "@/components/Files/Tabs";
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
    type: String,
    user: {
      type: String,
      required: true,
    },
  },
  mixins: [errors],
  data: () => ({
    loading: false,

    total_count: 0,
    counts: {
      to_evaluate: 0,
      evaluated: 0,
    },
    files: null,
    columns: null,
    display_status: false,

    currentFile: null,
    currentTab: null,
    rows_selected: [],
  }),
  created(){
    this.getFiles();
    if(this.$props.type === 'evaluation') {
      this.currentTab = 'to_evaluate';
    }
  },
  methods: {
    getFiles(refresh = false){
      this.loading = true;

      let fnum = window.location.href.split('#')[1];
      if(typeof fnum == 'undefined'){
        fnum = '';
      }

      if(this.$props.type === 'evaluation') {
        filesService.getFiles(this.$props.type,refresh).then((files) => {
          if(files.status == 1) {
            this.files = files.data;
            this.counts.to_evaluate = this.files.to_evaluate.length;
            this.counts.evaluated = this.files.evaluated.length;

            filesService.getColumns(this.$props.type).then((columns) => {
              this.columns = columns.data;

              Object.values(this.columns).forEach((column) => {
                if(column.name === 'status'){
                  this.display_status = true;
                }
              });
            });

            if(fnum !== ''){
              Object.values(this.files.all).forEach((file) => {
                if(file.fnum === fnum && this.currentFile === null){
                  this.openModal(file);
                }
              });
            }
          } else {
            this.displayError('COM_EMUNDUS_ERROR_OCCURED',files.msg);
          }

          this.loading = false;
        });
      }
    },
    openModal(file){
      this.currentFile = file;

      setTimeout(() => {
        this.$modal.show("application-modal");
      },500)
    },
    updateTab(tab){
      this.currentTab = tab;
    },
    openApplication(row, column, cell, event){
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
    }
  }
}
</script>

<style scoped>
.em-files{
  width: calc(100% - 75px) !important;
  margin-left: auto;
}
</style>