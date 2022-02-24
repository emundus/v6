import Vue from 'vue';
import App from './App.vue';

Vue.config.productionTip = false;

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

/** MIXINS **/
import translate from './mixins/translate.js';
Vue.mixin(translate);

/** STORE **/
import store from "./store";

/** DIRECTIVES **/
Vue.directive('tooltip', VTooltip);
Vue.directive('close-popover', VClosePopover);


let mountApp = false;
let elementId = "";
let data = {};
let componentName = "";

if (document.getElementById("em-application-attachment")) {
    const element = document.getElementById("em-application-attachment");
    Array.prototype.slice.call(element.attributes).forEach(function (attr) {
        data[attr.name] = attr.value;
    });

    componentName = "attachments";
    elementId = "#em-application-attachment";
    mountApp = true;

    if (mountApp) {
        const items = document.querySelectorAll('#em-appli-menu .list-group-item');

        // add eventlistener on changeFile
        function changeFile(e) {
            document.querySelector('#em-assoc-files .panel-body').empty();
            const checkedEm = document.querySelector('.em-check:checked');
            // uncheck element
            if (checkedEm) {
                checkedEm.checked = false;
            }

            // check element that have id equals to e.detail.fnum.fnum + "_check"
            const check = document.getElementById(e.detail.fnum.fnum + "_check");
            if (check) {
                check.checked = true;
            }

            // update href fnum param
            items.forEach(function (item) {
                item.setAttribute('href', item.getAttribute('href').replace(/fnum=\d+/, 'fnum=' + e.detail.fnum.fnum));
            });


            openFiles(e.detail.fnum, "attachment", true);
        }


        const vue = new Vue({
            el: elementId,
            store,
            render(h) {
                return h(
                    App, {
                        props: {
                            componentName: componentName,
                            data: data
                        },
                    });
            },
        });

        document.querySelector(".com_emundus_vue").addEventListener('changeFile', changeFile);
    }
}

console.log('here');

if (document.getElementById("em-component-vue")) {
    const vue = new Vue({
        el: '#em-component-vue',
        store,
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
}
