import { mount } from '@vue/test-utils';
import mockAttachment from '../../../mocks/attachments.mock';
import AttachmentRow from '../../../../src/components/Attachments/AttachmentRow.vue';
import store from '../../../../src/store';
import translate from '../../../mocks/mixins/translate';
import mixin from '../../../../src/mixins/mixin';

describe('AttachmentRow.vue', () => {
	const attachment = mockAttachment.attachments[1];
	const wrapper = mount(AttachmentRow, {
		propsData: {
			attachment: attachment,
			checkedAttachmentsProp: [attachment.aid],
			canUpdate: true,
		},
		mixins: [translate, mixin],
		global: {plugins: ['vue-js-modal']},
		store
	});

	it('should render the attachment row', () => {
		expect(wrapper.find('.attachment-row').exists()).toBe(true);
	});

	it('should display a warning icon if file is not found on server', () => {
		expect(wrapper.find('.warning.file-not-found').exists()).toBe(true);
	});

	it('should set checkedAttachments data to equals prop', () => {
		expect(wrapper.vm.checkedAttachments).toEqual([mockAttachment.attachments[1].aid]);
	});

	it('Expect element to have class checked', () => {
		expect(wrapper.find('.attachment-row').classes()).toContain('checked');
	});

	it('should format date', () => {
		expect(wrapper.vm.formattedDate('2021-12-01 08:04:32')).toBe('Wednesday, December 1, 2021 8:04 AM');
	});

	it('onClick .td-document should emit open-modal', () => {
		wrapper.find('.td-document').trigger('click');
		expect(wrapper.emitted('open-modal')).toBeTruthy();
	});

	it('onChange attachment-check should emit update-checked-attachments', () => {
		wrapper.find('.attachment-check').trigger('change');
		expect(wrapper.emitted('update-checked-attachments')).toBeTruthy();
	});

	it('onChange attachment-check should emit update-checked-attachments with an array as parameter', () => {
		wrapper.find('.attachment-check').trigger('change');
		expect(wrapper.emitted('update-checked-attachments')[0][0]).toEqual(['3']);
	});

	it('onChange .status select should emit update-status', () => {
		wrapper.find('.status select').trigger('change');
		expect(wrapper.emitted('update-status')).toBeTruthy();
	});

	// .visibility-permission should have class active if can_be_viewed is true
	it('should have class active if can_be_viewed is true and profiles is not empty', () => {
		expect(wrapper.find('.visibility-permission').classes()).toContain('active');
	});

	// .delete-permission should have class active if can_be_deleted is true
	it('should have class active if can_be_deleted is true', () => {
		expect(wrapper.find('.delete-permission').classes()).toContain('active');
	});

	// click on visibility-permission should emit change-permission with first param equals to can_be_viewed
	it('onClick .visibility-permission should emit change-permission with correct parameters', () => {
		wrapper.find('.visibility-permission').trigger('click');
		expect(wrapper.emitted('change-permission')).toBeTruthy();
		expect(wrapper.emitted('change-permission')[0][0]).toBe('can_be_viewed');
		expect(wrapper.emitted('change-permission')[0][1]).toBe(wrapper.vm.attachment);
	});
});

describe('AttachmentRow.vue but user can not update', () => {
	const wrapperUpdateRights = mount(AttachmentRow, {
		propsData: {
			attachment: mockAttachment.attachments[1],
			checkedAttachmentsProp: [mockAttachment.attachments[1].aid],
			canUpdate: false,
		},
		mixins: [translate, mixin],
		global: {plugins: ['vue-js-modal']},
		store
	});

	it('should have disabled attribute to true', () => {
		expect(wrapperUpdateRights.find('.valid-state select').attributes('disabled')).toBe('disabled');
	});

	it('onChange select, update-status should not be emitted', () => {
		wrapperUpdateRights.find('.status select').trigger('change');
		expect(wrapperUpdateRights.emitted('update-status')).toBeFalsy();
	});

	it('onClick .visibility-permission should not emit change-permission', () => {
		wrapperUpdateRights.find('.visibility-permission').trigger('click');
		expect(wrapperUpdateRights.emitted('change-permission')).toBeFalsy();
	});
});

describe('Attachment-row anonyme', () => {
	const wrapperAnonym = mount(AttachmentRow, {
		propsData: {
			attachment: mockAttachment.attachments[1],
			checkedAttachmentsProp: [mockAttachment.attachments[1].aid],
			canUpdate: true,
		},
		mixins: [translate, mixin],
		global: {plugins: ['vue-js-modal']},
		store
	});

	it('store global anonyme value should exists', () => {
		expect(store.state.global).toHaveProperty('anonyme');
	});

	if (store.state.global.anonyme) {
		it('if anonyme equals true canSee should be false', () => {
			expect(wrapperAnonym.vm.canSee).toBe(false);
		});


	} else {
		it('if anonyme equals false canSee should be true', () => {
			expect(wrapperAnonym.vm.canSee).toBe(true);
		});
	}

	it('if anonyme store value changes, canSee should be updated', () => {
		const newValue = !store.state.global.anonyme;
		store.dispatch('global/setAnonyme', newValue).then(() => {
			expect(wrapperAnonym.vm.canSee).toBe(!newValue);
		});
	});
});

describe('Attachment row no profiles', () => {
	let attachmentWithoutProfiles = mockAttachment.attachments[0];
	attachmentWithoutProfiles.profiles = [];

	const wrapperNoProfiles = mount(AttachmentRow, {
		propsData: {
			attachment: attachmentWithoutProfiles,
			checkedAttachmentsProp: [attachmentWithoutProfiles.aid],
			canUpdate: true,
		},
		mixins: [translate, mixin],
		global: {plugins: ['vue-js-modal']},
		store
	});

	it('should not have .visibility-permission and .delete-permission', () => {
		expect(wrapperNoProfiles.find('.visibility-permission').exists()).toBe(false);
		expect(wrapperNoProfiles.find('.delete-permission').exists()).toBe(false);
	});
});