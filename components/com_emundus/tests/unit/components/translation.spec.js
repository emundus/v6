import { mount, createLocalVue } from '@vue/test-utils';
import Translation from '../../../src/components/translation';
import translate from '../../mocks/mixins/translate';

const localVue = createLocalVue();
localVue.mixin(translate);

describe('Translation.vue', () => {
    const wrapper = mount(Translation, {
        propsData: {
            label: {
                en: 'Testing',
                fr: 'Test en cours'
            },
            actualLanguage: 'fr'
        },
        localVue
    });

    it ('Translation should exist', () => {
        expect(wrapper.find('.translation').exists()).toBeTruthy();
    });
});