<template>
  <div id="comments">
    <div id="file-comments" v-for="comment in parentComments" :key="comment.id">
      <div>
        <span>{{ comment.user_id }}</span>
        <span>{{ comment.date }}</span>
      </div>
      <p>{{ comment.comment_body }}</p>
    </div>
    <div id="add-comment-container">
      <textarea @keyup.enter="addComment" v-model="comment"></textarea>
      <div>
        <input type="radio" name="visible_to_applicant" v-model="visible_to_applicant" :value="false" id="visible-to-coords">
        <label for="visible-to-coords">{{ translate('COM_EMUNDUS_COMMENTS_VISIBLE_PARTNERS') }}</label>
      </div>
      <div>
        <input type="radio" name="visible_to_applicant" v-model="visible_to_applicant" :value="true" id="visible-to-applicant">
        <label for="visible-to-applicant">{{ translate('COM_EMUNDUS_COMMENTS_VISIBLE_APPLICANT') }}</label>
      </div>
      <button id="add-comment-btn" class="em-bg-main-500 em-neutral-300-color" @click="addComment">{{ translate('COM_EMUNDUS_COMMENTS_ADD_COMMENT') }}</button>
    </div>
    <div class="em-page-loader" v-if="loading"></div>
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
    comment: '',
    target: {
      target_type: 'element',
      target_id: 0,
    },
    visible_to_applicant: false,
    loading: false,
  }),
  created() {
    this.getComments();
  },
  methods: {
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
    addComment() {
      this.loading = true;
      commentsService.addComment(this.ccid, this.comment, this.target, this.visible_to_applicant).then((response) => {
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
      this.comment = '';
      this.visible_to_applicant = false;
      this.target.target_id = 0;
      this.target.target_type = 'element';
    }
  },
  computed: {
    parentComments() {
      return this.comments.filter((comment) => parseInt(comment.parent_id) === 0);
    }
  }
}
</script>

<style scoped>
</style>