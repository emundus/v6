import { mount, createLocalVue } from '@vue/test-utils';
import IncrementalSelect from '../../../src/components/IncrementalSelect';
import translate from '../../mocks/mixins/translate';

const localVue = createLocalVue();
localVue.mixin(translate);

describe('IncrementalSelect.vue without default value set', () => {
    const wrapper = mount(IncrementalSelect, {
        propsData: {
            options: [{id: 1,label: 'Option 1'},{id: 2, label: 'Option 2'}]
        },
        localVue
    });

    it ('Wrapper should exist and set existingValues equals to props options', () => {
        expect(wrapper.vm.existingValues).toMatchObject(wrapper.props().options);
        expect(wrapper.vm.isNewVal).toBeTruthy();
    });

    it ('Select an option should change components data', () => {
        wrapper.vm.onSelectValue(wrapper.props().options[0].id);

        expect(wrapper.vm.selectedExistingValue).toBe(wrapper.props().options[0].id);
        expect(wrapper.vm.newValue).toMatchObject(wrapper.props().options[0]);
    });
});