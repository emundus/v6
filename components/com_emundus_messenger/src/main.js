import Vue from 'vue';
import Messages from "./components/Messages";

Vue.config.productionTip = false;
Vue.config.devtools = true;

if (document.getElementById('em-messages-vue')) {
  new Vue({
    el: '#em-messages-vue',
    render(h) {
      return h(Messages, {
        props: {}
      });
    }
  });
}
