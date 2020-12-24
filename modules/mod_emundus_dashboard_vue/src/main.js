import Vue from 'vue'
import Dashboard from "@/Dashboard"

Vue.config.productionTip = false;
Vue.config.devtools = true;

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
