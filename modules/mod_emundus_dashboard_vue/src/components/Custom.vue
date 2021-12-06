<template>
  <div class='col-md-5 col-sm-6 tchooz-widget'>
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
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

const qs = require("qs");


export default {
  name: "Custom",

  components: {},

  props: {
    widget: Object,
    colors: String
  },

  data: () => ({
    datas: {},
    status: [],
    label: 'Total',
    type: "column2d",
    renderAt: "chart-container",
    dataFormat: "json",
    chartData: [],
    dataSource: {},
  }),

  methods: {
    renderChart(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=renderchartbytag",
        params: {
          params: JSON.parse(this.widget.params).emundus_setup_tag,
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
            paletteColors: this.colors,
            caption: Joomla.JText._("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS"),
            subcaption: "",
            xaxisname: Joomla.JText._("COM_EMUNDUS_DASHBOARD_STATUS"),
            yaxisname: Joomla.JText._("COM_EMUNDUS_DASHBOARD_FILES_BY_STATUS_NUMBER"),
            numbersuffix: "",
            theme: "fusion"
          },
          data: this.datas
        };
        //
      });
    },
  },

  created() {
    this.renderChart();
  },
}
</script>

<style scoped>

</style>
