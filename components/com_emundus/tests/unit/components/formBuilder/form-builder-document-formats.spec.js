import {mount, createLocalVue} from '@vue/test-utils';
import FormBuilderDocumentFormats from '../../../../src/components/FormBuilder/FormBuilderDocumentFormats';
import translate from '../../../mocks/mixins/translate';
import draggable from 'vuedraggable';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(draggable);

describe('FormBuilderDocumentFormats.vue', () => {
    const wrapper = mount(FormBuilderDocumentFormats, {
        propsData: {
            profile_id: 9,
        },
        localVue
    });

    it('FormBuilderDocumentFormats should exist', () => {
        expect(wrapper.find('#form-builder-document-formats').exists()).toBeTruthy();
    });
});