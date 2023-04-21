import { mount, createLocalVue } from '@vue/test-utils';
import ModalAffect from '../../../../src/components/AdvancedModals/ModalAffect';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('ModalAffect.vue', () => {
    const groupProfile = 'groupProfile';
    const wrapper = mount(ModalAffect, {
        propsData: {
            group: {},
            groupProfile: groupProfile
        },
        localVue
    });

    it ('ModalAffect constructed', () => {
        expect(wrapper.find('#modalAffect' + groupProfile).exists()).toBeTruthy();
    });
});