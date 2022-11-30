import { mount, createLocalVue } from '@vue/test-utils';
import Edit from '../../../../src/views/Users/Edit';
import translate from '../../../mocks/mixins/translate';
import store from '../../../../src/store/index';

const localVue = createLocalVue();
localVue.mixin(translate);

store.commit('global/initDatas', {
    isapplicant: {
        value: 1
    },
});

const user = {
    id: 95,
    profile_picture: '',
    login_type: 'internal',
    firstname: 'TEST',
    lastname: 'TEST'
};

describe('Edit.vue user null', () => {
    const wrapper = mount(Edit, {
        localVue,
        store
    });

    it ('Edit should not been displayed if user is null', () => {
        expect(wrapper.find('.em-container-profile-view').exists()).toBeFalsy();
    });
});

describe('Edit.vue user not null', () => {
    const wrapper = mount(Edit, {
        localVue,
        store
    });

    wrapper.vm.$data.user = user;

    it ('Edit should be displayed if user is not null', () => {
        expect(wrapper.find('.em-container-profile-view').exists()).toBeTruthy();
    });
});


