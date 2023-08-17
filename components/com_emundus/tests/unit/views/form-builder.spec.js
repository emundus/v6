import { shallowMount, mount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import formBuilder from '../../../src/views/formBuilder';
import translate from '../../mocks/mixins/translate';
import store from '../../../src/store/index';
import VModal from 'vue-js-modal';
import Notifications from 'vue-notification';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);
localVue.use(Notifications);

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

describe('formBuilder go back button', () => {
    const wrapper = shallowMount(formBuilder, {
        localVue,
        store
    });

    wrapper.vm.$modal.show('formBuilder');

    it ('formBuilder should have a button to go back', () => {
        expect(wrapper.find('#go-back').exists()).toBeTruthy();
    });

    test ('formBuilder should set vm.principalContainer to default when clicking on the button if it\'s on create-page', () => {
        const spyClose = jest.spyOn(wrapper.vm, 'onCloseCreatePage');
        wrapper.vm.$data.principalContainer = 'create-page';
        wrapper.find('#go-back').trigger('click');

        expect(spyClose).toHaveBeenCalledWith({reload: false});
        expect(wrapper.vm.$data.principalContainer).toEqual('default');

        spyClose.mockReset();
        spyClose.mockRestore();
    });

});