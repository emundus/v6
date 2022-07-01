import Vue from 'vue';
import Vuex from 'vuex';
import App from './App.vue';
import translate from '../../../components/com_emundus/src/mixins/translate';
import store from '../../../components/com_emundus/src/store/index.js';

import VModal from 'vue-js-modal';

Vue.config.productionTip = false;
// Pass to true in local development
Vue.config.devtools = false;

Vue.use(Vuex);
Vue.use(VModal);
Vue.mixin(translate);

const modRsstSignalementList =  document.getElementById('em-rsst-signalement-list-vue');
if (modRsstSignalementList) {
  const vue = new Vue({
    el: '#em-rsst-signalement-list-vue',
    store,
    render(h) {
      return h(App, {
        props: {
          currentUser: modRsstSignalementList.getAttribute('user')
        }
      });
    }
  });
}
