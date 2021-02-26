import Vue from 'vue'
import App from './App.vue'
import addWorkflow from "@/addWorkflow";
import addItem from "@/addItem";

//import VueSidebarMenu from 'vue-sidebar-menu'
import 'vue-sidebar-menu/dist/vue-sidebar-menu.css'
import "@fortawesome/fontawesome-free/css/all.css";
import "@fortawesome/fontawesome-free/js/all.js";

//import Bootstrap and BootstrapVue CSS (order is important)
import { BootstrapVue, IconsPlugin} from "bootstrap-vue";

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

import axios from 'axios';
const qs = require('qs');

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);

Vue.config.productionTip = false;
Vue.config.devtools = true;

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
      return h(addItem, {
        props:{}
      });
    }
  });
}

