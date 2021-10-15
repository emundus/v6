const state = {
    users: {},
    currentUser: 0,
    displayedUser: 0,
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
    setDisplayedUser({ commit }, user) {
        commit('setDisplayedUser', user);
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
    setDisplayedUser(state, user) {
        state.displayedUser = user;
    },
};

export default{
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};