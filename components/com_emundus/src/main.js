import Vue from 'vue';
import Attachments from './components/Attachments.vue';

Vue.config.productionTip = false;

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
