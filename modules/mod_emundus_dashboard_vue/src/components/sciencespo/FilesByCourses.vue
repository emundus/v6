<template>
  <div class='col-md-6 col-sm-12 tchooz-widget'>
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
  name: "FilesByCourses",

  components: {},

  props: {
    program: Number,
    colors: String,
    session: Number,
  },

  data: () => ({
    dataset: {},
    category: [],
    label: 'Total',
    type: "stackedcolumn2d",
    renderAt: "chart-container",
    dataFormat: "json",
    chartData: [],
    dataSource: {},
  }),

  methods: {
    renderFilesByStatus(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getfilescountbystatusandcourses",
        params: {
          program: this.program,
          session: this.session,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.dataset = response.data.dataset;
        this.category = response.data.category;
        let title =Joomla.JText._("COM_EMUNDUS_DASHBOARD_FILES_BY_COURSES");

        if(this.session === 1){
          title += ' - ' + Joomla.JText._("COM_EMUNDUS_DASHBOARD_JUNE_SESSION")
        } else if(this.session === 2){
          title += ' - ' + Joomla.JText._("COM_EMUNDUS_DASHBOARD_JULY_SESSION")
        }

        // Render chart
        this.dataSource = {
          chart: {
            caption: title,
            xAxisname: "",
            yAxisName: "",
            showValues: 1,
            minPlotHeightForValue: 15,
            exportEnabled: 1,
            paletteColors: this.colors,
            theme: "fusion"
          },
          categories: [
            {
              category: this.category
            }
          ],
          dataset: this.dataset
        }
        //
      });
    },
  },

  created() {
    this.program = 'univ';
    this.renderFilesByStatus();
  },

  watch:{
    program: function () {
      this.renderFilesByStatus();
    }
  }
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
