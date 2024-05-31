import Vue from 'vue';
import App from './App.vue';

Vue.config.productionTip = false;
Vue.config.devtools = false;

/** COMPONENTS **/
import VModal from 'vue-js-modal';
import { VTooltip, VPopover, VClosePopover } from 'v-tooltip';
import * as VueSpinnersCss from 'vue-spinners-css';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';
import { TableComponent, TableColumn } from 'vue-table-component';
import Notifications from 'vue-notification';
import velocity from 'velocity-animate';
import VWave from 'v-wave';
import Vuex from 'vuex';

/** STORE **/
import store from './store';

/** MIXINS **/
import translate from './mixins/translate.js';

Vue.component('v-popover', VPopover);
Vue.component('table-component', TableComponent);
Vue.component('table-column', TableColumn);
Vue.use(Notifications, { velocity });

Vue.use(VModal);
Vue.use(VueSpinnersCss);
Vue.use(VWave);
Vue.use(Vuex);

Vue.mixin(translate);

/** DIRECTIVES **/
import clickOutside from './directives/clickOutside';
Vue.directive('tooltip', VTooltip);
Vue.directive('close-popover', VClosePopover);
Vue.directive('click-outside', clickOutside);

let elementId = '';
let data = {};
let componentName = '';

const attachmentElement = document.getElementById('em-application-attachment');
const filesElement = document.getElementById('em-files');

if (attachmentElement || filesElement) {
    let element = null;

    if (attachmentElement) {
        element = attachmentElement;
        componentName = 'attachments';
        elementId = '#em-application-attachment';
    } else if (filesElement) {
        element = filesElement;
        componentName = 'files';
        elementId = '#em-files';
    }

    if (element !== null) {
        Array.prototype.slice.call(element.attributes).forEach(function (attr) {
            data[attr.name] = attr.value;
        });

        if(data.fnum !== '' && filesElement)
        {
            componentName = 'application';
        }

        new Vue({
            el: elementId,
            store,
            render(h) {
                return h(App, {
                    props: {
                        component: componentName,
                        data: data
                    },
                });
            },
        });
    }
} else if (document.getElementById('em-component-vue')) {
    new Vue({
        el: '#em-component-vue',
        store,
        render(h) {
            return h(App, {
                props: {
                    component: this.$el.attributes.component.value,
                    datas: this.$el.attributes,
                    currentLanguage: this.$el.attributes.currentLanguage.value,
                    shortLang: this.$el.attributes.shortLang.value,
                    manyLanguages: this.$el.attributes.manyLanguages.value,
                    defaultLang: this.$el.attributes.defaultLang ? this.$el.attributes.defaultLang.value : this.$el.attributes.currentLanguage.value,
                    coordinatorAccess: this.$el.attributes.coordinatorAccess.value,
                    sysadminAccess: this.$el.attributes.sysadminAccess.value,
                }
            });
        }
    });
}
