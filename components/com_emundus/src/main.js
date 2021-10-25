import Vue from 'vue';
import store from "./store";
import filterBuilderStore from './store/filterBuilder';
import router from "./router";
import i18n from "./i18n";
import VModal from 'vue-js-modal';
import App from './App.vue';
import FilterBuilder from './components/FilterBuilder/FilterBuilder.vue';

Vue.config.productionTip = false;

Vue.use(store);
Vue.use(VModal);

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
    const vm = new Vue({
        el: elementId,
        store,
        router,
        i18n,
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

    window.addEventListener('destroy-com-emundus-vue', () => {
        vm.$destroy();
    });
}

if (document.getElementById("em-vue-filter-builder")) {
    const filterbuilderApp = new Vue({
        el: '#em-vue-filter-builder',
        filterBuilderStore,
        i18n,
        render(h) {
            return h(
                FilterBuilder
            );
        }
    });
}
