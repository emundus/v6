import { mount, createLocalVue } from '@vue/test-utils';
import Comments from '../../../../src/components/Files/Comments';
import translate from '../../../mocks/mixins/translate';
import VModal from 'vue-js-modal';
import mockComments from '../../../mocks/comments.mock';


const localVue = createLocalVue();
localVue.mixin(translate);
localVue.use(VModal);

describe('Comments.vue, all rights', () => {
    const wrapper = mount(Comments, {
        propsData: {
            fnum: mockComments.fnums[0],
            user: mockComments.users[0],
            access:{'r':true,'c':true,'u':true,'d':true}
        },
        localVue
    });

    it ('Comments constructed', () => {
        expect(wrapper.find('#comments').exists()).toBeTruthy();
    });

    wrapper.vm.$data.comments = mockComments.comments;

    it ('.comment-content should exists and its length should be equal to comments length', () => {
        expect(wrapper.findAll('.comment-content').length).toEqual(mockComments.comments.length);
    });

    it ('delete a comment with no id given should not work', () => {
        const deleted = wrapper.vm.deleteComment(0);
        expect(deleted).toBeFalsy();
        expect(wrapper.vm.$data.comments.length).toEqual(mockComments.comments.length);
    });

    wrapper.vm.$data.show_options = wrapper.vm.$data.comments[0].id;
    it('i should have access to delete action on comments', () => {
        expect(wrapper.find('.comment-delete').exists()).toBeTruthy();
    });

    const deleteCommentFn = jest.spyOn(wrapper.vm, 'deleteComment');

    it('if i click on delete button, deleteComment should be called with correct id', () => {
        wrapper.find('.comment-delete').trigger('click');
        expect(deleteCommentFn).toHaveBeenCalledWith(wrapper.vm.$data.comments[0].id);
    });
});