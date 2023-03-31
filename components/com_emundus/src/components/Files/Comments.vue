<template>
  <div>
    <div class="em-flex-column em-flex-center em-mt-24">
      <div v-for="comment in comments" class="em-input-card em-w-50 em-mb-16">
        <div class="em-flex-row em-flex-space-between">
          <div>
            <p>{{comment.user}}</p>
            <span class="em-text-neutral-500">{{formattedDate(comment.date,'LLLL','+0200')}}</span>
          </div>
          <div v-click-outside="hideOptions">
            <span class="material-icons-outlined em-pointer" @click="show_options = comment.id">more_vert</span>
            <div v-if="show_options === comment.id" class="em-comment-option">
              <span class="em-pointer" v-if="$props.access.d || ($props.access.c && comment.user_id == $props.user)" @click="deleteComment(comment.id)">{{ translate('COM_EMUNDUS_FILES_COMMENT_DELETE') }}</span>
            </div>
          </div>
        </div>

        <hr/>
        <div>
          <strong class="em-mb-8">{{comment.reason}}</strong>
          <p style="word-break: break-all;">{{comment.comment_body}}</p>
        </div>
      </div>
    </div>

    <div class="em-flex-row em-flex-center em-mt-24" v-if="adding_comment">
      <div class="em-input-card em-w-50">
        <div>
          <span class="material-icons-outlined em-pointer em-float-right em-mb-4" @click="adding_comment = false">close</span>
        </div>


        <div class="em-mb-8">
          <label for="reason">{{ translate('COM_EMUNDUS_FILES_COMMENT_TITLE') }}</label>
          <input class="em-w-100" id="reason" type="text" v-model="comment.reason"/>
        </div>

        <div>
          <label class="em-mb-4" for="body">{{ translate('COM_EMUNDUS_FILES_COMMENT_BODY') }}</label>
          <textarea id="body" v-model="comment.comment_body" />
        </div>

        <div class="em-mt-8">
          <button class="em-primary-button em-w-auto em-float-right" @click="saveComment">{{ translate('COM_EMUNDUS_FILES_VALIDATE_COMMENT') }}</button>
        </div>

      </div>
    </div>

    <div v-if="$props.access.c && !adding_comment" class="em-flex-row em-flex-center em-mt-32">
      <button class="em-primary-button em-w-auto" @click="adding_comment = true;">{{ translate('COM_EMUNDUS_FILES_ADD_COMMENT') }}</button>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import filesService from 'com_emundus/src/services/files';
import mixins from "@/mixins/mixin";

export default {
  name: "Comments",
  props: {
    user: {
      type: String,
      required: true,
    },
    fnum: {
      type: String,
      required: true,
    },
    access: {
      type: Array,
      required: true,
    },
  },
  mixins: [mixins],
  data: () => ({
    comments: [],
    comment: {
      reason: '',
      comment_body: '',
    },

    loading: false,
    adding_comment: false,
    show_options: false,
  }),
  created(){
    this.getComments();
  },
  methods: {
    getComments(){
      this.loading = true;
      filesService.getComments(this.$props.fnum).then((response) => {
        if(response.status == 1){
          this.comments = response.data;
          this.loading = false;
        } else {
          this.displayError(
              'COM_EMUNDUS_FILES_CANNOT_GET_COMMENTS',
              'COM_EMUNDUS_FILES_CANNOT_GET_COMMENTS_DESC'
          );
        }
      });
    },

    saveComment(){
      this.loading = true;
      filesService.saveComment(this.$props.fnum,this.comment).then((response) => {
        if(response.status == 1){
          this.comments.push(response.data);
          this.adding_comment = false;
          this.loading = false;
        } else {
          this.displayError(
              'COM_EMUNDUS_FILES_CANNOT_GET_COMMENTS',
              'COM_EMUNDUS_FILES_CANNOT_GET_COMMENTS_DESC'
          );
        }
      });
    },

    deleteComment(cid){
      this.loading = true;
      filesService.deleteComment(cid).then((response) => {
        if(response.status == 1){
          this.comments.splice(this.comments.findIndex(v => v.id === cid), 1);
          this.loading = false;
        } else {
          this.displayError(
              'COM_EMUNDUS_FILES_CANNOT_GET_COMMENTS',
              'COM_EMUNDUS_FILES_CANNOT_GET_COMMENTS_DESC'
          );
        }
      });
    },

    hideOptions(){
      this.show_options = false;
    }
  }
}
</script>

<style scoped>
.em-comment-option{
  position: absolute;
  border-radius: var(--em-border-radius);
  padding: 12px 16px;
  height: auto;
  background: #fff;
  border: 1px solid #E3E3E3;
}
</style>