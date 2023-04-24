const state = {
    datas: [],
    currentLanguage: '',
    shortLang: '',
    manyLanguages: '',
    defaultLang: '',
    coordinatorAccess: '',
    sysadminAccess: false,
    anonyme: false,
    offset: 0,
};

const getters = {
    datas: state => state.datas,
    currentLanguage: state => state.currentLanguage,
    shortLang: state => state.shortLang,
    manyLanguages: state => state.manyLanguages,
    defaultLang: state => state.defaultLang,
    coordinatorAccess: state => state.coordinatorAccess,
    sysadminAccess: state => state.sysadminAccess,
    offset: state => state.offset,
};

const actions = {
    setLang({ commit }, lang) {
        commit('setLang', lang);
    },
    setAnonyme({ commit }, anonyme) {
        commit('setAnonyme', anonyme);
    },
};

const mutations = {
    initDatas(state, datas) {
        state.datas = datas;
        return state.datas;
    },

    initCurrentLanguage(state, language) {
        state.currentLanguage = language;
        return state.currentLanguage;
    },

    initOffset(state, offset) {
        state.offset = offset;
        return state.offset;
    },

    initShortLang(state, language) {
        state.shortLang = language;
        return state.shortLang;
    },

    initManyLanguages(state, result) {
        state.manyLanguages = result;
        return state.manyLanguages;
    },

    initDefaultLang(state, lang) {
        state.defaultLang = lang;
        return state.defaultLang;
    },

    initCoordinatorAccess(state, access) {
        state.coordinatorAccess = access;
        return state.coordinatorAccess;
    },
    initSysadminAccess(state, access) {
        state.sysadminAccess = access;
        return state.sysadminAccess;
    },

    setAnonyme(state, anonyme) {
        state.anonyme = anonyme;
    }
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};
