import { shallowMount, createLocalVue } from '@vue/test-utils';
import mockAttachment from '../mocks/attachments.mock';
import AttachmentRow from '../../src/components/AttachmentRow.vue';
import Vuex from 'vuex';
import VModal from 'vue-js-modal';
import store from '../../src/store';
import translate from '../mocks/mixins/translate';

const localVue = createLocalVue();

localVue.use(Vuex);
localVue.mixin(translate);
localVue.use(VModal);

describe('AttachmentRow.vue', () => {
	const wrapper = shallowMount(AttachmentRow, {
		propsData: {
			attachment: mockAttachment.attachments[0],
			checkedAttachmentsProp: [],
		},
		store: store,
		localVue
	});

	it('should render the attachment row', () => {
		expect(wrapper.find('.attachment-row').exists()).toBe(true);
	});
});