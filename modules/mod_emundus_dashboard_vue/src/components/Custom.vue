<template>
  <div class='tchooz-widget' :class="['col-md-' + selectedWidget.size,'col-sm-' + selectedWidget.size_small]">
    <div class='section-sub-menu' style='margin-bottom: 10px' :class="params.type === 'article' ? 'tchooz-widget__article-overflow' : ''">
      <div id="chart-container" v-if="params.type === 'chart'">
        <fusioncharts
            :key="chart_render"
            :type="chart_type"
            :width="'100%'"
            :height="'300'"
            :dataFormat="dataFormat"
            :dataSource="dataSource"
        >
        </fusioncharts>
        <div>
          <multiselect
              v-model="selectedWidget"
              :class="'tchooz-widget__select'"
              label="label"
              track-by="id"
              :options="widgets"
              :multiple="false"
              :taggable="false"
              select-label=""
              selected-label=""
              deselect-label=""
              :close-on-select="true"
              :clear-on-select="false"
              :searchable="false"
          ></multiselect>
        </div>
      </div>
      <div v-else :class="selectedWidget.class">
        <div v-html="datas"></div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Multiselect from 'vue-multiselect';

const qs = require("qs");


export default {
  name: "Custom",

  components: {
    Multiselect
  },

  props: {
    widget: Object,
    colors: String
  },

  data: () => ({
    widgets: [],
    chart_render: 0,
    position: null,
    selectedWidget: null,
    type: 'chart',
    // Fusion charts variables
    datas: {},
    chart_type: 'column2d',
    renderAt: "chart-container",
    dataFormat: "json",
    dataSource: {},
    params: []
  }),

  methods: {
    renderChart(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=renderchartbytag",
        params: {
          widget: this.selectedWidget.id,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.params = JSON.parse(this.selectedWidget.params);
        this.chart_type = this.params.chart_type;
        this.dataSource = response.data.dataset;

        this.chart_render++;
        //
      });
    },

    getArticle(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getarticle",
        params: {
          widget: this.selectedWidget.id,
          article: this.params.id
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.datas = response.data.data;
      });
    },

    getWidgets(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getallwidgetsbysize",
        params: {
          size: this.selectedWidget.size
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.widgets = response.data.data;
      });
    },

    updateDashboard(){
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=updatemydashboard",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          widget: this.selectedWidget.id,
          position: this.position
        })
      }).then(response => {
        this.renderChart();
      });
    }
  },

  created() {
    this.selectedWidget = this.widget;
    this.params = JSON.parse(this.selectedWidget.params);
    this.position = this.selectedWidget.position;
    this.type = this.params.type;
    switch (this.type){
      case 'article':
        this.getArticle();
        break;
      case 'chart':
        this.renderChart();
    }
    this.getWidgets();
  },

  watch: {
    selectedWidget: function(value){
      this.updateDashboard();
    }
  }
}
</script>

<style scoped>
.section-sub-menu{
  display: block;
  width: 100%;
  height: 100%;
  justify-content: center;
  border-radius: 4px;
  background-color: #fff;
  color: #1f1f1f;
  box-shadow: 0 1px 2px 0 hsla(0,0%,41.2%,.19);
  padding: 30px;
}
</style>
