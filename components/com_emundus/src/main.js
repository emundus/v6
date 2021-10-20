import Vue from 'vue';
import store from "./store";
import router from "./router";
import i18n from "./i18n";
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
    i18n,
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