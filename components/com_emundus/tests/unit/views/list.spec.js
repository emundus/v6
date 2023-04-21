import { shallowMount, createLocalVue } from '@vue/test-utils';
import list from '../../../src/views/list';
import Vuex from 'vuex';
import store from '../../../src/store';
import translate from '../../mocks/mixins/translate';

const localVue = createLocalVue();

localVue.use(Vuex);
localVue.mixin(translate);

describe('list.vue type campaign', () => {
    const type = 'campaign';
    store.commit('global/initDatas', {
        type: {
            value: type
        }
    });
    const wrapper = shallowMount(list, {
        store: store,
        localVue
    });

    it('List wrapper should exists', () => {
        expect(wrapper.find('#list').exists).toBeTruthy();
    });

    it('List component type should equal stored type (campaign)', () => {
        expect(wrapper.vm.type).toEqual(type);
    });
});