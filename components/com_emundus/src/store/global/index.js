const state = {
    lang: ''
};

const getters = {

};

const actions = {
    setLang({ commit }, lang) {
        commit('setLang', lang);
    }
};

const mutations = {
    setLang(state, lang) {
        state.lang = lang;
    }
};

export default{
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};