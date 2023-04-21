import { mount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import addFormNextCampaign from '../../../src/views/addFormNextCampaign';
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

describe('addFormNextCampaign.vue', () => {
    const wrapper = mount(addFormNextCampaign, {
        propsData: {
            index: 1
        },
        localVue,
        store
    });

    it ('addFormNextCampaign should exist', () => {
        expect(wrapper.find('#add-form-next-campaign').exists()).toBeTruthy();
    });
});