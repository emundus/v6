import Vue from "vue";
import Vuex from "vuex";

Vue.use(Vuex);

export const ListMutations = {
  /**
   *
   * @param state
   * @param id
   * @returns Array
   */
  selectItem(state, id) {
    if (state.selectedItems.includes(id)) {
      state.selectedItems.splice(state.selectedItems.indexOf(id), 1);
    } else {
      state.selectedItems.push(id);
    }
    return state.selectedItems;
  },

  /**
   * empties the selectedItems list
   * @param state
   */
  resetSelectedItemsList(state) {
    state.selectedItems = [];
  },

  /**
   * Updates list with the one it receives
   * @param state
   * @param insert
   */
  listUpdate(state, insert) {
    state.list = insert;
  },

  /**
   * Insert new data into list
   * @param state
   * @param insert
   */
  listInsert(state, insert) {
    insert.forEach(item => {
      state.list.push(item);
    });
  },

  /**
   * publishes a list of items in the list array
   * @param state
   * @param id Object
   */
  publish(state, id) {
    state.list.forEach((item, index) => {
      if (id.includes(item.id)) {
        state.list[index].published = 1;
      }
    });
  },

  /**
   * unpublishes a list of items in the list array
   * @param state
   * @param id Object
   */
  unpublish(state, id) {
    state.list.forEach((item, index) => {
      if (id.includes(item.id)) {
        state.list[index].published = 0;
      }
    });
  },

  /**
   * deletes a list of items in the list array
   * @param state
   * @param id Object
   */
  deleteSelected(state, id) {
    state.list.some((item, index) => {
      if (id == item.id) {
        state.list.splice(index, 1);
        return true;
      }
    });
  }
};

export const list = new Vuex.Store({
  state: {
    list: [],
    selectedItems: []
  },

  getters: {
    list: state => state.list,
    selectedItems: state => state.selectedItems,
    isSelected: state => id => state.selectedItems.includes(id),
    isSomething: state => Array.isArray(state.selectedItems) && state.selectedItems.length
  },

  mutations: ListMutations,

  actions: {
    deleteSelected({ commit, state }, ids) {
      ids.forEach(id => {
        commit("deleteSelected", id);
      });
    }
  }
});
