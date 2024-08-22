import { mount, createLocalVue } from '@vue/test-utils';
import Comments from '../../../../src/components/Files/Comments';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';


const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('Comments.vue, all rights', () => {
    const wrapper = mount(Comments, {
        propsData: {
            ccid: 1,
            user: "1",
            access:{'r':true,'c':true,'u':true,'d':true}
        },
        localVue
    });

    it ('Comments constructed', () => {
        expect(wrapper.find('#comments').exists()).toBeTruthy();
    });
});