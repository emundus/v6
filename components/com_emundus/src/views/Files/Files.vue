<template>
  <div class="em-mt-16 em-ml-32 em-files">
    <Application v-if="currentFile" :file="currentFile" :type="$props.type" />

    <div class="em-mb-12">
      <p class="em-h4">{{ translate('COM_EMUNDUS_FILES_'+type.toUpperCase()) }}</p>
    </div>

    <div v-if="files">
      <tabs v-if="$props.type === 'evaluation'" :counts="counts"></tabs>
      <hr/>
    </div>

    <el-table
        ref="multipleTable"
        style="width: 100%"
        max-height="500"
        v-if="files && columns"
        :data="files.to_evaluate">
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
          <span class="material-icons-outlined em-pointer" style="color: black">open_in_new</span>
        </template>
      </el-table-column>
    </el-table>

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
    type: String
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

    currentFile: null
  }),
  created(){
    this.loading = true;

    if(this.$props.type === 'evaluation') {
      filesService.getFiles(this.$props.type).then((files) => {
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
            })
          });
        } else {
          this.displayError('COM_EMUNDUS_ERROR_OCCURED',files.msg);
        }

        this.loading = false;
      });
    }
  },
  methods: {}
}
</script>

<style scoped>
.em-files{
  width: calc(100% - 75px) !important;
  margin-left: auto;
}
</style>