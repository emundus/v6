import {mount, createLocalVue} from '@vue/test-utils';
import addDocumentsDropfiles from '../../../../src/components/FunnelFormulaire/addDocumentsDropfiles.vue';
import translate from '../../../mocks/mixins/translate';

const localVue = createLocalVue();
localVue.mixin(translate);

describe('addDocumentsDropfiles.vue', () => {
    const wrapper = mount(addDocumentsDropfiles, {
        propsData: {
            funnelCategorie: 'test',
            profileId: 1,
            campaignId: 1,
            langue: 'fr',
            menuHighlight: 1,
            manyLanguages: 0
        },
        localVue
    });

    it('#documents-dropfiles should exist', () => {
        expect(wrapper.find('#documents-dropfiles').exists()).toBeTruthy();
    });

    // on editName a sweet alert should appear and the input inside .campaign-label should have a maxlength of 200
    it('call editName function should open a sweet alert', () => {
        wrapper.vm.editName({'id': 1, 'title': 'test test'});
        const sweetAlert = document.querySelector('.swal2-container .campaign-label');
        expect(sweetAlert).toBeTruthy();
        const input = sweetAlert.querySelector('input');
        expect(input.getAttribute('maxlength')).toBe('200');
    });
});