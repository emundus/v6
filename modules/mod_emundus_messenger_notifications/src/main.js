import Vue from 'vue'
import Notifications from "@/Notifications"
import VueJsModal from 'vue-js-modal';

Vue.config.productionTip = false;
Vue.config.devtools = true;

Vue.use(VueJsModal);

if (document.getElementById('em-notifications')) {
  new Vue({
    el: '#em-notifications',
    render(h) {
      return h(Notifications, {
        props: {
          user: Number(this.$el.attributes.user.value),
          fnum: this.$el.attributes.fnum.value,
        }
      });
    }
  });
}
