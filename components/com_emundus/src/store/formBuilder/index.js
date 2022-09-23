const state = {
    lastSave: null,
    pages: null,
    documentModels: [],
};

const getters = {
    getLastSave: state => state.lastSave,
    getPages: state => state.pages,
    getDocumentModels: state => state.documentModels,
};

const actions = {
    updateLastSave({ commit }, payload) {
        commit('updateLastSave', payload);
    },
    updateDocumentModels({ commit }, payload) {
        commit('updateDocumentModels', payload);
    },
};

const mutations = {
    updateLastSave(state, payload) {
        state.lastSave = payload;
    },
    updateDocumentModels(state, payload) {
        state.documentModels = payload;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};