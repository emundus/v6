const state = {
    lang: '',
    anonyme: false,
};

const getters = {

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
    setAnonyme(state, anonyme) {
        state.anonyme = anonyme;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};