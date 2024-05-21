import { shallowMount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import globalSettings from '../../../src/views/globalSettings';
import translate from '../../mocks/mixins/translate';
import VWave from 'v-wave';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VWave);

describe('globalSettings.vue type campaign', () => {
    const wrapper = shallowMount(globalSettings, {
        props: {
            actualLanguage: 'fr',
            coordinatorAccess: 1,
            manyLanguages: 1
        },
        localVue
    });

    it('globalSettings, no menu highlighted by default', () => {
        expect(wrapper.vm.menuHighlight).toEqual(0);
    });

    it('globalSettings, should display a list of menu with a length of ' + wrapper.vm.displayedMenus.length, () => {
        const cards = wrapper.findAll('.em-grid-3 .em-shadow-cards');
        expect(cards.length).toEqual(wrapper.vm.displayedMenus.length);
    });

    // by default there should be 4 displayed menus
    it('globalSettings, should display 4 menus', () => {
        expect(wrapper.vm.displayedMenus.length).toEqual(4);
    });
});

describe('globalSettings.vue menus', () => {
    const wrapper = shallowMount(globalSettings, {
        props: {
            actualLanguage: 'fr',
            coordinatorAccess: 1,
            manyLanguages: 1
        },
        localVue
    });

    const menus = wrapper.vm.$data.menus;

    it('If i change the menu, the menuHighlight should change after 200ms', () => {
        wrapper.vm.changeMenu(menus[1]);
        setTimeout(() => {
            expect(wrapper.vm.menuHighlight).toEqual(menus[1].index);
        }, 210);
    });
});