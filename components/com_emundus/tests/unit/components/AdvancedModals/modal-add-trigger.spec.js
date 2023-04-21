import { mount, createLocalVue } from '@vue/test-utils';
import ModalAddTrigger from '../../../../src/components/AdvancedModals/ModalAddTrigger';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('ModalAddTrigger.vue', () => {
    const triggerAction = 'candidate';
    const wrapper = mount(ModalAddTrigger, {
        propsData: {
            prog: 1,
            trigger: 1,
            triggerAction: triggerAction
        },
        localVue
    });

    it ('ModalAddTrigger constructed', () => {
        expect(wrapper.find('#modalAddTrigger' + triggerAction).exists()).toBeTruthy();
    });
});