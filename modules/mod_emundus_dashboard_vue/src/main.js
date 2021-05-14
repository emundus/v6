import Vue from 'vue'
import Dashboard from "@/Dashboard"
import VueFusionCharts from 'vue-fusioncharts';
import FusionCharts from 'fusioncharts';
import Column2D from 'fusioncharts/fusioncharts.charts';
import FusionTheme from 'fusioncharts/themes/fusioncharts.theme.fusion';

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(VueFusionCharts, FusionCharts, Column2D, FusionTheme);

if (document.getElementById('em-dashboard-vue')) {
  new Vue({
    el: '#em-dashboard-vue',
    render(h) {
      return h(Dashboard, {
        props: {}
      });
    }
  });
}
