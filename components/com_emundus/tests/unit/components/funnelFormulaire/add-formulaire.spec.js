import {mount, createLocalVue} from '@vue/test-utils';
import addFormulaire from '../../../../src/components/FunnelFormulaire/addFormulaire.vue';
import translate from '../../../mocks/mixins/translate';

const localVue = createLocalVue();
localVue.mixin(translate);

const mockProfiles = [
    {
        id: 1,
        form_label: 'Formulaire 1'
    },
    {
        id: 2,
        form_label: 'Formulaire 2'
    }
];


describe('addFormulaire.vue', () => {
    const wrapper = mount(addFormulaire, {
        propsData: {
            profileId: '1',
            campaignId: 1,
            profiles: mockProfiles,
            formulaireEmundus: 1,
            visibility: 1
        },
        localVue
    });

    it('Form select should exist and displays profiles options', () => {
        expect(wrapper.find('#select_profile').exists()).toBeTruthy();
        expect(wrapper.find('#select_profile').findAll('option').length).toEqual(mockProfiles.length);
    });
});