import { mount, createLocalVue } from '@vue/test-utils';
import Autocomplete from '../../../src/components/autocomplete';
import translate from '../../mocks/mixins/translate';

const localVue = createLocalVue();
localVue.mixin(translate);

describe('Autocomplete.vue', () => {
    const wrapper = mount(Autocomplete, {
        propsData: {
            id: 'test',
            name: 'test-autocomplete',
            year: '2022'
        },
        localVue
    });

    it ('Autocomplete should exist', () => {
        expect(wrapper.find('.autocomplete').exists()).toBeTruthy();
    });
});