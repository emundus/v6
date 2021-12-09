const state = {
    fnums: []
};

const getters = {

};

const actions = {
    setFnumsInfos({ commit }, data) {
        commit('setFnumsInfos', data);
    }
};

const mutations = {
    setFnumsInfos(state, data) {
        state.fnums[data.fnum] = data.fnumInfos;
    }
};

export default{
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};