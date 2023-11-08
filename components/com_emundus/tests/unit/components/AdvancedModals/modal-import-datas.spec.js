import {mount, createLocalVue} from '@vue/test-utils';
import ModalImportDatas from '../../../../src/components/AdvancedModals/ModalImportDatas';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('ModalImportDatas.vue', () => {
    const wrapper = mount(ModalImportDatas, {
        propsData: {},
        localVue
    });

    it('ModalImportDatas constructed', () => {
        expect(wrapper.find('#modalImportDatas').exists()).toBeTruthy();
    });
});