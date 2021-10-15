const state = {
    attachments: {},
    selectedAttachment: {},
};

const getters = {

};

const actions = {
    setAttachments({ commit }, attachments) {
        commit('setAttachments', attachments);
    },
    setSelectedAttachment({ commit }, attachment) {
        commit('setSelectedAttachment', attachment);
    },
};

const mutations = {
    setAttachments(state, attachments) {
        state.attachments = attachments;
    },
    setSelectedAttachment(state, attachment) {
        state.selectedAttachment = attachment;
    },
};

export default{
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};