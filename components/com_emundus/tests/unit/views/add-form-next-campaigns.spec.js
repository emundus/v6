import { mount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import CampaignEdition from '../../../src/views/CampaignEdition';
import translate from '../../mocks/mixins/translate';
import store from '../../../src/store/index';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);


store.commit('global/initDatas', {
    campaignId: {
        value: 1
    },
});
store.commit('global/initCurrentLanguage', 'fr-FR');

describe('CampaignEdition.vue', () => {
    const wrapper = mount(CampaignEdition, {
        propsData: {
            index: 1
        },
        localVue,
        store
    });

    it ('CampaignEdition should exist', () => {
        expect(wrapper.find('#edit-campaign').exists()).toBeTruthy();
    });

    it ('dates should be formatted correctly on initDates', () => {
       const campaign = {
           'start_date': '2023-05-14 22:00:00',
           'end_date': '2024-05-14 22:00:00'
       };

       wrapper.vm.initDates(campaign);
       expect(wrapper.vm.$data.form.start_date).toBe('14 mai 2023 à 22:00');
       expect(wrapper.vm.$data.form.end_date).toBe('14 mai 2024 à 22:00');
    });

    it ('empty end_date should be formatted correctly on initDates', () => {
        const campaign = {
            'start_date': '2023-05-14 00:00:00',
            'end_date': '0000-00-00 00:00:00'
        };

        wrapper.vm.initDates(campaign);
        expect(wrapper.vm.$data.form.start_date).toBe('14 mai 2023 à 00:00');
        expect(wrapper.vm.$data.form.end_date).toBe(null);
    });

    // setProfileId should change the profileId
    it ('setProfileId should change the profileId', () => {
        wrapper.vm.setProfileId(2);
        expect(wrapper.vm.$data.profileId).toBe(2);
    });
});