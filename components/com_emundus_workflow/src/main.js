import Vue from 'vue'
// import WorkflowSpace from "./WorkflowSpace";
// import WorkflowDashboard from "./WorkflowDashboard";
import addWorkflow from "@/addWorkflow";

//import VueSidebarMenu from 'vue-sidebar-menu'
//import 'vue-sidebar-menu/dist/vue-sidebar-menu.css'
import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/js/all.js";
import VueSweetalert2 from 'vue-sweetalert2';
import VModal from 'vue-js-modal';

import axios from 'axios';
const qs = require('qs');

import { BootstrapVue, IconsPlugin } from 'bootstrap-vue'

// Import Bootstrap an BootstrapVue CSS files (order is important)
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

import stepflow from "./components/StepFlow";

// Make BootstrapVue available throughout your project
Vue.use(BootstrapVue)
// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin)

Vue.use(VModal);
Vue.config.productionTip = false;
Vue.config.devtools = true;

//using Sweet alert
Vue.use(VueSweetalert2);

//using color picker
import InputColorPicker from "vue-native-color-picker";
Vue.use(InputColorPicker);

if (document.getElementById('add-workflow')) {
  new Vue({
    el: '#add-workflow',
    render(h) {
      return h(addWorkflow, {
        props: {}
      });
    }
  });
}

if(document.getElementById('add-item')) {
  new Vue({
    el: '#add-item',
    render(h) {
      return h(stepflow, {
        props:{}
      });
    }
  });
}
