const state = {
    unsavedChanges: false,
};

const getters = {
    unsavedChanges: state => state.unsavedChanges,
};

const actions = {
    setUnsavedChanges({ commit }, value) {
        commit('setUnsavedChanges', value);
    },
};

const mutations = {
    setUnsavedChanges(state, value) {
        state.unsavedChanges = value;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};