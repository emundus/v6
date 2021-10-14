import Vue from 'vue';
import Attachments from './components/Attachments.vue';
import VModal from 'vue-js-modal';

Vue.config.productionTip = false;
Vue.use(VModal);

if (document.getElementById("em-application-attachment")) {
  new Vue({
    render(h) {
      return h(Attachments, {
        props: {
          fnum: this.$el.attributes.fnum.value,
          user: this.$el.attributes.user.value,
        },
      });
    },
  }).$mount('#em-application-attachment');  
}