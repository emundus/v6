import { shallowMount, createLocalVue } from '@vue/test-utils';
import mockAttachment from '../../mocks/attachments.mock';
import Attachments from '../../../src/views/Attachments';
import Vuex from 'vuex';
import VModal from 'vue-js-modal';
import store from '../../../src/store';
import translate from '../../mocks/mixins/translate';

const localVue = createLocalVue();

localVue.use(Vuex);
localVue.mixin(translate);
localVue.use(VModal);

describe('Attachments.vue', () => {
  const wrapper = shallowMount(Attachments, {
    propsData: {user: '123', fnum: '2021061714501700000010000123'},
    store: store,
    localVue
  });

  it('displayedFnum data should be equal to fnum props', () => {
    expect(wrapper.vm.displayedFnum).toBe(wrapper.props().fnum);
  });

  // set attachments data
  wrapper.vm.users = mockAttachment.users;
  wrapper.vm.displayedUser = mockAttachment.users.find(user => user.id === '123');
  wrapper.vm.attachments = mockAttachment.attachments;
  wrapper.vm.fnums = mockAttachment.fnums;

  it('Expect table wrapper to exists if not empty attachments', () => {
    const table = wrapper.find('.table-wrapper');
    expect(table.exists()).toBe(true);
  });

  /**
   * test sort elements
   */
  it('By default sort last should be empty', () => {
    expect(wrapper.vm.sort.last).toBe('');
  });

  it('Expect sort to change on click on table th', () => {
    const table = wrapper.find('.table-wrapper');
    table.find('th.date').trigger('click');
    expect(wrapper.vm.sort.last).toBe('timedate');
    expect(wrapper.vm.sort.orderBy).toBe('timedate');
    expect(wrapper.vm.sort.order).toBe('asc');
  });

  it('Expect sort order to change on click on same th', () => {
    const table = wrapper.find('.table-wrapper');
    table.find('th.date').trigger('click');
    expect(wrapper.vm.sort.order).toBe('desc');
  });

  it('Expect sort to return to default values on refresh click', () => {
    const refresh = wrapper.find('.refresh');
    refresh.trigger('click');
    expect(wrapper.vm.sort.last).toBe('');
    expect(wrapper.vm.sort.orderBy).toBe('');
    expect(wrapper.vm.sort.order).toBe('');
  });

  /**
   * test search
   */
  it('Expect displayedAttachments length to adapt to search value', () => {
    // values are 'Courrier' and 'ZIP'
    wrapper.vm.$data.search = wrapper.vm.attachments[0].value;
    expect(wrapper.vm.displayedAttachments.length).toBe(1);

    wrapper.vm.$data.search = 'test';
    expect(wrapper.vm.displayedAttachments.length).toBe(0);

    wrapper.vm.$data.search = '';
    expect(wrapper.vm.displayedAttachments.length).toBe(wrapper.vm.attachments.length);

    wrapper.vm.$data.search = 'i';
    expect(wrapper.vm.displayedAttachments.length).toBe(2);

    wrapper.vm.$data.search = 'Cour';
    expect(wrapper.vm.displayedAttachments.length).toBe(1);
  });

  it('Expect search clear button to reset all attachments show value to true', () => {
    const clearSearch = wrapper.find('.searchbar-wrapper .clear');
    clearSearch.trigger('click');

    wrapper.vm.attachments.forEach(element => {
      expect(element.show).toBe(true);
    });
  });

  /**
   * assert that delete button is not displayed if canDelete is false
   */
  it('Expect delete button to not be displayed if canDelete is false', () => {
    const deleteButton = wrapper.find('.material-icons-outlined.delete');
    expect(deleteButton.exists()).toBe(false);
  });

  /**
   * assert that export button is not displayed if canExport is false
   */
  it('Expect export button to not be displayed if canExport is false', () => {
    const exportButton = wrapper.find('.material-icons-outlined.export');
    expect(exportButton.exists()).toBe(false);
  });

  /**
   * category select should be displayed if more than one category
   */
  wrapper.vm.$data.categories = {
    '1': 'category1',
    '2': 'category2',
    '3': 'category3',
    '4': 'category4'
  };
  it('Expect category select to be displayed if there are category option available', () => {
    const categorySelect = wrapper.find('.category-select');
    expect(categorySelect.exists()).toBe(true);
  });

  /**
   * Only attachments with category selected should be displayed
   * If no category selected, all attachments should be displayed
   * If option value equals all, all attachments should be displayed
   */
  it('Expect filterByCategory to show only attachments with selected category', () => {
    wrapper.vm.$data.category = '2';

    wrapper.vm.displayedAttachments.forEach(element => {
        expect(element.category).toBe('2');
    });
  });

  it('Local storage should be set with category value', () => {
    expect(localStorage.getItem('vue-attachment-category')).toBe('2');
  });

  it('Expect category to show all attachments if category value is all', () => {
    wrapper.vm.$data.category = 'all';

    expect(wrapper.vm.displayedAttachments.length).toBe(wrapper.vm.attachments.length);
  });

  it('Local storage should be set with category value', () => {
    expect(localStorage.getItem('vue-attachment-category')).toBe(null);
  });

  it('Expect .category-select to display only category options that are in attachments', () => {
    // Get different categories from attachments
    let uniqCategories = [];
    wrapper.vm.attachments.forEach(element => {
      if (!uniqCategories.includes(element.category)) {
        uniqCategories.push(element.category);
      }
    });

    const categorySelect = wrapper.find('.category-select');
    const options = categorySelect.findAll('option');
    expect(options.length).toBe(uniqCategories.length + 1);
  });
});

