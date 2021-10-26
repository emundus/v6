import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        filters: [],
        queryFilters: {
            relation: 'AND',
            filters: []
        },
    },
    mutations: {
        setFilters(state, filters) {
            state.filters = filters
        }
    },
    actions: {
        setFilters({ commit }, filters) {
            commit('setFilters', filters)
        }
    },
    modules: {
        
    }
})