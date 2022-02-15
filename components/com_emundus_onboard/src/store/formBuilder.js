import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
			formulaire: {}
    },
    mutations: {
			addForm(state, data) {
				// create default formulaire object
				state.formulaire = {
					formObjectArray: [],
					submittionPages: [],
					formList: [],
					profileLabel: "",
					id: 0,
					grab: 0,
					grabDocs: 0,
					rgt: 0,
					builderKey: 0,
					builderSubmitKey: 0,
					files: 0,
					documentsList: [],
				};
			},
			editForm(state, data) {
				// get form object from form service
		
			}
    },
    actions: {
			initForm({ commit }, data) {
				switch (data.mode) {
					case 'edit':
						commit('editForm', data);	
					break;
					case 'add':
					default:
						commit('addForm', data);	
					break;
				}
			}
    },
    modules: {
      
    }
})