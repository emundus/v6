import { mount, createLocalVue } from '@vue/test-utils';
import '../../../mocks/matchMedia.mock';
import Editor from '../../../../src/components/editor';
import ModalAddDocuments from '../../../../src/components/AdvancedModals/ModalAddDocuments';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);
localVue.use(Editor);

describe('ModalAddDocuments.vue', () => {
    const wrapper = mount(ModalAddDocuments, {
        propsData: {
            cid: 1,
            pid: 9,
            currentDoc: 1,
            langue: 'fr-FR',
            manyLanguages: 0,
        },
        localVue
    });

    it ('ModalAddDocuments constructed', () => {
        expect(wrapper.find('#modalAddDocuments').exists()).toBeTruthy();
    });
});