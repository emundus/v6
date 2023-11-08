import {mount, createLocalVue} from '@vue/test-utils';
import ModalWarningFormBuilder from '../../../../src/components/AdvancedModals/ModalWarningFormBuilder';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('ModalWarningFormBuilder.vue', () => {
    const wrapper = mount(ModalWarningFormBuilder, {
        propsData: {},
        localVue
    });

    it('ModalWarningFormBuilder constructed', () => {
        expect(wrapper.find('#modalWarningFormBuilder').exists()).toBeTruthy();
    });
});