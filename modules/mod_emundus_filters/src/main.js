import { createApp } from 'vue';
import Vuex from 'vuex';
import App from './App.vue';
import translate from './mixins/translate';


const modFilters =  document.getElementById('em-filters-vue');
if (modFilters) {
    const appliedFilters = JSON.parse(atob(modFilters.getAttribute('data-applied-filters')));
    const filters = JSON.parse(atob(modFilters.getAttribute('data-filters')));

    const app = createApp(App, {
        moduleId: parseInt(modFilters.getAttribute('data-module-id')),
        defaultAppliedFilters: appliedFilters,
        defaultFilters: filters,
        defaultQuickSearchFilters: JSON.parse(atob(modFilters.getAttribute('data-quick-search-filters'))),
        countFilterValues: modFilters.getAttribute('data-count-filter-values') === '1',
        allowAddFilter: modFilters.getAttribute('data-allow-add-filter') === '1',
    }).use(Vuex).mixin(translate);

    app.config.productionTip = true;
    app.config.devtools = true;
    app.mount('#em-filters-vue');
}
