import { shallowMount, createLocalVue } from '@vue/test-utils';
import AttachmentEdit from '../../../../src/components/Attachments/AttachmentEdit';
import translate from '../../../mocks/mixins/translate';
import mockAttachment from '../../../mocks/attachments.mock';
import store from '../../../../src/store';
import mixin from '../../../../src/mixins/mixin';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.mixin(mixin);
localVue.use(VModal);

describe('AttachmentEdit.vue', () => {
    beforeAll(() => {
        store.commit('attachment/setSelectedAttachment', mockAttachment.attachments[0]);
        store.commit('global/setAnonyme', false);

        let rights = {};
        rights[mockAttachment.fnums[0]] = {
            canSee: true,
            canUpdate: true,
            canDelete: true,
        };
        store.commit('user/setAccessRights', {fnum: mockAttachment.fnums[0], rights: rights});
    });

    const wrapper = shallowMount(AttachmentEdit, {
        propsData: {
            fnum: mockAttachment.fnums[0],
            isDisplayed: true
        },
        localVue,
        store
    });

    it ('AttachmentEdit should exist', () => {
        expect(wrapper.find('#attachment-edit').exists()).toBeTruthy();
        expect(wrapper.vm.$data.attachment).toEqual(mockAttachment.attachments[0]);
    });

    it('AttachmentEdit should display title', () => {
        expect(wrapper.find('.title').exists()).toBeTruthy();
        expect(wrapper.find('.title').text()).toBe(mockAttachment.attachments[0].value);
    });

    it('Attachment edit, can_be_wiewed and can_be_deleted inputs should exists', () => {
        expect(wrapper.find('input[name="can_be_viewed"]').exists()).toBeTruthy();
        expect(wrapper.find('input[name="can_be_deleted"]').exists()).toBeTruthy();
    });
});

describe('AttachmentEdit.vue, attachment with empty profiles', () => {
    let attachment = mockAttachment.attachments[1];
    attachment.profiles = [];

    beforeAll(() => {
        store.commit('attachment/setSelectedAttachment', attachment);
        store.commit('global/setAnonyme', false);

        let rights = {};
        rights[mockAttachment.fnums[0]] = {
            canSee: true,
            canUpdate: true,
            canDelete: true,
        };
        store.commit('user/setAccessRights', {fnum: mockAttachment.fnums[0], rights: rights});
    });

    const wrapper = shallowMount(AttachmentEdit, {
        propsData: {
            fnum: mockAttachment.fnums[0],
            isDisplayed: true
        },
        localVue,
        store
    });

    it ('AttachmentEdit should exist', () => {
        expect(wrapper.find('#attachment-edit').exists()).toBeTruthy();
        expect(wrapper.vm.$data.attachment).toEqual(attachment);
    });

    it('Attachment edit, can_be_wiewed and can_be_deleted inputs should not exists', () => {
        expect(wrapper.find('input[name="can_be_viewed"]').exists()).toBeFalsy();
        expect(wrapper.find('input[name="can_be_deleted"]').exists()).toBeFalsy();
    });
});