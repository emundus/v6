import {mount, createLocalVue} from '@vue/test-utils';
import FormBuilderDocuments from '../../../../src/components/FormBuilder/FormBuilderDocuments';
import translate from '../../../mocks/mixins/translate';
import draggable from 'vuedraggable';
import store from '../../../../src/store/index';
import formService from '../../../../src/services/form';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(draggable);

const mockDocuments = [
    {
        'docid': '12',
        'label': 'CV',
        'id': '60',
        'profile_id': '1',
        'campaign_id': null,
        'attachment_id': '12',
        'displayed': '1',
        'mandatory': '1',
        'ordering': '0',
        'published': '1',
        'bank_needed': null,
        'duplicate': '0',
        'has_sample': null,
        'sample_filepath': null,
        'allowed_types': 'pdf;jpg;jpeg'
    },
    {
        'docid': '8',
        'label': 'Lettre de motivation',
        'id': '61',
        'profile_id': '1',
        'campaign_id': null,
        'attachment_id': '8',
        'displayed': '1',
        'mandatory': '0',
        'ordering': '2',
        'published': '1',
        'bank_needed': null,
        'duplicate': '0',
        'has_sample': null,
        'sample_filepath': null,
        'allowed_types': 'pdf;jpg;jpeg'
    }
];

describe('FormBuilderDocuments.vue', () => {
    const wrapper = mount(FormBuilderDocuments, {
        propsData: {
            profile_id: 1,
            campaign_id: 1
        },
        localVue,
        store
    });

    it('FormBuilderDocuments should exist', () => {
        expect(wrapper.find('#form-builder-documents').exists()).toBeTruthy();
    });

    it('add documents button should exist', () => {
        expect(wrapper.find('#add-document').exists()).toBeTruthy();
    });

    const spyOnDisplayError = jest.spyOn(wrapper.vm, 'displayError');

    test('FormBuilderPage should display an error message when getDocuments() fails', async () => {
        formService.getDocuments = jest.fn().mockImplementation(() => {
            return Promise.resolve({
                status: false,
                msg: 'Error message'
            });
        });

        await wrapper.vm.getDocuments();
        expect(spyOnDisplayError).toHaveBeenCalledWith(wrapper.vm.translate('COM_EMUNDUS_FORM_BUILDER_GET_DOCUMENTS_FAILED'), wrapper.vm.translate('Error message'));
    });


    test('FormBuilderPage should display an error message when getDocuments() fails', async () => {
        jest.clearAllMocks();
        formService.getDocuments = jest.fn().mockImplementation(() => {
            return Promise.resolve({
                status: true,
                msg: 'worked',
                data: mockDocuments
            });
        });

        await wrapper.vm.getDocuments();

        expect(wrapper.vm.documents.length).toEqual(2);
    });
});

describe('FormBuilderDocuments with documents', () => {
    const wrapper = mount(FormBuilderDocuments, {
        propsData: {
            profile_id: 1,
            campaign_id: 1
        },
        localVue,
        store
    });

    wrapper.vm.$data.documents = mockDocuments;

    it('A list of documents labels should be displayed', () => {
        expect(wrapper.find('.document-label').exists()).toBeTruthy();
        expect(wrapper.findAll('.document-label').length).toEqual(2);
        expect(wrapper.find('.document-label').text()).toEqual(mockDocuments[0].label);
    });
});