import { mount, createLocalVue } from '@vue/test-utils';
import ModalAddEvaluation from '../../../../src/components/AdvancedModals/ModalAddEvaluation';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('ModalAddEvaluation.vue', () => {
    const wrapper = mount(ModalAddEvaluation, {
        propsData: {
            prog: 1,
            grid: 1
        },
        localVue
    });

    it ('ModalAddEvaluation constructed', () => {
        expect(wrapper.find('#modalAddEvaluation').exists()).toBeTruthy();
    });
});