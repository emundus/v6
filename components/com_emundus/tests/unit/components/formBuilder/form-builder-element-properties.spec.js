import { mount, createLocalVue } from '@vue/test-utils';
import FormBuilderElementProperties from '../../../../src/components/FormBuilder/FormBuilderElementProperties';
import translate from '../../../mocks/mixins/translate';
import store from '../../../../src/store/index';
import '../../../mocks/matchMedia.mock';

const localVue = createLocalVue();
localVue.mixin(translate);

window.matchMedia = window.matchMedia || function() {
    return {
        matches: false,
        addListener: function() {},
        removeListener: function() {}
    };
};

describe('FormBuilderElementProperties.vue', () => {
    const wrapper = mount(FormBuilderElementProperties, {
        propsData: {
            element: {
                label: {
                    'fr': '',
                    'en': ''
                }
            },
            profile_id: 9
        },
        localVue,
        store
    });

    it ('FormBuilderElementProperties should exist', () => {
        expect(wrapper.find('#form-builder-element-properties').exists()).toBeTruthy();
    });
});