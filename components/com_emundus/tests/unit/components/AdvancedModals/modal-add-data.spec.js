import {mount, createLocalVue} from '@vue/test-utils';
import ModalAddDatas from '../../../../src/components/AdvancedModals/ModalAddDatas';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('ModalAddDatas.vue', () => {
    const wrapper = mount(ModalAddDatas, {
        propsData: {
            actualLanguage: 'fr-FR',
            manyLanguages: 0,
        },
        localVue
    });

    it('ModalAddDatas constructed', () => {
        expect(wrapper.find('#modalAddDatas').exists()).toBeTruthy();
    });
});