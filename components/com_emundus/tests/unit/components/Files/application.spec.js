import { mount, createLocalVue } from '@vue/test-utils';
import ApplicationSingle from '../../../../src/components/Files/ApplicationSingle';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('Vue scindée simple', () => {
    const wrapper = mount(ApplicationSingle, {
        propsData: {
            file: {
                id: 1,
                fnum: '2021061715003400000010000095',
                applicant_id: 95,
                student_id: 95,
                applicant_name: 'COORDINATOR Program',
            },
            type: 'evaluation',
            user: '95',
            ratio: '66/33',
            context: 'files'
        },
        localVue
    });

    it ('ApplicationSingle constructed', () => {
        expect(wrapper.find('#application-modal').exists()).toBeTruthy();
    });

    wrapper.vm.$data.access = {
        1: {r: true, c: true, u: true, d: true},
        4: {r: true, c: true, u: true, d: true},
        10: {r: true, c: true, u: true, d: true},
    };
    wrapper.vm.$data.loading = false;
    wrapper.vm.$data.evaluation_form = 270;

    it ('form default tabs should exists', () => {
        expect(wrapper.find('#modal-applicationform').exists()).toBeTruthy();
    });

    it ('evaluation grid should exists', () => {
        expect(wrapper.find('#modal-evaluationgrid').exists()).toBeTruthy();
    });
});

describe('Vue scindée, changement de dossier', () => {
    const wrapper = mount(ApplicationSingle, {
        propsData: {
            file: {
                id: 1,
                fnum: '2021061715003400000010000095',
                applicant_id: 95,
                student_id: 95,
                applicant_name: 'COORDINATOR Program',
            },
            type: 'evaluation',
            user: '95',
            ratio: '66/33',
            context: 'files'
        },
        localVue
    });

    wrapper.vm.$data.access = {
        1: {r: true, c: true, u: true, d: true},
        4: {r: true, c: true, u: true, d: true},
        10: {r: true, c: true, u: true, d: true},
    };
    wrapper.vm.$data.loading = false;
    wrapper.vm.$data.evaluation_form = 270;


    const spyOnRender = jest.spyOn(wrapper.vm, 'render');
    it ('on openSingleApplicationWithFnum event triggered, render function should be called', () => {
        window.dispatchEvent(new CustomEvent('openSingleApplicationWithFnum', {
            detail: {fnum: '2021061715003400000010000095'}
        }));
        expect(spyOnRender).toHaveBeenCalled();
    });
});