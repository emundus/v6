const state = {
    users: {},
    currentUser: 0,
    displayedUser: 0,
    rights: {},
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
    setAccessRights({ commit }, data) {
        commit('setAccessRights', data);
    }
};

const mutations = {
    setUser(state, users) {
        if (users && users.length > 0) {
            users.forEach(user => {
                state.users[user.user_id] = user;
            });
        }
    },
    setCurrentUser(state, user) {
        state.currentUser = user;
    },
    setDisplayedUser(state, user) {
        state.displayedUser = user;
    },
    setAccessRights(state, data) {
        state.rights[data.fnum] = data.rights;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};