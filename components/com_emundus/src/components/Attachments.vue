<template>
    <div id="em-attachments">
        <h1>Documents - ..% envoyés</h1>
        <div id="searchbar">
          <label for="searchbar">Rechercher : </label>
          <input name="searchbar" type="text" ref="searchbar" placeholder="Key words" @input="searchInFiles">
        </div>
        <table v-if="attachments.length">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th>Date d'envoi</th>
                    <th>Modifié par</th>
                    <th>Date de dernière modifcation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="attachment in displayedAttachments" :key="attachment.aid">
                    <td class="td-document">{{ attachment.filename }}</td>
                    <td>{{ attachment.description }}</td>
                    <td>{{ formattedValidState(attachment.is_validated)}}</td>
                    <td>{{ formattedTimeDate(attachment.timedate) }}</td>
                    <td></td>
                    <td>{{ formattedTimeDate(attachment.timedate) }}</td>
                    <td>
                        <span @click="openModal(attachment)">Edit</span>
                        <span @click="deleteAttachment(attachment.aid)">Delete</span>
                    </td>
                </tr>
            </tbody>
        </table>
        <p v-else>Aucun dossier rattaché à cet utilisateur</p>
        <modal 
          id="edit-modal" 
          name="edit"
          height="50%"
          width="50%"
          styles="display:flex;flex-direction:row;justify-content:center;align-items:center;"
        >
          <AttachmentPreview :attachment="selectedAttachment"></AttachmentPreview>
          <AttachmentEdit @closeModal="$modal.hide('edit')" @saveChanges="updateAttachment()" :attachment="selectedAttachment" :user="user" :fnum="fnum"></AttachmentEdit>
        </modal>
    </div>
</template>

<script>
import AttachmentPreview from './AttachmentPreview.vue'
import AttachmentEdit from './AttachmentEdit.vue'
import attachmentService from '../services/attachment.js';
import moment from 'moment';

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
    updateAttachment() {
      this.getAttachments();
      this.$modal.hide('edit');
      this.selectedAttachment = null;
    },
    async deleteAttachment(id) {
      this.attachments = this.attachments.filter(attachment => attachment.aid !== aid);

      const response = await attachmentService.deleteAttachment(this.fnum, aid);
      if (response.status == true) {
        // Display tooltip deleted succesfully  
      }
    },
    searchInFiles() {
      this.attachments.forEach((attachment, index) => {
        // if attachment description contains the search term, show it
        // lowercase the search term to avoid case sensitivity
        if (attachment.description.toLowerCase().includes(this.$refs["searchbar"].value.toLowerCase()) || attachment.filename.toLowerCase().includes(this.$refs["searchbar"].value.toLowerCase())) {
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
    formattedValidState(state) {
      switch(state) {
        case "1":
          return 'Validé';
          break;
        case "-2":
          return 'Invalide';
          break;
        case "0":
        default:
          return 'Indéfini';
          break;
      }
    },
    formattedTimeDate(timedate) {
      return moment(timedate).format('DD/MM/YYYY HH:mm');
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
#em-attachments {
  width: 90%;
  margin: auto;

  #searchbar {
    margin-bottom: 20px;
    display: flex;
    flex-direction: row;
    justify-content: flex-end;
    align-items: center;

    input {
      width: 350px;
      margin-left: 5px;
    }
  }

  table {
    .td-document {
      width: 250px;
      max-width: 250px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }
  }
  
  .actions {
    display: flex;
    justify-content: center;
    align-items: center;
    span {
      margin: 0 5px;
    }
  } 
}

</style>
