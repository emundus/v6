import { mount, createLocalVue } from '@vue/test-utils';
import AttachmentPreview from '../../../../src/components/Attachments/AttachmentPreview';
import translate from '../../../mocks/mixins/translate';
import store from '../../../../src/store';
import mixin from '../../../../src/mixins/mixin';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.mixin(mixin);
localVue.use(VModal);

describe('AttachmentPreview.vue', () => {
    const wrapper = mount(AttachmentPreview, {
        propsData: {
            user: 95
        },
        localVue,
        store
    });

    it ('AttachmentPreview constructed', () => {
        expect(wrapper.find('#em-attachment-preview').exists()).toBeTruthy();
    });
});