<template>
    <div>
        <h1>Documents - ..% envoyés</h1>
        <div class="searchbar">
          <input type="text" ref="searchbar" placeholder="Key words" @input="searchInFiles">
        </div>
        <ul v-if="attachments.length">
            <li v-for="attachment in displayedAttachments" :key="attachment.aid">
              <a @click="openModal">{{attachment.filename}}</a>
              <span>{{attachment.description}}</span>
              <div class="actions">
                <span @click="openModal(attachment)">Edit</span>
                <span @click="deleteAttachment(attachment.aid)">Delete</span>
              </div>
            </li>
        </ul>
        <p v-else>Aucun dossier rattaché à cet utilisateur</p>
        <modal name="edit">
          <AttachmentPreview :attachment="selectedAttachment"></AttachmentPreview>
          <AttachmentEdit @closeModal="$modal.hide('edit')" @saveChanges="updateAttachment()" :attachment="selectedAttachment" :user="user" :fnum="fnum"></AttachmentEdit>
        </modal>
    </div>
</template>

<script>
import AttachmentPreview from './AttachmentPreview.vue'
import AttachmentEdit from './AttachmentEdit.vue'
import attachmentService from '../services/attachment.js';

export default {
  name: 'Attachments',
  components: {
    AttachmentPreview,
    AttachmentEdit
  },
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
      selectedAttachment: null,
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
    openModal(attachment) {
      this.$modal.show('edit');
      this.selectedAttachment = attachment;
    },
    updateAttachment() {
      this.getAttachments();
      this.$modal.hide('edit');
    },
    async deleteAttachment(id) {
      // remove attachment from attachments data
      this.attachments = this.attachments.filter(attachment => attachment.aid !== aid);

      const response = await attachmentService.deleteAttachment(this.fnum, aid);
      if (response.status == true) {
        // Display tooltip deleted succesfully  
      }
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

.actions {
  display: flex;
  justify-content: center;
  align-items: center;
  span {
    margin: 0 5px;
  }
}

</style>
