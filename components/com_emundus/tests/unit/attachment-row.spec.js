import { mount } from '@vue/test-utils';
import mockAttachment from '../mocks/attachments.mock';
import AttachmentRow from '../../src/components/AttachmentRow.vue';
import store from '../../src/store';
import translate from '../mocks/mixins/translate';
import mixin from '../../src/mixins/mixin';

describe('AttachmentRow.vue', () => {
	const wrapper = mount(AttachmentRow, {
		propsData: {
			attachment: mockAttachment.attachments[1],
			checkedAttachmentsProp: [123],
		},
		mixins: [translate, mixin],
		global: {
			plugins: ['vue-js-modal']
		},
		store
	});

	it('should render the attachment row', () => {
		expect(wrapper.find('.attachment-row').exists()).toBe(true);
	});

	it('should display a warning icon if file is not found on server', () => {
		expect(wrapper.find('.warning.file-not-found').exists()).toBe(true);
	});

	it('should set checkedAttachments data to equals prop', () => {
		expect(wrapper.vm.checkedAttachments).toEqual([123]);
	});

	it('should format date', () => {
		expect(wrapper.vm.formattedDate("2021-12-01 08:04:32")).toBe('Wednesday, December 1, 2021 8:04 AM');
	});

	it('onClick .td-document should emit open-modal', () => {
		wrapper.find('.td-document').trigger('click');
		expect(wrapper.emitted('open-modal')).toBeTruthy();
	});

	it('onChange attachment-check should emit update-checked-attachments', () => {
		wrapper.find('.attachment-check').trigger('change');
		expect(wrapper.emitted('update-checked-attachments')).toBeTruthy();
	});

	it('onChange .status select should emit update-status', () => {
		wrapper.find('.status select').trigger('change');
		expect(wrapper.emitted('update-status')).toBeTruthy();
	});
});