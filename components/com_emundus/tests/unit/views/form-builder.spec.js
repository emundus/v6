import { mount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import formBuilder from '../../../src/views/formBuilder';
import translate from '../../mocks/mixins/translate';
import store from '../../../src/store/index';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

store.commit('global/initDatas', {
    prid: {
        value: 9
    },
    cid: {
        value: 1
    }
});


describe('formBuilder.vue', () => {
    const wrapper = mount(formBuilder, {
        localVue,
        store
    });

    it ('formBuilder should exist', () => {
        expect(wrapper.find('#formBuilder').exists()).toBeTruthy();
    });
});