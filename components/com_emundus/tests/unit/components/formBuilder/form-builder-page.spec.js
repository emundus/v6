import { mount, createLocalVue } from '@vue/test-utils';
import '../../../mocks/matchMedia.mock';
import FormBuilderPage from '../../../../src/components/FormBuilder/FormBuilderPage';
import translate from '../../../mocks/mixins/translate';
import store from '../../../../src/store/index';
import formBuilderService from '../../../../src/services/formbuilder';

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
describe('FormBuilderPage add section behaviour', () => {
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

    it('FormBuilderPage should have a button to add a section', () => {
        expect(wrapper.find('#add-section').exists()).toBeTruthy();
    });

    // addSection() is called when clicking on the button
    const spyOnAddSection = jest.spyOn(wrapper.vm, 'addSection');
    it('FormBuilderPage should call addSection() when clicking on the button', () => {
        wrapper.find('#add-section').trigger('click');
        expect(spyOnAddSection).toHaveBeenCalled();
    });

    const spyOndisplayError = jest.spyOn(wrapper.vm, 'displayError');

    test('FormBuilderPage should display an error message when addSection() fails', async () => {
        formBuilderService.createSimpleGroup = jest.fn().mockImplementation(() => {
            return Promise.resolve({
                status: false,
                msg: 'Error message'
            });
        });

        await wrapper.vm.addSection();
        expect(spyOndisplayError).toHaveBeenCalledWith(wrapper.vm.translate('COM_EMUNDUS_FORM_BUILDER_CREATE_SECTION_ERROR'), wrapper.vm.translate('Error message'));
    });

    const spyOnGetSections = jest.spyOn(wrapper.vm, 'getSections');
    test('FormBuilderPage should call getSections() when addSection() succeeds', async () => {
        jest.clearAllMocks();
        formBuilderService.createSimpleGroup = jest.fn().mockImplementation(() => {
            return Promise.resolve({
                status: true,
                group_id: 9999
            });
        });

        await wrapper.vm.addSection();
        expect(spyOnGetSections).toHaveBeenCalled();
    });
});