const state = {
    users: {}
};

const getters = {

};

const actions = {
    setUser({ commit }, user) {
        commit('setUser', user);
    }
};

const mutations = {
    setUser(state, user) {
        state.users[user.id] = user;
    }
};

export default{
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};