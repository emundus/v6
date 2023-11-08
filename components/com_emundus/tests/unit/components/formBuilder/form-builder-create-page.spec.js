import {mount, createLocalVue} from '@vue/test-utils';
import '../../../mocks/matchMedia.mock';
import FormBuilderCreatePage from '../../../../src/components/FormBuilder/FormBuilderCreatePage';
import translate from '../../../mocks/mixins/translate';
import store from '../../../../src/store/index';

const localVue = createLocalVue();
localVue.mixin(translate);
const models = [
    {
        'id': 1,
        'form_id': 378,
        'created': '0000-00-00 00:00:00',
        'label': {
            'fr': 'Infos personnelles',
            'en': 'Infos personnelles'
        },
        'intro': {
            'fr': '',
            'en': ''
        },
        displayed: true
    },
    {
        'id': 2,
        'form_id': 379,
        'created': '0000-00-00 00:00:00',
        'label': {
            'fr': 'Respect de la publication des éléments',
            'en': 'Respect de la publication des éléments'
        },
        'intro': {
            'fr': '',
            'en': ''
        },
        displayed: true
    }
];

store.commit('global/initDefaultLang', 'fr-FR');

describe('FormBuilderCreatePage.vue', () => {
    const wrapper = mount(FormBuilderCreatePage, {
        propsData: {
            profile_id: 9,
        },
        localVue,
        store
    });
    wrapper.vm.$data.models = models;
    wrapper.vm.$data.loading = false;

    it('#form-builder-create-page should exist', () => {
        expect(wrapper.find('#form-builder-create-page').exists()).toBeTruthy();
    });

    it('By default, model structure option should be set to "new"', () => {
        expect(wrapper.vm.$data.structure).toEqual('new');
        expect(wrapper.vm.$data.canUseInitialStructure).toBeTruthy();
    });

    it('input radio value="new" should be displayed', () => {
        expect(wrapper.find('input#new-structure')).toBeTruthy();
    });

    it('should diplay all models', () => {
        expect(wrapper.findAll('.model-preview').length).toEqual(models.length);
    });
});

describe('FormBuilderCreatePage.vue search through models', () => {
    const wrapper = mount(FormBuilderCreatePage, {
        propsData: {
            profile_id: 9,
        },
        localVue,
        store
    });
    wrapper.vm.$data.models = models;
    wrapper.vm.$data.loading = false;
    wrapper.vm.$data.search = '';
    wrapper.vm.$data.search = 'Respect';

    it('should display only one model', () => {
        const displayedModels = wrapper.vm.$data.models.filter(model => {
            return model.displayed;
        });

        expect(displayedModels.length).toEqual(1);
    });
});

describe('FormBuilderCreatePage.vue check model already used', () => {
    const wrapper = mount(FormBuilderCreatePage, {
        propsData: {
            profile_id: 9,
        },
        localVue,
        store
    });
    wrapper.vm.$data.models = models;
    wrapper.vm.$data.loading = false;
    wrapper.vm.$data.selected = -1;
    const isInitialStructureAlreadyUsed = jest.spyOn(wrapper.vm, 'isInitialStructureAlreadyUsed');

    wrapper.vm.$data.selected = models[0].id;
    it('isInitialStructureAlreadyUsed should have been called', () => {
        expect(isInitialStructureAlreadyUsed).toHaveBeenCalled();
    });
});

describe('FormBuilderCreatePage.vue case model already used', () => {
    // isInitialStructureAlreadyUsed should return true
    const wrapper = mount(FormBuilderCreatePage, {
        propsData: {
            profile_id: 9,
        },
        localVue,
        store
    });

    wrapper.vm.$data.models = models;
    wrapper.vm.$data.loading = false;
    wrapper.vm.$data.selected = -1;
    wrapper.vm.isInitialStructureAlreadyUsed = jest.fn(() => {
        wrapper.vm.canUseInitialStructure = false;
        return true;
    });
    const isInitialStructureAlreadyUsed = jest.spyOn(wrapper.vm, 'isInitialStructureAlreadyUsed');

    wrapper.vm.$data.selected = models[0].id;

    it('#initial-structure parent should have class "disabled"', () => {
        expect(isInitialStructureAlreadyUsed).toHaveBeenCalled();
        expect(isInitialStructureAlreadyUsed).toHaveReturnedWith(true);
        expect(wrapper.vm.$data.canUseInitialStructure).toBeFalsy();
        let input = wrapper.find('#initial-structure');
        let inputParent = input.element.parentElement;

        expect(inputParent.classList.contains('disabled')).toBeTruthy();
    });

    it('even if user is smart enough to check the input, the submit function won\'t let structure be set to other than "new"', () => {
        wrapper.vm.$data.structure = 'initial';
        wrapper.vm.createPage();
        expect(wrapper.vm.$data.structure).toEqual('new');
    });

});