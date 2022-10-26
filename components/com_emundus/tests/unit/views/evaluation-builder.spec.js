import { mount, createLocalVue } from '@vue/test-utils';
import '../../mocks/matchMedia.mock';
import evaluationBuilder from '../../../src/views/evaluationBuilder';
import translate from '../../mocks/mixins/translate';
import store from '../../../src/store/index';
import Notifications from 'vue-notification';
import VModal from 'vue-js-modal';
import draggable from 'vuedraggable';

const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(Notifications);
localVue.use(VModal);
localVue.use(draggable);

store.commit("global/initDatas", {
    prid: {
        value: '9'
    },
    cid: {
        value: 1
    },
    index: {
        value: 0
    },
    eval: {
        value: 1
    }
});

describe('evaluationBuilder.vue', () => {
    const wrapper = mount(evaluationBuilder, {
        propsData: {
            prid: '9',
            index: 0,
            cid: 1,
            eval: 1,
        },
        localVue,
        store
    });

    it ('evaluationBuilder should exist', () => {
        expect(wrapper.find('#evaluation-builder').exists()).toBeTruthy();
    });
});