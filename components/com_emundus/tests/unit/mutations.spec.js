import { expect } from 'chai';
import Vue from 'vue';

import { ListMutations } from '../../src/store/store';

const {
  selectItem, resetSelectedItemsList, listUpdate, listInsert, publish, unpublish, deleteSelected,
} = ListMutations;

describe('mutations', () => {
  const state = {
    selectedItems: [],
    list: [{
      id: 1,
    }],
  };
  it('Selected an Item', () => {
    selectItem(state, 1);
    selectItem(state, 2);
    selectItem(state, 3);
    expect(state.selectedItems).to.contain(1);
  });
  it('Deselect an item', () => {
    selectItem(state, 1);
    expect(state.selectedItems).to.not.contain(1);
  });
  it('Reset Selected list array', () => {
    resetSelectedItemsList(state);
    expect(state.selectedItems).to.have.lengthOf(0);
  });
  it('Update the list', () => {
    const list = [{ id: 1, published: 1 }, { id: 2, published: 0 }, { id: 3, published: 1 }];
    listUpdate(state, list);
    expect(state.list).to.have.lengthOf(3);
    expect(state.list[2].id).to.not.equal(9);
  });
  it('Update the list', () => {
    const list = [{ id: 4, published: 0 }, { id: 5, published: 0 }];
    listInsert(state, list);
    expect(state.list).to.have.lengthOf(5);
    expect(state.list[2].id).to.equal(3);
  });
  it('Publish an item in the list', () => {
    publish(state, [2, 5]);
    expect(state.list[1].published).to.equal(1);
    expect(state.list[4].published).to.not.equal(1);
  });
  it('Unpublish an item in the list', () => {
    unpublish(state, [1, 2]);
    expect(state.list[0].published).to.equal(0);
    expect(state.list[1].published).to.not.equal(1);
  });
  it('Delete an item in the list', () => {
    deleteSelected(state, [5]);
    expect(state.list).to.not.have.lengthOf(5);
  });
});
