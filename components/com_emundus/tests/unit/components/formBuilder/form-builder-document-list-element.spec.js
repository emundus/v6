import {mount, createLocalVue} from '@vue/test-utils';
import FormBuilderDocumentListElement from '../../../../src/components/FormBuilder/FormBuilderDocumentListElement';
import translate from '../../../mocks/mixins/translate';
import draggable from 'vuedraggable';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(draggable);

describe('FormBuilderDocumentListElement.vue', () => {
    const wrapper = mount(FormBuilderDocumentListElement, {
        propsData: {
            document: {},
            profile_id: 9
        },
        localVue
    });

    it('FormBuilderDocumentListElement should exist', () => {
        expect(wrapper.find('#form-builder-document-list-element').exists()).toBeTruthy();
    });
});