import { createApp } from 'vue';
import App from './App.vue';

/** COMPONENTS **/
import { vfmPlugin } from 'vue-final-modal';

import {
    // Directives
    VTooltip,
    VClosePopper,
    // Components
    Dropdown,
    Tooltip,
    Menu
} from 'floating-vue';
import * as VueSpinnersCss from 'vue-spinners-css';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';
import { TableComponent, TableColumn } from 'vue-table-component';
import VWave from 'v-wave';

/** MIXINS **/
import translate from './mixins/translate.js';

/** STORE **/
import store from './store';


let mountApp = false;
let elementId = '';
let data = {};
let props = {};
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
        props = {
            component: componentName,
            data: data
        };
    }
}

if (document.getElementById("em-component-vue")) {
    const element = document.getElementById('em-component-vue');
    Array.prototype.slice.call(element.attributes).forEach(function (attr) {
        data[attr.name] = attr.value;
    });

    props = {
        component: data.component,
        datas: data,
        currentLanguage: data.currentLanguage,
        shortLang: data.shortlang,
        manyLanguages: data.manylanguages,
        defaultLang: data.defaultlang ? data.defaultlang : data.currentlanguage,
        coordinatorAccess: data.coordinatoraccess,
        sysadminAccess: data.sysadminaccess,
    };
    elementId = '#em-component-vue';

}

const app = createApp(App,props);

app.directive('tooltip', VTooltip);
app.directive('close-popper', VClosePopper);

app.component('table-component', TableComponent);
app.component('table-column', TableColumn);
app.component('VDropdown', Dropdown);
app.component('VTooltip', Tooltip);
app.component('VMenu', Menu);

app.use(vfmPlugin);
app.use(VueSpinnersCss);
app.use(VWave);
app.use(store);
app.use(Dropdown);

/** MIXINS **/
app.mixin(translate);

app.mount(elementId);
