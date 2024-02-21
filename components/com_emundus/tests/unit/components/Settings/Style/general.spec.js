import { mount, createLocalVue } from '@vue/test-utils';
import translate from '../../../../mocks/mixins/translate';
import General from '../../../../../src/components/Settings/Style/General';
import VModal from 'vue-js-modal';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('General.vue', () => {
    const wrapper = mount(General, {
        propsData: {},
        localVue
    });

    it('renders', () => {
        expect(wrapper.exists()).toBe(true);
    });

    // it should have 3 btns to update banner, logo and favicon
    // #btn-update-favicon, #btn-update-logo, #btn-update-banner
    wrapper.vm.bannerLink = 'https://vanilla.tchooz.io';
    wrapper.vm.$data.logo_updating = false;
    wrapper.vm.$data.hideLogo = false;
    wrapper.vm.$data.imageLink = 'images/custom/logo.png';
    wrapper.vm.$data.loading = false;
    it('should have 3 btns to update banner, logo and favicon', () => {
        expect(wrapper.find('button#btn-update-favicon').exists()).toBe(true);
        expect(wrapper.find('button#btn-update-logo').exists()).toBe(true);
        expect(wrapper.find('button#btn-update-banner').exists()).toBe(true);
    });

    it('updateView beahviour', () => {
        wrapper.vm.updateView({
            old_logo: 'logo.png',
            filename: 'logo_new.png',
        });
        //imageLink  should contains logo_new.png but with a random string at the end
        expect(wrapper.vm.imageLink).toContain('logo_new.png');
    });
});
