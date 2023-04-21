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
});