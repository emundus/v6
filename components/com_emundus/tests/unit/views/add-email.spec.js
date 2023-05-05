import { shallowMount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import addEmail from '../../../src/views/addEmail';
import translate from '../../mocks/mixins/translate';
import store from '../../../src/store/index';

import Notifications from 'vue-notification';

const localVue = createLocalVue();
localVue.use(Notifications);
localVue.mixin(translate);


describe('addEmail.vue', () => {

    it('addEmail should exist',  () => {
        const wrapper = shallowMount(addEmail, {
            propsData: {
                campaign: 1
            },
            store,
            localVue
        });

        expect(wrapper.find('.emails__add-email').exists()).toBeTruthy();
    });
});