import Vue from 'vue';
import App from './App.vue';

Vue.config.productionTip = true;
Vue.config.devtools = true;

/** COMPONENTS **/
import VModal from 'vue-js-modal';
import { VTooltip, VPopover, VClosePopover } from 'v-tooltip';
import * as VueSpinnersCss from 'vue-spinners-css';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';
import { TableComponent, TableColumn } from 'vue-table-component';
import Notifications from 'vue-notification';
import velocity from 'velocity-animate';
import VWave from 'v-wave';

Vue.component('v-popover', VPopover);
Vue.component('table-component', TableComponent);
Vue.component('table-column', TableColumn);
Vue.use(Notifications, { velocity });

Vue.use(VModal);
Vue.use(VueSpinnersCss);
Vue.use(VWave);

/** STORE **/
import store from './store';

/** MIXINS **/
import translate from './mixins/translate.js';
Vue.mixin(translate);

/** DIRECTIVES **/
Vue.directive('tooltip', VTooltip);
Vue.directive('close-popover', VClosePopover);

let mountApp = false;
let elementId = '';
let data = {};
let componentName = '';

if (document.getElementById('em-application-attachment')) {
    const element = document.getElementById('em-application-attachment');
    Array.prototype.slice.call(element.attributes).forEach(function (attr) {
        data[attr.name] = attr.value;
    });

    componentName = 'attachments';
    elementId = '#em-application-attachment';
    mountApp = true;

    if (mountApp) {
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
}

if (document.getElementById('em-files')) {
    const element = document.getElementById('em-files');
    Array.prototype.slice.call(element.attributes).forEach(function (attr) {
        data[attr.name] = attr.value;
    });

    componentName = 'files';
    elementId = '#em-files';
    mountApp = true;

    if (mountApp) {
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
}

if (document.getElementById("em-component-vue")) {
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
