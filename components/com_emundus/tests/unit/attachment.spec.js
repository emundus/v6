import { shallowMount, createLocalVue } from '@vue/test-utils';
import mockAttachment from '../mocks/attachments.mock';
import Attachments from '@/views/Attachments.vue';
import Vuex from 'vuex';
import VModal from 'vue-js-modal';
import store from '@/store';
import translate from '../mocks/mixins/translate';

const localVue = createLocalVue();

localVue.use(Vuex);
localVue.mixin(translate);
localVue.use(VModal);

describe('Attachments.vue', () => {
  const wrapper = shallowMount(Attachments, {
    propsData: {
      user: "123",
      fnum: "2021061714501700000010000123"
    },
    store: store,
    localVue
  });

  // set attachments data
  wrapper.vm.users = mockAttachment.users;
  wrapper.vm.displayedUser = mockAttachment.users.find(user => user.id === "123");
  wrapper.vm.attachments = mockAttachment.attachments;
  wrapper.vm.fnums = mockAttachment.fnums;

  it('Expect table wrapper to exists if not empty attachments', () => {
    const table = wrapper.find('.table-wrapper');
    expect(table.exists()).toBe(true);
  });

  /**
   * displayed-user should match the user firstname and lastname
   */
  it('Expect p.name content to be the user firstname and lastname', () => {
    const name = wrapper.find('.displayed-user .name');
    expect(name.text()).toBe(mockAttachment.users[0].firstname + ' ' + mockAttachment.users[0].lastname);
  });

  /**
   * if more than one fnums, next button should be displayed
   */
  it('Expect navigateButtons to be displayed if more than one fnums', () => {
    const navigateButtons = wrapper.find('.prev-next-files');
    expect(navigateButtons.exists()).toBe(true);
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

  // ?? Test returns true but async warning ??
  // it('Expect sort to return to default values on refresh click', () => {
  //   const refresh = wrapper.find('.refresh');
  //   refresh.trigger('click');
  //   expect(wrapper.vm.sort.last).toBe('');
  //   expect(wrapper.vm.sort.orderBy).toBe('');
  //   expect(wrapper.vm.sort.order).toBe('');
  // })

  /**
   * test searchInFiles
   */
  it('Expect searchInFiles to set attachment.show to false if attachment.value do not contains value', () => {
    wrapper.vm.$refs.searchbar.value = wrapper.vm.attachments[0].value;
    wrapper.vm.searchInFiles();

    expect(wrapper.vm.attachments[0].show).toBe(true);
    expect(wrapper.vm.attachments[1].show).toBe(false);
  });

  it('Expect searchInFiles clear button to reset all attachments show value to true', () => {
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
    const deleteButton = wrapper.find('.material-icons.delete');
    expect(deleteButton.exists()).toBe(false);
  });

  /**
   * assert that export button is not displayed if canExport is false
   */
  it('Expect export button to not be displayed if canExport is false', () => {
    const exportButton = wrapper.find('.material-icons.export');
    expect(exportButton.exists()).toBe(false);
  });
});
