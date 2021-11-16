import Vue from 'vue';
import workflowBuilder from './workflowBuilder.vue';
import stepsBuilder from './stepsBuilder.vue';
import Multiselect from 'vue-multiselect';

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.component('multiselect', Multiselect);

if (document.getElementById('workflows-dashboard')) {
  new Vue({
    el: '#workflows-dashboard',
    render(h) {
      return h(workflowBuilder, {
        props: {}
      });
    }
  });
}

if(document.getElementById('workflow-steps')) {
  new Vue({
    el: '#workflow-steps',
    render(h) {
      return h(stepsBuilder, {
        props:{}
      });
    }
  });
}


