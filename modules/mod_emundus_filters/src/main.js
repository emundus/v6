import { createApp } from 'vue';
import Vuex from 'vuex';
import App from './App.vue';
import translate from './mixins/translate';


const modFilters =  document.getElementById('em-filters-vue');
if (modFilters) {
    const app = createApp(App, {
        moduleId: modFilters.getAttribute('data-module-id'),
    }).use(Vuex).mixin(translate);

    app.mount('#em-filters-vue');
}
