<template>
  <div id="comments" class="p-4">
    <div id="file-comment" v-for="comment in parentComments" :key="comment.id"
      class="shadow rounded-lg py-2 px-4 my-4 em-white-bg"
    >
      <div class="file-comment-header flex flex-row items-center justify-between mb-3">
        <div class="file-comment-header-left flex flex-row cursor-pointer items-center" @click="replyToComment(comment.id)">
          <div class="flex flex-col mr-3">
            <span class="em-text-neutral-500 text-xs">{{ comment.date }}</span>
            <span>{{ comment.username }}</span>
          </div>
          <div>
            <span v-if="childrenComments[comment.id].length > 0" class="label label-green-2">
              {{ childrenComments[comment.id].length }} {{ childrenComments[comment.id].length > 1 ? translate('COM_EMUNDUS_COMMENTS_ANSWERS') : translate('COM_EMUNDUS_COMMENTS_ANSWER') }}
            </span>
          </div>
        </div>
        <div class="file-comment-header-right">
          <span class="material-icons-outlined cursor-pointer" @click="replyToComment(comment.id)">reply</span>
          <span class="material-icons-outlined cursor-pointer" @click="deleteComment(comment.id)">delete</span>
        </div>
      </div>
      <p>{{ comment.comment_body }}</p>
      <p v-if="comment.target_id > 0" class="text-sm em-gray-color mt-3">{{ getCommentTargetLabel(comment.target_id) }}</p>

      <div class="comment-children" :class="{'opened': openedCommentId === comment.id, 'hidden': openedCommentId !== comment.id}">
        <hr>
        <div v-for="child in childrenComments[comment.id]" :key="child.id" dir="ltr">
          <div class="child-comment flex flex-col border-s-4 my-2 px-3">
            <div class="file-comment-header flex flex-row justify-between">
              <div class="file-comment-header-left flex flex-col">
                <span class="em-text-neutral-500 text-xs">{{ child.date }}</span>
                <span>{{ child.username }}</span>
              </div>
              <div class="file-comment-header-left">
                <span class="material-icons-outlined cursor-pointer" @click="deleteComment(child.id)">delete</span>
              </div>
            </div>
            <p>{{ child.comment_body }}</p>
          </div>
        </div>
        <div class="add-child-comment">
          <textarea class="mb-2" @keyup.enter="addComment(comment.id)" v-model="newChildCommentText"></textarea>
          <div class="w-full flex flex-row justify-end">
            <button id="add-comment-btn"
                    class="em-primary-button em-bg-main-500 em-neutral-300-color w-fit mt-2"
                    :class="{'cursor-not-allowed opacity-50': newChildCommentText.length === 0}"
                    :disabled="newChildCommentText.length === 0"
                    @click="addComment(comment.id)">
              <span>{{ translate('COM_EMUNDUS_COMMENTS_ADD_COMMENT') }}</span>
              <span class="material-icons-outlined ml-1 em-neutral-300-color">send</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <div id="add-comment-container">
      <textarea @keyup.enter="addComment" v-model="newCommentText"></textarea>
      <div class="flex flex-row items-center">
        <div class="flex flex-row items-center">
          <input type="radio" name="visible_to_applicant" v-model="visible_to_applicant" :value="false" id="visible-to-coords">
          <label for="visible-to-coords" class="m-0">{{ translate('COM_EMUNDUS_COMMENTS_VISIBLE_PARTNERS') }}</label>
        </div>
        <div class="flex flex-row items-center ml-2">
          <input type="radio" name="visible_to_applicant" v-model="visible_to_applicant" :value="true" id="visible-to-applicant">
          <label for="visible-to-applicant" class="m-0">{{ translate('COM_EMUNDUS_COMMENTS_VISIBLE_ALL') }}</label>
        </div>
      </div>
      <div class="flex flex-row justify-end">
        <button id="add-comment-btn"
                class="em-primary-button em-bg-main-500 em-neutral-300-color w-fit"
                :class="{'cursor-not-allowed opacity-50': newCommentText.length === 0}"
                :disabled="newCommentText.length === 0"
                @click="addComment(0)"
        >
          <span>{{ translate('COM_EMUNDUS_COMMENTS_ADD_COMMENT') }}</span>
          <span class="material-icons-outlined ml-1 em-neutral-300-color">send</span>
        </button>
      </div>
    </div>
    <div class="em-page-loader" v-if="loading"></div>

    <modal name="add-comment-modal" id="add-comment-modal">
      <div class="w-full h-full p-4 flex flex-col justify-between">
        <div>
          <h2 class="mb-3">{{ translate('COM_EMUNDUS_COMMENTS_ADD_COMMENT_ON') }} {{ targetLabel }}</h2>
          <textarea v-model="newCommentText"></textarea>
          <div class="flex flex-row items-center">
            <div class="flex flex-row items-center">
              <input type="radio" name="visible_to_applicant" v-model="visible_to_applicant" :value="false" id="visible-to-coords">
              <label for="visible-to-coords" class="m-0">{{ translate('COM_EMUNDUS_COMMENTS_VISIBLE_PARTNERS') }}</label>
            </div>
            <div class="flex flex-row items-center ml-2">
              <input type="radio" name="visible_to_applicant" v-model="visible_to_applicant" :value="true" id="visible-to-applicant">
              <label for="visible-to-applicant" class="m-0">{{ translate('COM_EMUNDUS_COMMENTS_VISIBLE_ALL') }}</label>
            </div>
          </div>
        </div>

        <div class="flex flex-row justify-between">
          <button @click="hideModal()"> {{ translate('COM_EMUNDUS_COMMENTS_CANCEL') }}</button>
          <button id="add-comment-btn"
                  class="em-primary-button em-bg-main-500 em-neutral-300-color w-fit"
                  :class="{'cursor-not-allowed opacity-50': newCommentText.length === 0}"
                  :disabled="newCommentText.length === 0"
                  @click="addComment(0)"
          >
            <span>{{ translate('COM_EMUNDUS_COMMENTS_ADD_COMMENT') }}</span>
            <span class="material-icons-outlined ml-1 em-neutral-300-color">send</span>
          </button>
        </div>
      </div>
    </modal>
  </div>
