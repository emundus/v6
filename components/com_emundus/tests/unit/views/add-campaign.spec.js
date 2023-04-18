import { mount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import addCampaign from '../../../src/views/addCampaign';
import translate from '../../mocks/mixins/translate';
import store from '../../../src/store/index';

const localVue = createLocalVue();
localVue.mixin(translate);

describe('addCampaign.vue, impossible to submit campaign data if missing required fields', () => {
    const wrapper = mount(addCampaign, {
        propsData: {campaign: 0},
        localVue,
        store
    });

    wrapper.vm.actualLanguage = 'fr';

    it ('addCampaign should exist', () => {
        expect(wrapper.find('.campaigns__add-campaign').exists()).toBeTruthy();
    });

    it('Submit campaign should return 0 if label is empty', () => {
       const submitResponse = wrapper.vm.submit();
       expect(submitResponse).toEqual(0);
       expect(wrapper.vm.errors.label).toBe(true);
    });

    it('Submit campaign should return 0 if year is empty', () => {
        wrapper.vm.form.label.fr = 'Test Jest';
        wrapper.vm.form.label.en = 'Test Jest - EN';
        const submitResponse = wrapper.vm.submit();
        expect(submitResponse).toEqual(0);
        expect(wrapper.vm.errors.label).toBe(false);
    });


    it('Submit campaign should return 0 if start_date is empty', () => {
        wrapper.vm.form.start_date = '';
        wrapper.vm.form.end_date = '';
        wrapper.vm.form.year = '2022';
        const submitResponse = wrapper.vm.submit();
        expect(submitResponse).toEqual(0);
    });

    wrapper.vm.ready = true;
    it('Recurrent settings should exists', () => {
        expect(wrapper.find('#recurrent-settings').exists()).toBeTruthy();
    });

    it('Is recurrent should be false by default', () => {
        expect(wrapper.vm.form.params.is_recurring).toBe(0);
    });

    it('Recurring delay should be equal to 0 by default', () => {
        expect(wrapper.vm.form.params.recurring_delay).toBe(0);
    });

    it('Recurring delay should not be visible if is_recurring is false', () => {
        expect(wrapper.find('#recurring-delay').exists()).toBeFalsy();
    });
});

describe('addCampaign.vue, reccurent settings', () => {
    const wrapper = mount(addCampaign, {
        propsData: {campaign: 0},
        localVue,
        store
    });
    wrapper.vm.ready = true;
    wrapper.vm.actualLanguage = 'fr';
    wrapper.vm.form.params.is_recurring = 1;

    it('Recurring delay should be visible if is_recurring is true', () => {
        expect(wrapper.find('#recurring-delay').exists()).toBeTruthy();
    });
});