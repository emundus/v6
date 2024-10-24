import Vue from 'vue'
import Dashboard from "@/Dashboard"
import VueFusionCharts from 'vue-fusioncharts';
import FusionCharts from 'fusioncharts';
import Column2D from 'fusioncharts/fusioncharts.charts';
import FusionTheme from 'fusioncharts/themes/fusioncharts.theme.fusion';
import { VTooltip, VPopover, VClosePopover } from 'v-tooltip';
import translate from './mixins/translate';

Vue.config.productionTip = false;
Vue.config.devtools = false;

Vue.directive('close-popover', VClosePopover);

Vue.directive('tooltip', VTooltip);
Vue.component('v-popover', VPopover);

Vue.use(VueFusionCharts, FusionCharts, Column2D, FusionTheme);
Vue.use(VTooltip);
Vue.mixin(translate);

if (document.getElementById('em-dashboard-vue')) {
  const element = document.getElementById('em-dashboard-vue');

  const vue = new Vue({
    el: '#em-dashboard-vue',
    render(h) {
      return h(Dashboard, {
        props: {
          programmeFilter: element.attributes['programmeFilter'].value,
          displayDescription: element.attributes['displayDescription'].value,
          displayShapes: element.attributes['displayShapes'].value,
          displayTchoozy: element.attributes['displayTchoozy'].value,
          displayName: element.attributes['displayName'].value,
          name: element.attributes['name'].value,
          language: element.attributes['language'].value,
          profile_name: element.attributes['profile_name'].value,
          profile_description: element.attributes['profile_description'].value,
        }
      });
    }
  });
}
