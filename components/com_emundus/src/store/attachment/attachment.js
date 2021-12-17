const state = {
    attachmentPath: "images/emundus/files/",
    attachments: {},
    selectedAttachment: {},
    previews: {},
    categories: {},
    checkedAttachments: [],
};

const getters = {

};

const actions = {
    setAttachments({ commit }, attachments) {
        commit('setAttachments', attachments);
    },
    setAttachmentsOfFnum({ commit }, data) {
        commit('setAttachmentsOfFnum', data);
    },
    updateAttachmentOfFnum({ commit }, data) {
        commit('updateAttachmentOfFnum', data);
    },
    setSelectedAttachment({ commit }, attachment) {
        commit('setSelectedAttachment', attachment);
    },
    setPreview({ commit }, previewData) {
        commit('setPreview', previewData);
    },
    setCategories({ commit }, categories) {
        commit('setCategories', categories);
    },
    setAttachmentPath({ commit }, path) {
        commit('setAttachmentPath', path);
    },
    setCheckedAttachments({ commit }, attachments) {
        commit('setCheckedAttachments', attachments);
    }
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
    setCategories(state, categories) {
        state.categories = categories;
    },
    setAttachmentPath(state, path) {
        state.attachmentPath = path;
    },
    setCheckedAttachments(state, attachments) {
        state.checkedAttachments = attachments;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};