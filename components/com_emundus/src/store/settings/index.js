const state = {
    needSaving: false
};

const getters = {
    needSaving: state => state.needSaving
};

const actions = {
    setNeedSaving({ commit }, needSaving) {
        commit('setNeedSaving', needSaving);
    },
};

const mutations = {
    setNeedSaving({commit}, needSaving) {
        state.needSaving = needSaving;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
