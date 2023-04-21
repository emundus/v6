import { mount, createLocalVue } from '@vue/test-utils';
import '../../../mocks/matchMedia.mock';
import FormBuilderPageSection from '../../../../src/components/FormBuilder/FormBuilderPageSection';
import translate from '../../../mocks/mixins/translate';
import draggable from 'vuedraggable';
import store from '../../../../src/store/index';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(draggable);

describe('FormBuilderPageSection.vue', () => {
    const wrapper = mount(FormBuilderPageSection, {
        propsData: {
            profile_id: 9,
            page_id: 1,
            section: {
                group_id: 1,
                repeat_group: 0,
                label: {
                    'fr': '',
                    'en': ''
                },
                elements: []
            },
            index: 0,
            totalSections: 0
        },
        localVue,
        store
    });

    it ('FormBuilderPageSection should exist', () => {
        expect(wrapper.find('#form-builder-page-section-' + wrapper.vm.$props.section.group_id).exists()).toBeTruthy();
    });
});