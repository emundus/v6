import Vue from 'vue';
import Vuex from 'vuex';
import filterBuilder from './filterBuilder';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        filterBuilder
    }
});