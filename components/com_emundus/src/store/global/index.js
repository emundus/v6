const state = {
    lang: '',
    datas: [],
    actualLanguage: '',
    manyLanguages: '',
    coordinatorAccess: '',
    anonyme: false,
};

const getters = {
    datas: state => state.datas,
    actualLanguage: state => state.actualLanguage,
    manyLanguages: state => state.manyLanguages,
    coordinatorAccess: state => state.coordinatorAccess,
};

const actions = {
    setLang({ commit }, lang) {
        commit('setLang', lang);
    },
    setAnonyme({ commit }, anonyme) {
        commit('setAnonyme', anonyme);
    },
};

const mutations = {
    setLang(state, lang) {
        state.lang = lang;
    },

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
    },

    setAnonyme(state, anonyme) {
        state.anonyme = anonyme;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
