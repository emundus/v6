import Vue from 'vue';
import store from "./store";
import Attachments from './components/Attachments.vue';
import VModal from 'vue-js-modal';

Vue.config.productionTip = false;

Vue.use(store);
Vue.use(VModal);

if (document.getElementById("em-application-attachment")) {
  new Vue({
    el: document.getElementById("em-application-attachment"),
    store,
    render(h) {
      return h(
        Attachments,
        {
          props: {
            fnum: this.$el.attributes.fnum.value,
            user: this.$el.attributes.user.value,
          },
      });
    },
  })
}