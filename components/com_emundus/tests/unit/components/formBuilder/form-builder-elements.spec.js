import { mount, createLocalVue } from '@vue/test-utils';
import FormBuilderElements from '../../../../src/components/FormBuilder/FormBuilderElements';
import translate from '../../../mocks/mixins/translate';
import draggable from "vuedraggable";

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(draggable);

describe('FormBuilderElements.vue', () => {
    const wrapper = mount(FormBuilderElements, {
        propsData: {

        },
        localVue
    });

    it ('FormBuilderElements should exist', () => {
        expect(wrapper.find('#form-builder-elements').exists()).toBeTruthy();
    });
});