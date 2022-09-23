import { mount, createLocalVue } from '@vue/test-utils';
import FormBuilderDocuments from '../../../../src/components/FormBuilder/FormBuilderDocuments';
import translate from '../../../mocks/mixins/translate';
import draggable from 'vuedraggable';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(draggable);

describe('FormBuilderDocuments.vue', () => {
    const wrapper = mount(FormBuilderDocuments, {
        propsData: {
            profile_id: 9,
            campaign_id: 1
        },
        localVue
    });

    it ('FormBuilderDocuments should exist', () => {
        expect(wrapper.find('#form-builder-documents').exists()).toBeTruthy();
    });
});