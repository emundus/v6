<template>
  <div id="comments" class="p-4 w-full">
    <!--<h2 class="mb-2">{{ translate('COM_EMUNDUS_COMMENTS') }}</h2>-->
    <div v-if="comments.length > 0" id="filter-comments" class="flex flex-row">
      <input type="text" class="em-input mr-2" :placeholder="translate('COM_EMUNDUS_COMMENTS_SEARCH')" v-model="search" @keyup="onSearchChange">
      <select v-model="filterOpenedState">
        <option value="all">{{ translate('COM_EMUNDUS_COMMENTS_ALL_THREAD') }}</option>
        <option value="1">{{ translate('COM_EMUNDUS_COMMENTS_OPENED_THREAD') }}</option>
        <option value="0">{{ translate('COM_EMUNDUS_COMMENTS_CLOSED_THREAD') }}</option>
      </select>
    </div>

    <div v-if="parentComments.length > 0" id="comments-list-container" class="p-1">
      <div :id="'file-comment-' + comment.id" v-for="comment in parentComments" :key="comment.id"
           class="group shadow rounded-lg py-2 px-4 my-4 border"
           :class="{
            'border-transparent': comment.id != openedCommentId,
            'focus em-border-main-500': comment.id == openedCommentId,
            'em-lightgray-bg em-border-left-600': comment.opened == 0,
            'em-white-bg': comment.opened == 1,
         }"
      >
        <div class="file-comment-header flex flex-col mb-3">
          <p v-if="comment.target_id > 0" class="comment-target-label text-sm em-gray-color cursor-pointer !mb-3" @click="replyToComment(comment.id)">
            {{ getCommentTargetLabel(comment.target_id, comment.target_type) }}
          </p>

          <div class="flex flex-row justify-between items-center">
            <div class="file-comment-header-left flex flex-row cursor-pointer items-center"
                 @click="replyToComment(comment.id)">
              <div class="flex flex-row items-center">
                <div class="profile-picture h-8 w-8 rounded-full border-2 mr-2 flex flex-row justify-center items-center">
                  <div v-if="comment.profile_picture" class="image h-full w-full rounded-full" :style="'background-image: url(' + comment.profile_picture + ');background-size: cover;background-position: center;'"></div>
                  <span v-else>{{ comment.firstname.charAt(0) }}{{ comment.lastname.charAt(0) }}</span>
                </div>
                <div class="flex flex-col mr-3">
                  <span class="em-text-neutral-500 text-xs">{{ comment.updated ? comment.updated : comment.date }}</span>
                  <span>{{ comment.username }}</span>
                </div>
              </div>
              <div>
                      <span v-if="childrenComments[comment.id].length > 0" class="label em-bg-main-500">
                        {{ childrenComments[comment.id].length }}
                        {{
                          childrenComments[comment.id].length > 1 ? translate('COM_EMUNDUS_COMMENTS_ANSWERS') : translate('COM_EMUNDUS_COMMENTS_ANSWER')
                        }}
                      </span>
              </div>
            </div>
            <div class="file-comment-header-right ease-in-out duration-300 opacity-0 group-hover:opacity-100">
              <span class="material-icons-outlined cursor-pointer" @click="replyToComment(comment.id)">reply</span>
              <span v-if="access.d" class="material-icons-outlined cursor-pointer" @click="deleteComment(comment.id)">delete</span>
              <span v-if="access.u || (access.c && comment.user_id == user)" class="material-icons-outlined cursor-pointer" @click="makeCommentEditable(comment.id)">edit</span>
            </div>
          </div>
        </div>

        <div v-if="editable === comment.id">
          <textarea :id="'editable-comment-' + comment.id" class="comment-body" v-model="comment.comment_body" @keyup.enter="updateComment(comment.id)"></textarea>
          <div class="flex flex-row justify-end mt-2">
            <button id="add-comment-btn" class="em-primary-button w-fit" @click="updateComment(comment.id)">
              <span>{{ translate('COM_EMUNDUS_COMMENTS_UPDATE_COMMENT') }}</span>
              <span class="material-icons-outlined ml-1 em-neutral-300-color">send</span>
            </button>
            <button id="abort-update" class="em-secondary-button w-fit ml-2" @click="abortUpdateComment">
              <span>{{ translate('COM_EMUNDUS_COMMENTS_CANCEL') }}</span>
            </button>
          </div>
        </div>
        <p class="comment-body" v-else>{{ comment.comment_body }}</p>
        <i v-if="comment.updated_by > 0" class="text-xs em-gray-color mt-3">{{ translate('COM_EMUNDUS_COMMENTS_EDITED') }}</i>

        <div class="comment-children"
             :class="{'opened': openedCommentId === comment.id, 'hidden': openedCommentId !== comment.id}">
          <hr>
          <div :id="'file-comment-' + child.id" v-for="child in childrenComments[comment.id]" :key="child.id" dir="ltr">
            <div class="child-comment flex flex-col border-s-4 my-3 px-3">
              <div class="file-comment-header flex flex-row justify-between mb-2">
                <div class="file-comment-header-left flex flex-col">
                  <div class="flex flex-row items-center">
                    <div class="profile-picture h-8 w-8 rounded-full border-2 mr-2 flex flex-row justify-center items-center">
                      <div v-if="comment.profile_picture" class="image h-full w-full rounded-full" :style="'background-image: url(' + comment.profile_picture + '); background-size: cover;background-position: center;'"></div>
                      <span v-else>{{ comment.firstname.charAt(0) }}{{ comment.lastname.charAt(0) }}</span>
                    </div>
                    <div class="flex flex-col mr-3">
                      <span class="em-text-neutral-500 text-xs">{{ child.updated ? child.updated : child.date }}</span>
                      <span>{{ child.username }}</span>
                    </div>
                  </div>
                </div>
                <div class="file-comment-header-left">
                  <span v-if="access.d" class="material-icons-outlined cursor-pointer" @click="deleteComment(child.id)">delete</span>
                  <span v-if="access.u || (access.c && child.user_id == user)" class="material-icons-outlined cursor-pointer" @click="makeCommentEditable(child.id)">edit</span>
                </div>
              </div>

              <div v-if="editable === child.id">
                <textarea :id="'editable-comment-' + child.id" class="comment-body" v-model="child.comment_body" @keyup.enter="updateComment(child.id)"></textarea>
                <div class="flex flex-row justify-end mt-2">
                  <button id="add-comment-btn" class="em-primary-button w-fit" @click="updateComment(child.id)">
                    <span>{{ translate('COM_EMUNDUS_COMMENTS_UPDATE_COMMENT') }}</span>
                    <span class="material-icons-outlined ml-1 em-neutral-300-color">send</span>
                  </button>
                  <button id="abort-update" class="em-secondary-button w-fit ml-2" @click="abortUpdateComment">
                    <span>{{ translate('COM_EMUNDUS_COMMENTS_CANCEL') }}</span>
                  </button>
                </div>
              </div>
              <p class="comment-body" v-else>{{ child.comment_body }}</p>
              <i v-if="child.updated_by > 0" class="text-xs em-gray-color mt-3">{{ translate('COM_EMUNDUS_COMMENTS_EDITED') }}</i>
            </div>
          </div>
          <div class="add-child-comment">
          <textarea class="mb-2 p-2" @keyup.enter="addComment(comment.id)" v-model="newChildCommentText"
                    :placeholder="translate('COM_EMUNDUS_COMMENTS_ADD_COMMENT_PLACEHOLDER')"></textarea>
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
          <div class="flex flex-row justify-center items-center mt-2">
            <button class="em-primary-button w-fit" v-if="comment.opened == 1" @click="updateCommentOpenedState(comment.id, 0)">
              <span class="material-icons-outlined em-text-neutral-300">lock</span>
              <span>{{ translate('COM_EMUNDUS_COMMENTS_CLOSE_COMMENT_THREAD') }}</span>
            </button>
            <button class="em-primary-button w-fit" v-else @click="updateCommentOpenedState(comment.id, 1)">
              <span class="material-icons-outlined em-text-neutral-300">lock_open</span>
              <span>{{ translate('COM_EMUNDUS_COMMENTS_REOPEN_COMMENT_THREAD') }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    <p v-else id="empty-comments" class="text-center">{{ translate('COM_EMUNDUS_COMMENTS_NO_COMMENTS') }}</p>

    <div id="add-comment-container">
      <textarea @keyup.enter="addComment" v-model="newCommentText" class="p-2"
                :placeholder="translate('COM_EMUNDUS_COMMENTS_ADD_COMMENT_PLACEHOLDER')"></textarea>
      <div v-if="!isApplicant" class="flex flex-row items-center">
        <div class="flex flex-row items-center mr-2">
          <input type="radio" name="visible_to_applicant" v-model="visible_to_applicant" :value="false"
                 id="visible-to-coords">
          <label for="visible-to-coords" class="m-0">{{ translate('COM_EMUNDUS_COMMENTS_VISIBLE_PARTNERS') }}</label>
        </div>
        <div class="flex flex-row items-center">
          <input type="radio" name="visible_to_applicant" v-model="visible_to_applicant" :value="true"
                 id="visible-to-applicant">
          <label for="visible-to-applicant" class="m-0">{{ translate('COM_EMUNDUS_COMMENTS_VISIBLE_ALL') }}</label>
        </div>
      </div>
      <div class="flex flex-row justify-end mt-2">
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
          <textarea v-model="newCommentText" class="p-2"
                    :placeholder="translate('COM_EMUNDUS_COMMENTS_ADD_COMMENT_PLACEHOLDER')"></textarea>
          <div v-if="!isApplicant" class="flex flex-row items-center">
            <div class="flex flex-row items-center">
              <input type="radio" name="visible_to_applicant" v-model="visible_to_applicant" :value="false"
                     id="visible-to-coords">
              <label for="visible-to-coords" class="m-0">{{
                  translate('COM_EMUNDUS_COMMENTS_VISIBLE_PARTNERS')
                }}</label>
            </div>
            <div class="flex flex-row items-center ml-2">
              <input type="radio" name="visible_to_applicant" v-model="visible_to_applicant" :value="true"
                     id="visible-to-applicant">
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
        c: false,
        r: true,
        u: false,
        d: false
      })
    },
    isApplicant: {
      type: Boolean,
      default: false
    },
    currentForm: {
      type: Number,
      default: 0
    }
  },
  mixins: [mixins, errors],
  data: () => ({
    comments: [],
    newCommentText: '',
    newChildCommentText: '',
    target: {
      type: 'elements',
      id: 0,
    },
    visible_to_applicant: false,
    openedCommentId: 0,
    loading: false,
    targetableElements: {
      elements: [],
      groups: [],
      forms: []
    },
    focus: null,
    editable: null,
    tmpComment: null,
    search: '',
    filterOpenedState: '1'
  }),
  created() {
    this.getTargetableELements().then(() => {
      this.getComments();
    });
    this.addListeners();
  },
  beforeDestroy() {
    document.removeEventListener('openModalAddComment');
    document.removeEventListener('focusOnCommentElement');
  },
  methods: {
    addListeners() {
      document.addEventListener('openModalAddComment', (event) => {
        this.target.id = event.detail.targetId;
        this.target.type = event.detail.targetType;
        this.showModal();
      });

      document.addEventListener('focusOnCommentElement', (event) => {
        if (event.detail.targetId !== null && event.detail.targetId > 0) {
          const foundComment = this.parentComments.find((comment) => {
            return comment.target_id == event.detail.targetId
          });

          if (foundComment) {
            this.openedCommentId = foundComment.id;
            const commentElement = document.getElementById(`file-comment-${foundComment.id}`);
            if (commentElement) {
              commentElement.scrollIntoView({behavior: 'smooth'});
            }
          }
        } else {
          this.openedCommentId = 0;
        }
      });
    },
    dispatchCommentsLoaded() {
      const event = new CustomEvent('commentsLoaded', {
        detail: {
          comments: this.parentComments
        }
      });
      document.dispatchEvent(event);
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
        this.dispatchCommentsLoaded();
      });
    },
    async getTargetableELements() {
      return await commentsService.getTargetableElements(this.ccid).then((response) => {
        if (response.status) {
          this.targetableElements = response.data;
        }
      }).catch((error) => {
        this.handleError(error);
      });
    },
    getCommentTargetLabel(target_id, target_type = 'elements') {
      let label = '';

      // make sure targetableElements[target_type] entry exists
      if (!this.targetableElements[target_type]) {
        target_type = 'elements';
      }

      const target = this.targetableElements[target_type].find((element) => element.id === target_id);
      if (target) {
        if (target_type === 'elements') {
          if (target.element_form_label.length > 0) {
            label += `${target.element_form_label} > `;
          }

          if (target.element_group_label.length > 0) {
            label += `${target.element_group_label} > `;
          }
        }

        if (target_type === 'groups') {
          // find label of the form
          const form = this.targetableElements.forms.find((form) => form.id === target.form_id);
          if (form) {
            label += `${form.label} > `;
          }
        }

        label += target.label;
      }

      return label;
    },
    addComment(parent_id = 0) {
      this.loading = true;

      if (this.access.c) {
        if (this.isApplicant) {
          this.visible_to_applicant = true;
        }

        let commentContent = this.newCommentText;
        if (parent_id !== 0) {
          commentContent = this.newChildCommentText;
        }

        commentsService.addComment(this.ccid, commentContent, this.target, this.visible_to_applicant, parent_id).then((response) => {
          if (response.status) {
            this.comments.push(response.data);
            this.resetAddComment();
            this.getComments();
          }
        }).catch((error) => {
          this.handleError(error);
        }).finally(() => {
          this.loading = false;
        });
      }
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
      if (commentId > 0 && this.access.d) {
        this.comments = this.comments.filter((comment) => comment.id !== commentId);

        commentsService.deleteComment(commentId).then((response) => {
          if (!response.status) {
            // TODO: handle error
          }
        }).catch((error) => {
          this.handleError(error);
        });
      }
    },
    makeCommentEditable(commentId) {
      if (commentId > 0) {
        const comment = this.comments.find((comment) => comment.id === commentId);
        if (comment && comment.user_id === this.user) {
          this.editable = commentId;
          this.tmpComment = comment.comment_body;

          this.$nextTick(() => {
            const textarea = document.getElementById(`editable-comment-${commentId}`);
            if (textarea) {
              textarea.focus();
            }
          });
        }
      }
    },
    abortUpdateComment() {
      this.comments.find((comment) => comment.id === this.editable).comment_body = this.tmpComment;
      this.editable = null;
      this.tmpComment = null;
    },
    updateComment(commentId) {
      this.loading = true;

      const commentToUpdate = this.comments.find((comment) => comment.id === commentId);
      if (this.access.u || (this.access.c && commentToUpdate.user == this.user)) {
        const commentContent = commentToUpdate.comment_body;
        commentsService.updateComment(commentId, commentContent).then((response) => {
          // nothing to do
        }).catch((error) => {
          this.handleError(error);
        }).finally(() => {
          this.loading = false;
          this.editable = null;
          this.tmpComment = null;
        });
      }
    },
    updateCommentOpenedState(commentId, state) {
      this.loading = true;

      this.comments.find((comment) => comment.id == commentId).opened = state;
      commentsService.updateCommentOpenedState(commentId, state).then((response) => {
        if (!response.status) {
          // todo: display error message
        }
      }).catch((error) => {
        this.handleError(error);
      }).finally(() => {
        this.loading = false;
      });
    },
    onSearchChange() {
      this.highlight(this.search, ['.comment-body', '.comment-target-label']);
    }
  },
  computed: {
    displayedComments() {
      let displayedComments = this.comments;
      if (this.currentForm > 0) {
        displayedComments = displayedComments.filter((comment) => {
          if (comment.target_id == 0) {
            return true;
          } else if (comment.target_type == 'elements') {
            return this.targetableElements.elements.find((element) => element.id === comment.target_id && element.element_form_id === this.currentForm);
          } else if (comment.target_type == 'groups') {
            return this.targetableElements.groups.find((group) => group.id == comment.target_id).form_id == this.currentForm;
          } else if (comment.target_type == 'forms') {
            return comment.target_id == this.currentForm;
          }

          return false;
        });
      }

      displayedComments = this.filterOpenedState !== 'all' ? displayedComments.filter((comment) => comment.opened == this.filterOpenedState) : displayedComments;

      return this.isApplicant ? displayedComments.filter((comment) => comment.visible_to_applicant == 1) : displayedComments;
    },
    parentComments() {
      let parentComments =  this.displayedComments.filter((comment) => parseInt(comment.parent_id) === 0);

      if (this.search.length > 0) {
        parentComments = parentComments.filter((comment) => {
          return comment.comment_body.toLowerCase().includes(this.search.toLowerCase())
              || this.getCommentTargetLabel(comment.target_id, comment.target_type).toLowerCase().includes(this.search.toLowerCase())
              || this.childrenComments[comment.id].some((child) => {
            return child.comment_body.toLowerCase().includes(this.search.toLowerCase()) || this.getCommentTargetLabel(child.target_id, child.target_type).toLowerCase().includes(this.search.toLowerCase());
          });
        });
      }

      parentComments.sort((a, b) => {
        return new Date(b.date_time) - new Date(a.date_time);
      });

      return parentComments;
    },
    childrenComments() {
      return this.displayedComments.reduce((acc, comment) => {
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
      return this.target.id > 0 ? this.getCommentTargetLabel(this.target.id, this.target.type) : '';
    }
  }
}
</script>

<style scoped>
#empty-comments {
  margin: var(--em-spacing-4) 0 !important;
}
</style>