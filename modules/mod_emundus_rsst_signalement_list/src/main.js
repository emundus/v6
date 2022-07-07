import Vue from 'vue';
import Vuex from 'vuex';
import App from './App.vue';
import translate from '../../../components/com_emundus/src/mixins/translate';
import store from '../../../components/com_emundus/src/store/index.js';
import './mixins';

import { VPopover} from 'v-tooltip';

Vue.config.productionTip = false;
// Pass to true in local development
Vue.config.devtools = true;

Vue.component('v-popover', VPopover);

Vue.use(Vuex);

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
