import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export const GlobalMutations = {
    /**
     *
     * @param state
     * @param datas
     * @returns Array
     */
    initDatas(state, datas) {
        state.datas = datas;
        return state.datas;
    },

    initCurrentLanguage(state, language) {
        state.actualLanguage = language;
        return state.actualLanguage;
    },

    initManyLanguages(state, result) {
        state.manyLanguages = result;
        return state.manyLanguages;
    },

    initCoordinatorAccess(state, access){
        state.coordinatorAccess = access;
        return state.coordinatorAccess;
    }
};

export const global = new Vuex.Store({
    state: {
        datas: [],
        actualLanguage: '',
        manyLanguages: '',
        coordinatorAccess: '',
    },

    getters: {
        datas: state => state.datas,
        actualLanguage: state => state.actualLanguage,
        manyLanguages: state => state.manyLanguages,
        coordinatorAccess: state => state.coordinatorAccess,
    },

    mutations: GlobalMutations,

    actions: {}
});
