import Vue from 'vue';
import store from "./store";
import router from "./router";
import VModal from 'vue-js-modal';
import App from './App.vue';

Vue.config.productionTip = false;

Vue.use(store);
Vue.use(VModal);

let mountApp = false;
let elementId = "";
let data = {};
let componentName = "";

if (document.getElementById("em-application-attachment")) {
  const element = document.getElementById("em-application-attachment");
  Array.prototype.slice.call(element.attributes).forEach(function (attr) {
    data[attr.name] = attr.value;
  });

  componentName = "attachments";
  elementId = "#em-application-attachment";
  mountApp = true;
}

if (mountApp) {
  new Vue({
    el: elementId,
    store,
    router,
    render(h) {
      return h(
        App,
        {
          props: {
            componentName: componentName,
            data: data
          },
        }
      );
    }
  })
}