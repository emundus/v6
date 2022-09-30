import { mount, createLocalVue } from '@vue/test-utils';
import '../../../mocks/matchMedia.mock';
import FormBuilderPage from '../../../../src/components/FormBuilder/FormBuilderPage';
import translate from '../../../mocks/mixins/translate';
import store from '../../../../src/store/index';

const localVue = createLocalVue();
localVue.mixin(translate);

describe('FormBuilderPage.vue', () => {
    const pageProp = {
        id: 1,
        label: 'Titre de ma page'
    };

    const wrapper = mount(FormBuilderPage, {
        propsData: {
            profile_id: 9,
            page: pageProp
        },
        localVue,
        store
    });

    it ('FormBuilderPage should exist, and sections should be empty by default', () => {
        expect(wrapper.find('#form-builder-page').exists()).toBeTruthy();
        expect(wrapper.vm.$data.sections.length).toEqual(0);
    });

    it('FormBuilderPage title data should be equal to page label, and be displayed', () => {
        expect(wrapper.vm.$data.title).toEqual(pageProp.label);
        expect(wrapper.vm.$refs.pageTitle.innerHTML).toEqual(pageProp.label);
    });
});