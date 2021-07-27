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
<!--      <label>Nombre de dossiers <br/><span>{{label}}</span></label>
      <p class='big-number'>{{files}}</p>-->
    </div>
  </div>
</template>

<script>
import axios from "axios";

const qs = require("qs");

export default {
  name: "FilesNumberByStatus",

  components: {},

  props: {
    program: Number,
    colors: String
  },

  data: () => ({
    files: {},
    status: [],
    label: 'Total',
    type: "column2d",
    renderAt: "chart-container",
    dataFormat: "json",
    chartData: [],
    dataSource: {},
  }),

  methods: {
    renderFilesByStatus(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getfilescountbystatus",
      }).then(response => {
        this.files = response.data.files;
        this.status = response.data.status;

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
          data: this.files
        };
        //
      });
    },
  },

  created() {
    this.renderFilesByStatus();
  },
}
</script>

<style scoped lang="scss">
  .tchooz-widget{
    height: auto !important;
  }
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

  .big-number{
    font-size: 30px;
    margin-top: 10%;
  }

  label {
    font-size: 21px;
  }

</style>
