<template>
    <div id="em-attachments">
        <div class="prev-next-files" v-if="fnums.length > 1">
            <div class="prev-file" v-if="fnumPosition > 0" @click="prevFile">
                <i class="fa fa-chevron-left"></i>
            </div>
            <div class="next-file" v-if="fnumPosition < fnums.length - 1" @click="nextFile">
                <i class="fa fa-chevron-right"></i>
            </div>
        </div>
        <div class="displayed-user">
          <p>{{ displayedUser.firstname }} {{ displayedUser.lastname }} </p>
          <p>{{ displayedUser.email }}</p>
        </div>
        <div id="filters">
          <input id="searchbar" type="text" ref="searchbar" placeholder="Rechercher" @input="searchInFiles">
          <div class="actions">
            <span @click="deleteAttachments">DELETE</span>
          </div>
        </div>
        <table v-if="attachments.length">
            <thead>
                <tr>
                    <th>
                      <input type="checkbox" @change="updateAllCheckedAttachments">
                    </th>
                    <th @click="orderBy('filename')">Nom</th>
                    <th @click="orderBy('timedate')">Date d'envoi</th>
                    <th @click="orderBy('description')">Description</th>
                    <th @click="orderBy('is_validated')">Statut</th>
                    <th @click="orderBy('modified_by')">Modifié par</th>
                    <th @click="orderBy('modified')">Date de modification</th>
                </tr>
            </thead>
            <tbody>
                <tr 
                  v-for="attachment in displayedAttachments" 
                  :key="attachment.aid"
                  :class="{'checked': checkedAttachments.includes(attachment.aid)}"  
                >
                    <td>
                      <input class="attachment-check" type="checkbox" @change="updateCheckedAttachments(attachment.aid)" :checked="checkedAttachments.includes(attachment.aid)">
                    </td>
                    <td class="td-document" @click="openModal(attachment)">{{ attachment.filename }}</td>
                    <td>{{ formattedTimeDate(attachment.timedate) }}</td>
                    <td>{{ attachment.description }}</td>
                    <td>{{ formattedValidState(attachment.is_validated)}}</td>
                    <td>{{ attachment.modified_by }}</td>
                    <td>{{ formattedTimeDate(attachment.modified) }}</td>
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
          <AttachmentEdit @closeModal="$modal.hide('edit')" @saveChanges="updateAttachment()" :attachment="selectedAttachment" :fnum="displayedFnum"></AttachmentEdit>
        </modal>
    </div>
</template>

