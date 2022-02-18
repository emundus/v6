import Vue from 'vue';
Vue.config.productionTip = false;
Vue.config.devtools = true;

import VueJsModal from 'vue-js-modal';
import { VTooltip, VPopover, VClosePopover } from 'v-tooltip';
import * as VueSpinnersCss from 'vue-spinners-css';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';
import { TableComponent, TableColumn } from 'vue-table-component';
import Notifications from 'vue-notification';
import velocity from 'velocity-animate';

/** Import Mixins **/
import mixin_errors from '../../../media/com_emundus/js/mixins/errors.js';
//import translate from '../../com_emundus/src/mixins/translate';

Vue.directive('tooltip', VTooltip);
Vue.directive('close-popover', VClosePopover);
Vue.component('v-popover', VPopover);
Vue.component('table-component', TableComponent);
Vue.component('table-column', TableColumn);

Vue.use(Notifications, { velocity });
Vue.use(VueJsModal);
Vue.use(VueSpinnersCss);

/** Use Mixins **/
Vue.mixin(mixin_errors);
//Vue.mixin(translate);

import App from "./App";


// TODO: use mixin for translate
// see https://emundus.atlassian.net/wiki/spaces/EKB/pages/2016903204/Mixins
// or go watch inside com_emundus/src/main.js and see how it works

new Vue({
  el: '#em-component-vue',
  render(h) {
    return h(App, {
      props: {
        component: this.$el.attributes.component.value,
        datas: this.$el.attributes,
        actualLanguage: this.$el.attributes.actualLanguage.value,
        manyLanguages: this.$el.attributes.manyLanguages.value,
        coordinatorAccess: this.$el.attributes.coordinatorAccess.value,
      }
    });
  }
});
