import { mount, createLocalVue } from '@vue/test-utils';
import ModalAffect from '../../../../src/components/AdvancedModals/ModalEmailPreview';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('ModalEmailPreview.vue', () => {
    const wrapper = mount(ModalAffect, {
        propsData: {
            model: 'test',
            models: []
        },
        localVue
    });

    it ('ModalEmailPreview constructed', () => {
        expect(wrapper.find('#modalEmailPreview_test').exists()).toBeTruthy();
    });
});