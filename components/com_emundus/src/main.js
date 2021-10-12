import Vue from 'vue'
import Attachments from './components/Attachments.vue'

Vue.config.productionTip = false
console.log('test');
// new Vue({
//   render: h => h(Attachments, {
//     props: {
//       fnum: this.$el.attributes.fnum.value,
//       user: this.$el.attributes.user.value,
//     }
//   }),
// }).$mount('#em-application-attachment')

if (document.getElementById('em-application-attachment')) {
  new Vue({
    el: '#em-application-attachment',
    render(h) {
      return h(Attachments, {
        props: {
          fnum: this.$el.attributes.fnum.value,
          user: this.$el.attributes.user.value,
        }
      });
    }
  });
}
