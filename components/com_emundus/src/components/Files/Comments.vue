<template>
  <div id="comments">
    <div v-for="comment in parentComments" :key="comment.id">
      <div>
        <span>{{ comment.user_id }}</span>
        <span>{{ comment.date }}</span>
      </div>
      <p>{{ comment.comment_body }}</p>
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
    }
  },
  computed: {
    parentComments() {
      return this.comments.filter((comment) => comment.parent_id == 0);
    }
  }
}
</script>

<style scoped>
</style>