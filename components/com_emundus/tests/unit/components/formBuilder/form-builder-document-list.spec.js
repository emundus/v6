import {mount, createLocalVue} from '@vue/test-utils';
import FormBuilderDocumentList from '../../../../src/components/FormBuilder/FormBuilderDocumentList';
import translate from '../../../mocks/mixins/translate';
import draggable from 'vuedraggable';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(draggable);

describe('FormBuilderDocumentList.vue', () => {
    const wrapper = mount(FormBuilderDocumentList, {
        propsData: {
            profile_id: 9,
            campaign_id: 1
        },
        localVue
    });

    it('FormBuilderDocumentList should exist', () => {
        expect(wrapper.find('#form-builder-document-list').exists()).toBeTruthy();
    });
});