import { mount, createLocalVue } from '@vue/test-utils';
import ModalUpdateImage from '../../../../src/components/AdvancedModals/ModalUpdateImage';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('ModalUpdateImage.vue', () => {
    const wrapper = mount(ModalUpdateImage, {
        propsData: {},
        localVue
    });

    it ('ModalUpdateImage constructed', () => {
        expect(wrapper.find('#modalUpdateImage').exists()).toBeTruthy();
    });
});