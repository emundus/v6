import Vue from 'vue';
import VModal from 'vue-js-modal';
import store from "./store";
import App from './App.vue';
import translate from './mixins/translate.js';
// import filterBuilderStore from './store/filterBuilder';
// import FilterBuilder from './components/FilterBuilder/FilterBuilder.vue';

let mountApp = false;
let elementId = "";
let data = {};
let componentName = "";

if (document.getElementById("em-application-attachment")) {
    const element = document.getElementById("em-application-attachment");
    Array.prototype.slice.call(element.attributes).forEach(function(attr) {
        data[attr.name] = attr.value;
    });

    componentName = "attachments";
    elementId = "#em-application-attachment";
    mountApp = true;
}

if (mountApp) {
    Vue.config.productionTip = false;
    Vue.use(store);
    Vue.use(VModal);
    Vue.mixin(translate);

    new Vue({
        el: elementId,
        store,
        render(h) {
            return h(
                App, {
                    props: {
                        componentName: componentName,
                        data: data
                    },
                }
            );
        },
    });
}

// if (document.getElementById("em-vue-filter-builder")) {
//     const filterbuilderApp = new Vue({
//         el: '#em-vue-filter-builder',
//         store: filterBuilderStore,
//         render(h) {
//             return h(
//                 FilterBuilder
//             );
//         }
//     });
// }
