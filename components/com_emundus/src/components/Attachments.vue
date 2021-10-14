<template>
    <div>
        <h1>Documents - ..% envoyés</h1>
        <div class="searchbar">
          <input type="text" ref="searchbar" placeholder="Key words" @input="searchInFiles">
        </div>
        <ul v-if="attachments.length">
            <li v-for="attachment in displayedAttachments" :key="attachment.attachment_id">
              <a @click="openModal">{{attachment.filename}}</a>
              <span>{{attachment.description}}</span>
              <div class="actions">
                <span @click="openModal" >Edit</span>
                <span @click="deleteAttachment(attachment.attachment_id)">Delete</span>
              </div>
            </li>
        </ul>
        <p v-else>Aucun dossier rattaché à cet utilisateur</p>
    </div>
</template>

<script>
import attachmentService from '../services/attachment.js';

export default {
  name: 'Attachments',
  props: {
    user: {
      type: String,
      required: true,
    },
    fnum: {
      type: String,
      required: true,
    }
  },
  data() {
    return {
      loading: true,
      attachments: [],
    };
  },
  mounted() {
    this.getAttachments();
  },
  methods: {
    async getAttachments() {
      this.attachments = await attachmentService.getAttachmentsByFnum(this.fnum);
    },
    searchInFiles() {
      this.attachments.forEach((attachment, index) => {
        // if attachment description contains the search term, show it
        if (attachment.description.includes(this.$refs["searchbar"].value) || attachment.filename.includes(this.$refs["searchbar"].value)) {
          this.attachments[index].show = true;
        } else {
          this.attachments[index].show = false;
        }
      });
    },
    openModal() {

    },
    async deteteAttachment(attachment_id) {
      // remove attachment from attachments data
      this.attachments = this.attachments.filter(attachment => attachment.attachment_id !== attachment_id);

      const response = await attachmentService.deleteAttachment(attachment_id);
      console.log(response);
    }
  },
  computed: {
    displayedAttachments() {
      return this.attachments.filter(attachment => {
        return attachment.show == true || attachment.show == undefined;
      });
    }
  }
};
</script>

<style lang="scss" scoped>

</style>
