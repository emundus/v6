import { mount, createLocalVue } from '@vue/test-utils';
import ModalUpdateColors from '../../../../src/components/AdvancedModals/ModalUpdateColors';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('ModalUpdateColors.vue', () => {
    const wrapper = mount(ModalUpdateColors, {
        propsData: {
            primary: '#1b1f3c',
            secondary: '#de6339',
        },
        localVue
    });

    it ('ModalUpdateColors constructed', () => {
        expect(wrapper.find('#modalUpdateColors').exists()).toBeTruthy();
    });
});