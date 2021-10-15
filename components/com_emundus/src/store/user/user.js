const state = {
    users: {},
    currentUser: {},
};

const getters = {

};

const actions = {
    setUsers({ commit }, users) {
        commit('setUser', users);
    },
    setCurrentUser({ commit }, user) {
        commit('setCurrentUser', user);
    },
};

const mutations = {
    setUser(state, users) {
        users.forEach(user => {
            state.users[user.id] = user;
        });
    },
    setCurrentUser(state, user) {
        state.currentUser = user;
    },
};

export default{
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};