<script>
import AttachmentPreview from './AttachmentPreview.vue'
import AttachmentEdit from './AttachmentEdit.vue'
import attachmentService from '../services/attachment.js';
import userService from '../services/user.js';
import fileService from '../services/file.js';
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
      fnums: [],
      users: {},
      displayedUser: {},
      displayedFnum: this.fnum,
      checkedAttachments: [],
      selectedAttachment: null,
      lastSort: "",
    };
  },
  mounted() {
    this.getFnums();
    this.getUsers();
    this.getAttachments();
  },
  methods: {
    // Getters and setters
    async getFnums() {
      this.fnums = await fileService.getFnums(this.user);
    },
    async getUsers() {
      this.users = await userService.getUsers();
      this.$store.dispatch('user/setUsers', this.users);
      this.$store.dispatch('user/setCurrentUser', this.user);

      this.setDisplayedUser();
    },
    async setDisplayedUser() {
      const response = await fileService.getFnumInfos(this.displayedFnum);
      this.displayedUser = this.users.find(user => user.id == response.fnumInfos.applicant_id);
      this.$store.dispatch('user/setDisplayedUser', this.displayedUser.id);
    },
    async getAttachments() {
      this.attachments = await attachmentService.getAttachmentsByFnum(this.displayedFnum);
    },   
    updateAttachment() {
      this.getAttachments();
      this.$modal.hide('edit');
      this.selectedAttachment = null;
    },
    async deleteAttachments() {
      // remove all checked attachments from attachments array
      this.attachments = this.attachments.filter(attachment => !this.checkedAttachments.includes(attachment.aid));

      // delete all checkedAttachments
      const response = await attachmentService.deleteAttachments(this.displayedFnum, this.checkedAttachments);
      if (response.status == true) {
        // Display tooltip deleted succesfully  
      }
    },

    // Select another fnum
    prevFile() {
      this.displayedFnum = this.fnums[this.fnumPosition - 1];
      this.setDisplayedUser();
      this.getAttachments();
    },
    nextFile() {
      this.displayedFnum = this.fnums[this.fnumPosition + 1];
      this.setDisplayedUser();
      this.getAttachments();
    },

    // Front methods
    searchInFiles() {
      this.attachments.forEach((attachment, index) => {
        // if attachment description contains the search term, show it
        // lowercase the search term to avoid case sensitivity
        if (attachment.description.toLowerCase().includes(this.$refs["searchbar"].value.toLowerCase()) || attachment.filename.toLowerCase().includes(this.$refs["searchbar"].value.toLowerCase())) {
          this.attachments[index].show = true;
        } else {
          // remove attachments from checkedAttachment list
          this.checkedAttachments = this.checkedAttachments.filter(aid => aid !== attachment.aid);
          this.attachments[index].show = false;
        }
      });
    },
    orderBy(key) {
      // if last sort is the same as the current sort, reverse the order
      if (this.lastSort == key) {
        this.attachments.reverse();
      } else {
        // sort by key
        this.attachments.sort((a, b) => {
          if (a[key] < b[key]) {
            return -1;
          }
          if (a[key] > b[key]) {
            return 1;
          }
          return 0;
        });
      }
      this.lastSort = key;
    },
    updateAllCheckedAttachments(e) {
      if (e.target.checked) {
        // check all input that has class attachment-check and add them to the checkedAttachments array
        this.checkedAttachments = this.displayedAttachments.map(attachment => attachment.aid);
      } else {
        this.checkedAttachments = [];
      }
    },
    updateCheckedAttachments(aid) {
      if (this.checkedAttachments.contains(aid)) {
        this.checkedAttachments.splice(this.checkedAttachments.indexOf(aid), 1);
      } else {
        this.checkedAttachments.push(aid);
      }
    },

    // Modal methods
    openModal(attachment) {
      this.$modal.show('edit');
      this.selectedAttachment = attachment;
    },

    // Format Methods
    formattedValidState(state) {
      switch(state) {
        case "1":
          return 'Valide';
          break;
        case "-2":
          return 'Non valide';
          break;
        case "0":
        default:
          return 'En attente';
          break;
      }
    },
    formattedTimeDate(timedate) {
      return moment(timedate).format('DD/MM/YYYY');
    }
  },
  computed: {
    displayedAttachments() {
      return this.attachments.filter(attachment => {
        return attachment.show == true || attachment.show == undefined;
      });
    },
    fnumPosition() {
      return this.fnums.indexOf(this.displayedFnum) + 1;
    }
  }
};
</script>

<style lang="scss" scoped>
#em-attachments {
  font-size: 14px;
  margin: 20px;

  #filters {
    margin-bottom: 20px;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;

    input {
      align-self: flex-start;
      width: 221px;
    }
  }

  table {
    border: 0;

    tr {
      th:first-of-type {
        width: 39px;
        input {
          margin-right: 0px;
        }
      }
    }

    tr, th {
      height: 49px;
      background: transparent;
      background-color: transparent;
    }

    thead {
      tr {
        th {
          border-top: 1px solid #e0e0e0;
          border-bottom: 1px solid #e0e0e0;
        }
      }
    }

    tbody {
      tr {
        border-bottom: 1px solid #e0e0e0;
        &:hover:not(.checked) {
          background-color: #F2F2F3;
        }

        &.checked {
          background-color: #F0F6FD;  
        }
      }

      .td-document {
        width: 250px;
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
      }
    }
  }
}

</style>
