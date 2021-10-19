import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export const GlobalMutations = {
    /**
     *
     * @param state
     * @param id
     * @returns Array
     */
    initDatas(state, datas) {
        state.datas = datas;
        return state.datas;
    },
}

export const global = new Vuex.Store({
    state: {
        datas: [],
        actualLanguage: '',
        manyLanguages: ''
    },

    getters: {
        datas: state => state.datas,
        actualLanguage: state => state.actualLanguage,
        manyLanguages: state => state.manyLanguages,
    },

    mutations: GlobalMutations,

    actions: {}
});
