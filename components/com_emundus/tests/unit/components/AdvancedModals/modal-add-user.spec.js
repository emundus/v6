import {mount, createLocalVue} from '@vue/test-utils';
import ModalAddUser from '../../../../src/components/AdvancedModals/ModalAddUser';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('ModalAddUser.vue', () => {
    const wrapper = mount(ModalAddUser, {
        propsData: {},
        localVue
    });

    it('ModalAddTrigger constructed', () => {
        expect(wrapper.find('#modalAddUser').exists()).toBeTruthy();
    });
});