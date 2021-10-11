import Vue from 'vue';
import Messages from "./components/Messages";
import MessagesCoordinator from "./components/MessagesCoordinator";
import * as VueSpinnersCss from 'vue-spinners-css';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(VueSpinnersCss);

if (document.getElementById('em-messages-vue')) {
  new Vue({
    el: '#em-messages-vue',
    render(h) {
      return h(Messages, {
        props: {
          fnum: this.$el.attributes.fnum.value,
          user: Number(this.$el.attributes.user.value),
          modal: this.$el.attributes.modal.value,
        }
      });
    }
  });
}

if (document.getElementById('em-messages-coordinator-vue')) {
  new Vue({
    el: '#em-messages-coordinator-vue',
    render(h) {
      return h(MessagesCoordinator, {
        props: {
          fnum: this.$el.attributes.fnum.value,
          user: Number(this.$el.attributes.user.value),
        }
      });
    }
  });
}
