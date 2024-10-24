import Vue from 'vue';
import Vuex from 'vuex';
import App from './App.vue';
import store from '../../../components/com_emundus/src/store/index.js';
import './mixins';

import {VPopover} from 'v-tooltip';

Vue.config.productionTip = false;
// Pass to true in local development
Vue.config.devtools = true;

Vue.component('v-popover', VPopover);

Vue.use(Vuex);


const modRsstSignalementList = document.getElementById('em-rsst-signalement-list-vue');
if (modRsstSignalementList) {
    const vue = new Vue({
        el: '#em-rsst-signalement-list-vue',
        store,
        render(h) {

            return h(App, {
                props: {
                    currentUser: modRsstSignalementList.getAttribute('user'),
                    fabrikListId: modRsstSignalementList.getAttribute('listId'),
                    fabrikListActionColumn: modRsstSignalementList.getAttribute('listActionColumn'),
                    fabrikListParticularConditionalColumn: modRsstSignalementList.getAttribute('listParticularConditionalColumn'),
                    fabrikListParticularConditionalColumnValues: modRsstSignalementList.getAttribute('listParticularConditionalColumnValues'),
                    fabrikListColumnShowingAsBadge: modRsstSignalementList.getAttribute('listColumnShowingAsBadge'),
                    fabrikListColumnToNotShowingWhenFilteredBy: modRsstSignalementList.getAttribute('listColumnToNotShowingWhenFilteredBy'),
                    readOnly: modRsstSignalementList.getAttribute('readOnly')
                }
            });
        }
    });
}
