import { mount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import Editor from '../../../src/components/editor';
import translate from '../../mocks/mixins/translate';

const localVue = createLocalVue();
localVue.mixin(translate);

describe('Editor.vue', () => {
    const wrapper = mount(Editor, {
        propsData: {
            text: '',
            lang: 'fr',
            placeholder: 'Mon texte',
            id: 'uniqid_123456',
            height: '200px',
            'enable_variables': false
        },
        localVue
    });

    it ('Editor should exist', () => {
        expect(wrapper.find('.editor').exists()).toBeTruthy();
    });
});