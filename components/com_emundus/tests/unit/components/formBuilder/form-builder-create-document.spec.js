import {mount, createLocalVue} from '@vue/test-utils';
import '../../../mocks/matchMedia.mock';
import FormBuilderCreateDocument from '../../../../src/components/FormBuilder/FormBuilderCreateDocument';
import translate from '../../../mocks/mixins/translate';
import draggable from 'vuedraggable';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(draggable);

describe('FormBuilderCreateDocument.vue', () => {
    const wrapper = mount(FormBuilderCreateDocument, {
        propsData: {
            profile_id: 9,
        },
        localVue
    });

    it('FormBuilderCreateDocument should exist', () => {
        expect(wrapper.find('#form-builder-create-document').exists()).toBeTruthy();
    });

    it('I can not save document if name is empty', () => {
        expect(wrapper.vm.saveDocument()).toBe(false);
    });

    it('I can not save if empty selected types', () => {
        wrapper.vm.$data.document.name = 'Test';
        expect(wrapper.vm.saveDocument()).toBe(false);
    });

    it('hasSample should exists and be false by default', () => {
        expect(wrapper.vm.$data.hasSample).toBe(false);
    });

    it('newSample should exists and be empty by default', () => {
        expect(wrapper.vm.$data.newSample).toBe('');
    });

    it('#sample should not exists by default', () => {
        expect(wrapper.find('#sample').exists()).toBeFalsy();
    });

    it('#current-sample should not exists by default', () => {
        expect(wrapper.find('#current-sample').exists()).toBeFalsy();
    });
});

describe('FormBuilder create document sample', () => {
    const wrapper = mount(FormBuilderCreateDocument, {
        propsData: {
            profile_id: 9,
        },
        localVue
    });

    wrapper.vm.$data.hasSample = true;
    wrapper.vm.$data.currentSample = 'test.pdf';

    it('#sample should exists', () => {
        expect(wrapper.find('#sample').exists()).toBeTruthy();
    });

    it('#current-sample should exists', () => {
        expect(wrapper.find('#current-sample').exists()).toBeTruthy();

        expect(wrapper.find('#current-sample a').exists()).toBeTruthy();
        expect(wrapper.find('#current-sample a').attributes('href')).toBe(wrapper.vm.$data.currentSample);
    });

    it('onSampleFileInputChange should check file extension', () => {
        const event = {
            target: {
                files: [{name: 'Test.php', tmp: 'test', size: 1000, type: 'text/plain'}]
            }
        };
        const response = wrapper.vm.onSampleFileInputChange(event);

        expect(response).toBe(false);
        expect(wrapper.vm.$data.newSample).toBe(null);
    });
});

describe('FormBuilder create document sample, correct file', () => {
    const wrapper = mount(FormBuilderCreateDocument, {
        propsData: {
            profile_id: 9,
        },
        localVue
    });

    wrapper.vm.$data.hasSample = true;

    it('onSampleFileInputChange should check file extension', () => {
        const validevent = {
            target: {
                files: [{name: 'Test.pdf', tmp: 'test', size: 1000, type: 'application/pdf'}]
            }
        };

        wrapper.vm.onSampleFileInputChange(validevent);
        expect(wrapper.vm.$data.newSample).not.toBe(null);

    });
});
