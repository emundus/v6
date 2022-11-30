import { mount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import addCampaign from '../../../src/views/addCampaign';
import translate from '../../mocks/mixins/translate';
import store from '../../../src/store/index';

const localVue = createLocalVue();
localVue.mixin(translate);

describe('addCampaign.vue', () => {
    const wrapper = mount(addCampaign, {
        propsData: {
            campaign: 1
        },
        localVue,
        store
    });

    it ('addCampaign should exist', () => {
        expect(wrapper.find('.campaigns__add-campaign').exists()).toBeTruthy();
    });
});