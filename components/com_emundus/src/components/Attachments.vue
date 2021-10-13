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
                <span>Edit</span>
                <span>Delete</span>
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
      type: Number,
      required: true,
    },
    fnum: {
      type: Number,
      required: true,
    }
  },
  data() {
    return {
      loading: true,
      attachments: [{"show":true, "aid":"2","id":"12","lbl":"_cv","value":"CV","description":"<p>Curriculum vitae<\/p>","allowed_types":"pdf;jpg;jpeg","nbmax":"1","ordering":"1","published":"1","ocr_keywords":null,"category":"1","video_max_length":"60","min_width":null,"max_width":null,"min_height":null,"max_height":null,"attachment_id":"12","filename":"programcoordinator_cv-a5deabe10886194e39029abb25a46844.pdf","timedate":"2021-07-01 11:15:24","can_be_deleted":"0","can_be_viewed":"0","is_validated":"-2","campaign_label":"Campagne de candidature de base ","year":"2019-2021","training":"prog"},{"show":true,"aid":"3","id":"12","lbl":"_cv","value":"CV","description":"CV un peu bizarre, \u00e7a ressemble \u00e0 un dossier axa ","allowed_types":"pdf;jpg;jpeg","nbmax":"1","ordering":"1","published":"1","ocr_keywords":null,"category":"1","video_max_length":"60","min_width":null,"max_width":null,"min_height":null,"max_height":null,"attachment_id":"12","filename":"95-1-cv-2069897862.pdf","timedate":"2021-10-12 15:22:32","can_be_deleted":"1","can_be_viewed":"0","is_validated":null,"campaign_label":"Campagne de candidature de base ","year":"2019-2021","training":"prog"}],
    };
  },
  mounted() {

    console.log(this.attachments);
    // this.getAttachments();
  },
  methods: {
    async getAttachments() {
      this.attachments = await attachmentService.getAttachments(this.user);
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
