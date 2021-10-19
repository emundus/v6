const state = {
    attachmentPath: "images/emundus/files/",
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
    setAttachmentsOfFnum({commit}, data) {
        commit('setAttachmentsOfFnum', data);
    },
    updateAttachmentOfFnum({commit}, data) {
        commit('updateAttachmentOfFnum', data);
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
    setAttachmentsOfFnum(state, data) {
        state.attachments[data.fnum] = data.attachments;
    },
    updateAttachmentOfFnum(state, data) {
        const attachmentIndex = state.attachments[data.fnum].findIndex(attachment => attachment.aid === data.attachment.aid);

        state.attachments[data.fnum][attachmentIndex] = data.attachment;
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