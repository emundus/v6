import {createLocalVue, shallowMount} from '@vue/test-utils';
import Vuex from 'vuex';
import store from '../../../src/store';
import translate from '../../mocks/mixins/translate';
import FormBuilderPageSection from "../../../src/components/FormBuilder/FormBuilderPageSection";

const localVue = createLocalVue();

localVue.use(Vuex);
localVue.mixin(translate);

describe('FormBuilderPageSection.vue', () => {
    const wrapper = shallowMount(FormBuilderPageSection, {
        propsData: {
            profile_id: 1,
            page_id: 1,
            section: {
                "ordering": 2,
                "group_showLegend": "Nouvelle section",
                "group_tag": "GROUP_377_806",
                "label": {
                    "fr": "Nouvelle section",
                    "en": "New section"
                },
                "group_id": "806",
                "repeat_group": false,
                "elements": {},
                "hidden_group": 1
            },
            index: 0,
            totalSections: 1,
        },
        store: store,
        localVue
    });

    // test initial state
    it('form-builder-page-section-{section.group_id} should have been rendered', () => {
        expect(wrapper.find(`#form-builder-page-section-${wrapper.vm.$props.section.group_id}`).exists()).toBe(true);
    });
});