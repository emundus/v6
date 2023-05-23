import { mount, createLocalVue } from '@vue/test-utils';
import '../../../mocks/matchMedia.mock';
import FormBuilderCreatePage from '../../../../src/components/FormBuilder/FormBuilderCreatePage';
import translate from '../../../mocks/mixins/translate';
import store from '../../../../src/store/index';

const localVue = createLocalVue();
localVue.mixin(translate);

describe('FormBuilderCreatePage.vue', () => {
    const wrapper = mount(FormBuilderCreatePage, {
        propsData: {
            profile_id: 9,
        },
        localVue,
        store
    });

    it('#form-builder-create-page should exist', () => {
        expect(wrapper.find('#form-builder-create-page').exists()).toBeTruthy();
    });

    it('By default, model structure option should be set to "new"', () => {
        expect(wrapper.vm.$data.structure).toEqual('new');
        expect(wrapper.vm.$data.canUseInitialStructure).toBeTruthy();
    });

    it ('input radio value="new" should be displayed', () => {
        const inputNew = wrapper.find('input#new-structure');
    });
});