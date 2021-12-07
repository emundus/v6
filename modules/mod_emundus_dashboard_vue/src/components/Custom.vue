<template>
  <div class='tchooz-widget' :class="['col-md-' + widget.size,'col-sm-' + widget.size_small]">
    <div class='section-sub-menu' style='margin-bottom: 10px'>
      <div id="chart-container">
        <fusioncharts
            :type="type"
            :width="'100%'"
            :height="'300'"
            :dataformat="dataFormat"
            :dataSource="dataSource"
        >
        </fusioncharts>
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
    selectedWidget: null,
    // Fusion charts variables
    datas: {},
    status: [],
    label: 'Total',
    type: 'column2d',
    renderAt: "chart-container",
    dataFormat: "json",
    chartData: [],
    dataSource: {},
    params: []
  }),

  methods: {
    renderChart(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=renderchartbytag",
        params: {
          widget: this.widget.id,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.datas = response.data.dataset;

        // Render chart
        this.dataSource = {
          chart: {
            animation: 1,
            paletteColors: typeof this.params.colors === 'undefined' ? this.colors : this.params.colors,
            caption: Joomla.JText._(this.params.caption) === '' ? this.params.caption : Joomla.JText._(this.params.caption),
            subcaption: "",
            xaxisname: Joomla.JText._(this.params.xaxisname) === '' ? this.params.xaxisname : Joomla.JText._(this.params.xaxisname),
            yaxisname: Joomla.JText._(this.params.yaxisname) === '' ? this.params.yaxisname : Joomla.JText._(this.params.yaxisname),
            numbersuffix: "",
            theme: "fusion"
          },
          data: this.datas
        };
        //
      });
    },

    getWidgets(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getallwidgets",
      }).then(response => {
        this.widgets = response.data.data;
      });
    }
  },

  created() {
    this.selectedWidget = this.widget;
    this.params = JSON.parse(this.widget.params);
    this.type = this.params.type;
    this.renderChart();
    this.getWidgets();
  },
}
</script>

<style scoped>

</style>