</template>

<script>
import commentsService from 'com_emundus/src/services/comments';
import mixins from '../../mixins/mixin';
import errors from '../../mixins/errors';

export default {
  name: 'Comments',
  props: {
    user: {
      type: String,
      required: true,
    },
    ccid: {
      type: Number,
      required: true,
    },
    access: {
      type: Object,
      default: () => ({
        c: true,
        r: true,
        u: true,
        d: true
      })
    }
  },
  mixins: [mixins, errors],
  data: () => ({
    comments: [],
    newCommentText: '',
    newChildCommentText: '',
    target: {
      type: 'element',
      id: 0,
    },
    visible_to_applicant: false,
    openedCommentId: 0,
    loading: false,
    targetableElements: [],
  }),
  created() {
    this.getComments();
    this.getTargetableELements();
    this.addListeners();
  },
  beforeDestroy() {
    document.removeEventListener('openModalAddComment');
  },
  methods: {
    addListeners() {
      document.addEventListener('openModalAddComment', (event) => {
        this.target.id = event.detail.targetId;
        this.target.type = event.detail.targetType;
        this.showModal();
      });
    },
    showModal(name = 'add-comment-modal') {
      this.$modal.show(name);
    },
    hideModal(name = 'add-comment-modal') {
      this.$modal.hide(name);
    },
    getComments() {
      this.loading = true;
      commentsService.getComments(this.ccid).then((response) => {
        if (response.status) {
          this.comments = response.data;
        }
      }).catch((error) => {
        this.handleError(error);
      }).finally(() => {
        this.loading = false;
      });
    },
    getTargetableELements() {
      commentsService.getTargetableElements(this.ccid).then((response) => {
        if (response.status) {
          this.targetableElements = response.data;
        }
      }).catch((error) => {
        this.handleError(error);
      });
    },
    getCommentTargetLabel(target_id) {
      let label = '';

      const target = this.targetableElements.find((element) => element.id === target_id);
      if (target) {
        label = `${target.element_form_label} > ${target.element_group_label} > ${target.label}`;
      }
      return label;
    },
    addComment(parent_id = 0) {
      this.loading = true;

      let commentContent = this.newCommentText;
      if (parent_id !== 0) {
        commentContent = this.newChildCommentText;
      }

      commentsService.addComment(this.ccid, commentContent, this.target, this.visible_to_applicant, parent_id).then((response) => {
        if (response.status) {
          this.comments.push(response.data);
          this.resetAddComment();
        }
      }).catch((error) => {
        this.handleError(error);
      }).finally(() => {
        this.loading = false;
      });
    },
    resetAddComment() {
      this.newCommentText = '';
      this.newChildCommentText = '';
      this.visible_to_applicant = false;
      this.target.id = 0;
      this.target.type = 'element';
      this.hideModal();
    },
    replyToComment(commentId) {
      if (commentId > 0) {
        this.resetAddComment();
        this.openedCommentId = this.openedCommentId === commentId ? 0 : commentId;
      }
    },
    deleteComment(commentId) {
      if (commentId > 0) {
        commentsService.deleteComment(commentId).then((response) => {
          if (response.status) {
            this.comments = this.comments.filter((comment) => comment.id !== commentId);
          }
        }).catch((error) => {
          this.handleError(error);
        });
      }
    }
  },
  computed: {
    parentComments() {
      return this.comments.filter((comment) => parseInt(comment.parent_id) === 0);
    },
    childrenComments() {
      return this.comments.reduce((acc, comment) => {
        if (parseInt(comment.parent_id) !== 0) {
          if (!acc[comment.parent_id]) {
            acc[comment.parent_id] = [];
          }
          acc[comment.parent_id].push(comment);
        } else {
          if (!acc[comment.id]) {
            acc[comment.id] = [];
          }
        }
        return acc;
      }, {});
    },
    targetLabel() {
      return this.target.id > 0 ? this.getCommentTargetLabel(this.target.id) : '';
    }
  }
}
</script>

<style scoped>
</style>