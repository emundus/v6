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
  name: "UsersByMonth",

  components: {},

  props: {
    program: Number,
    colors: String
  },

  data: () => ({
    usersnumbers: {},
    days: {},
    label: 'Total',
    type: "line",
    renderAt: "chart-container",
    dataFormat: "json",
    chartData: [],
    dataSource: {},
  }),

  methods: {
    renderUsersByDate(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getusersbyday",
      }).then(response => {
        this.usersnumbers = response.data.users;
        this.days = response.data.days;
        this.total = response.data.total;

        // Render chart
        this.dataSource = {
          chart: {
            animation: 1,
            paletteColors: this.colors,
            caption: Joomla.JText._("COM_EMUNDUS_DASHBOARD_USERS_BY_DAY"),
            subcaption: Joomla.JText._("COM_EMUNDUS_DASHBOARD_USERS_TOTAL") + this.total + Joomla.JText._("COM_EMUNDUS_DASHBOARD_USERS"),
            xaxisname: Joomla.JText._("COM_EMUNDUS_DASHBOARD_USERS_DAYS"),
            yaxisname: Joomla.JText._("COM_EMUNDUS_DASHBOARD_USERS_NUMBER"),
            yAxisMinValue: 0,
            setAdaptiveYMin: 0,
            adjustDiv: 0,
            yAxisValuesStep: 10,
            numbersuffix: "",
            theme: "fusion"
          },
          categories: [
            {
              category: this.days
            }
          ],
          data: this.usersnumbers,
        };
        //
      });
    }
  },

  created() {
    this.renderUsersByDate();
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
