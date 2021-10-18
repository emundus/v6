const state = {
    attachments: {},
    selectedAttachment: {},
    previews: {},
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
    setPreview({ commit }, previewData) {
        commit('setPreview', previewData);
    },
};

const mutations = {
    setAttachments(state, attachments) {
        state.attachments = attachments;
    },
    setSelectedAttachment(state, attachment) {
        state.selectedAttachment = attachment;
    },
    setPreview(state, previewData) {
        state.previews[previewData.id] = previewData.preview;
    },
};

export default{
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};