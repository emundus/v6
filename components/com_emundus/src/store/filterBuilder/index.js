import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        filters: [],
        queryFilters: {
            relation: 'AND',
            groups: {
                0: {
                    filters: {},
                }
            } 
        },
    },
    mutations: {
        setFilters(state, filters) {
            state.filters = filters
        },
        updateQueryFilters(state, data) {
            if (!state.queryFilters.groups[data.group]) {
                state.queryFilters.groups[data.group] = {
                    filters: {}
                };
            }

            if(!state.queryFilters.groups[data.group].filters) {
                state.queryFilters.groups[data.group].filters = {};
            }


            state.queryFilters.groups[data.group].filters[data.id] = data.filter;
        },
        removeGroup(state, group) {
            delete state.queryFilters.groups[group];
        },
        removeQueryFilter(state, data) {
            delete state.queryFilters.groups[data.group].filters[data.id];
        }
    },
    actions: {
        setFilters({ commit }, filters) {
            commit('setFilters', filters)
        },
        updateQueryFilters({ commit }, data) {
            commit('updateQueryFilters', data)
        },
        removeGroup({ commit }, group) {
            commit('removeGroup', group)
        },
        removeQueryFilter({ commit }, data) {
            commit('removeQueryFilter', data)
        }
    },
    modules: {
        
    }
})