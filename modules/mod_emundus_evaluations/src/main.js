import Vue from 'vue';
import Vuex from 'vuex';
import App from './App.vue';
import translate from './mixins/translate';
import store from '../../../components/com_emundus/src/store/index.js';

import VModal from 'vue-js-modal';

Vue.config.productionTip = false;
Vue.config.devtools = false;

Vue.use(Vuex);
Vue.use(VModal);
Vue.mixin(translate);

const modEvaluation = document.getElementById('em-evaluations-vue');
if (modEvaluation) {
    const vue = new Vue({
        el: '#em-evaluations-vue',
        store,
        render(h) {
            return h(App, {
                props: {
                    currentUser: modEvaluation.getAttribute('user'),
                    module: modEvaluation.getAttribute('module'),
                    readonly: modEvaluation.getAttribute('readonly')
                }
            });
        }
    });
}
