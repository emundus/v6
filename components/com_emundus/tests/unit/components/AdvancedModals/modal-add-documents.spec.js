import { mount, createLocalVue } from '@vue/test-utils';
import '../../../mocks/matchMedia.mock';
import EditorQuill from '../../../../src/components/editorQuill';
import ModalAddDocuments from '../../../../src/components/AdvancedModals/ModalAddDocuments';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);
localVue.use(EditorQuill);

describe('ModalAddDocuments.vue', () => {
    const wrapper = mount(ModalAddDocuments, {
        propsData: {
            cid: 1,
            pid: 9,
            currentDoc: 1,
            langue: 'fr-FR',
            manyLanguages: 0,
            mandatory: '1',
        },
        localVue
    });

    it ('ModalAddDocuments constructed', () => {
        expect(wrapper.find('#modalAddDocuments').exists()).toBeTruthy();
    });
});