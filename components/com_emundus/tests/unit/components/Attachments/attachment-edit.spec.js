import { mount, createLocalVue } from '@vue/test-utils';
import AttachmentEdit from '../../../../src/components/Attachments/AttachmentEdit';
import translate from '../../../mocks/mixins/translate';
import store from '../../../../src/store';
import mixin from '../../../../src/mixins/mixin';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.mixin(mixin);
localVue.use(VModal);

describe('AttachmentEdit.vue', () => {
    const wrapper = mount(AttachmentEdit, {
        propsData: {
            fnum: '123456789',
            isDisplayed: true
        },
        localVue,
        store
    });

    it ('AttachmentEdit constructed', () => {
        expect(wrapper.find('#attachment-edit').exists()).toBeTruthy();
    });
});