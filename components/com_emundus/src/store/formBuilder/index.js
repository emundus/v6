const state = {
    lastSave: null,
    pages: null,
    documentModels: [],
    rulesKeywords: ''
};

const getters = {
    getLastSave: state => state.lastSave,
    getPages: state => state.pages,
    getDocumentModels: state => state.documentModels,
    getRulesKeywords: state => state.rulesKeywords
};

const actions = {
    updateLastSave({ commit }, payload) {
        commit('updateLastSave', payload);
    },
    updateDocumentModels({ commit }, payload) {
        commit('updateDocumentModels', payload);
    },
    updateRulesKeywords({ commit }, payload) {
        commit('updateRulesKeywords', payload);
    },
};

const mutations = {
    updateLastSave(state, payload) {
        state.lastSave = payload;
    },
    updateDocumentModels(state, payload) {
        state.documentModels = payload;
    },
    updateRulesKeywords(state, payload) {
        state.rulesKeywords = payload;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};