const state = {
    lastSave: null,
    pages: null,
};

const getters = {
    getLastSave: state => state.lastSave,
    getPages: state => state.pages,
};

const actions = {
    updateLastSave({ commit }, payload) {
        commit('updateLastSave', payload);
    },
};

const mutations = {
    updateLastSave(state, payload) {
        state.lastSave = payload;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};