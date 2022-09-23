import { mount, createLocalVue } from '@vue/test-utils';
import '../../../mocks/matchMedia.mock';
import FormBuilderCreateDocument from '../../../../src/components/FormBuilder/FormBuilderCreateDocument';
import translate from '../../../mocks/mixins/translate';
import draggable from "vuedraggable";

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(draggable);

describe('FormBuilderCreateDocument.vue', () => {
    const wrapper = mount(FormBuilderCreateDocument, {
        propsData: {
            profile_id: 9,
        },
        localVue
    });

    it ('FormBuilderCreateDocument should exist', () => {
        expect(wrapper.find('#form-builder-create-document').exists()).toBeTruthy();
    });
});