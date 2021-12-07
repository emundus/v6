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
        <v-popover :popoverArrowClass="'custom-popover-arraow'">
          <button class="tooltip-target b3 card-button"></button>

          <template slot="popover">
            <div style="max-width: unset">
              <transition :name="'slide-down'" type="transition">
                <div class="container-2 w-container">
                  <nav aria-label="action" class="actions-dropdown">
                    <a class="action-submenu">
                      Modifier mon graphique
                    </a>
                  </nav>
                </div>
              </transition>
            </div>
          </template>
        </v-popover>
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
  },

  created() {
    this.params = JSON.parse(this.widget.params);
    this.type = this.params.type;
    this.renderChart();
  },
}
</script>

<style scoped>

</style>
