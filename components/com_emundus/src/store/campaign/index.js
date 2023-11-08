const state = {
    unsavedChanges: false,
    allowPinnedCampaign: false,
    pinned: 0
};

const getters = {
    unsavedChanges: state => state.unsavedChanges,
    pinned: state => state.pinned,
    allowPinnedCampaign: state => state.allowPinnedCampaign,
};

const actions = {
    setUnsavedChanges({commit}, value) {
        commit('setUnsavedChanges', value);
    },
    setPinned({commit}, value) {
        commit('setPinned', value);
    },
    setAllowPinnedCampaign({commit}, value) {
        commit('setAllowPinnedCampaign', value);
    },
};

const mutations = {
    setUnsavedChanges(state, value) {
        state.unsavedChanges = value;
    },
    setPinned(state, value) {
        state.pinned = value;
    },
    setAllowPinnedCampaign(state, value) {
        state.allowPinnedCampaign = value;
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