describe('Attachments.vue empty categories', () => {
  const wrapper = shallowMount(Attachments, {
    propsData: {user: '123', fnum: '2021061714501700000010000123'},
    store: store,
    localVue
  });

  wrapper.vm.$data.categories = {};
  it('Expect category select to be hidden if there are no category option available', () => {
    expect(wrapper.vm.$data.categories).toEqual({});
    expect(wrapper.vm.displayedAttachmentCategories).toEqual({});
    const categorySelect = wrapper.find('.category-select');
    expect(categorySelect.exists()).toBe(false);
  });
});

describe('Attachments.vue delete Methods', () => {
  const wrapper = shallowMount(Attachments, {
    propsData: {user: '123', fnum: '2021061714501700000010000123'},
    store: store,
    localVue
  });

  // set attachments data
  wrapper.vm.users = mockAttachment.users;
  wrapper.vm.displayedUser = mockAttachment.users.find(user => user.id === '123');
  wrapper.vm.attachments = mockAttachment.attachments;
  wrapper.vm.fnums = mockAttachment.fnums;
  wrapper.vm.canDelete = true;

  it('Expect delete button to be displayed if canDelete is true', () => {
    const deleteButton = wrapper.find('.material-icons-outlined.delete');
    expect(deleteButton.exists()).toBe(true);
  });

  // click on .material-icons-outlined.delete calls confirmDeleteAttachments
  wrapper.vm.checkedAttachments = [wrapper.vm.attachments[0]['aid']];
  const confirmDeleteAttachments = jest.spyOn(wrapper.vm, 'confirmDeleteAttachments');
  it('Expect confirmDeleteAttachments to be called on click on .material-icons-outlined.delete', () => {
    const deleteButton = wrapper.find('.material-icons-outlined.delete');
    deleteButton.trigger('click');
    expect(confirmDeleteAttachments).toHaveBeenCalled();
  });

  it('first click on .delete should not delete the attachment from the list', () => {
    const deleteButton = wrapper.find('.material-icons-outlined.delete');
    deleteButton.trigger('click');
    expect(wrapper.vm.attachments.length).toBe(2);
  });

  it('deleteAttachments should remove checkedAttachments from attachments', () => {
    wrapper.vm.deleteAttachments();
    expect(wrapper.vm.attachments.length).toBe(1);
  });
});

describe('Attachments.vue anonyme', () => {
  const wrapper = shallowMount(Attachments, {
    propsData: {
      user: '123',
      fnum: '2021061714501700000010000123'
    },
    store: store,
    localVue
  });

  // set attachments data
  wrapper.vm.users = mockAttachment.users;
  wrapper.vm.displayedUser = mockAttachment.users.find(user => user.id === '123');
  wrapper.vm.attachments = mockAttachment.attachments;
  wrapper.vm.fnums = mockAttachment.fnums;

  it('store global anonyme value should exists', () => {
    expect(store.state.global).toHaveProperty('anonyme');
  });

  it('if anonyme store value changes, canSee should be updated', () => {
    const newValue = !store.state.global.anonyme;
    store.dispatch('global/setAnonyme', newValue).then(() => {
      expect(wrapper.vm.canSee).toBe(!newValue);
    });
  });


  it('Expect .name to display fnum if anonyme to true', () => {
    store.dispatch('global/setAnonyme', true).then(() => {
      const name = wrapper.find('.displayed-user .name');
      expect(name.text()).toBe(wrapper.vm.displayedFnum);
    });
  });
});

describe('Attachments.vue sync', () => {
  const wrapper = shallowMount(Attachments, {
    propsData: {user: '123', fnum: '2021061714501700000010000123'},
    store: store,
    localVue
  });

  // sync should be set to false by default
  it('sync should be set to false by default', () => {
    expect(wrapper.vm.$data.sync).toBe(false);
  });

  // synchronizeAttachments should always return false if sync is false
  it('synchronizeAttachments should always return false if sync is false', () => {
    expect(wrapper.vm.synchronizeAttachments()).toBe(false);
  });

});

describe('Attachments.vue attachments with wrong data', () => {
  const wrapper = shallowMount(Attachments, {
    propsData: {user: '123', fnum: '2021061714501700000010000123'},
    store: store,
    localVue
  });

  mockAttachment.attachments[0].upload_description = null;

  // set attachments data
  wrapper.vm.users = mockAttachment.users;
  wrapper.vm.displayedUser = mockAttachment.users.find(user => user.id === '123');
  wrapper.vm.attachments = mockAttachment.attachments;
  wrapper.vm.fnums = mockAttachment.fnums;

  it('No error should occur even if upload_description is null', () => {
    const table = wrapper.find('.table-wrapper');
    expect(table.exists()).toBe(true);
  });
});