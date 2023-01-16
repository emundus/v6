<template>
  <div class="em-mt-16 em-ml-32 em-files">
    <div class="em-mb-12">
      <p class="em-h4">{{ translate('COM_EMUNDUS_FILES_'+type.toUpperCase()) }}</p>
    </div>

    <div v-if="files">
      <tabs v-if="$props.type === 'evaluation'" :counts="counts"></tabs>
      <hr/>
    </div>

    <el-table
        ref="multipleTable"
        v-if="files"
        :data="files.to_evaluate">
      <el-table-column
          type="selection"
          width="55">
      </el-table-column>
      <el-table-column
          v-for="column in columns"
          v-if="column.show_in_list_summary == 1"
          :prop="column.name"
          :label="column.label">
      </el-table-column>
    </el-table>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import Tabs from "@/views/Files/Tabs";
import { Table,TableColumn } from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';

/** SERVICES **/
import filesService from 'com_emundus/src/services/files';
import errors from "@/mixins/errors";

export default {
  name: "Files",
  components: {
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
    columns: null
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
          })
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