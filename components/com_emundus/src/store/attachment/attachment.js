const state = {
    attachments: {}
};

const getters = {

};

const actions = {
    setAttachments({ commit }, attachments) {
        commit('setAttachments', attachments);
    }
};

const mutations = {
    setAttachments(state, attachments) {
        state.attachments = attachments;
    }
};

export default{
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};