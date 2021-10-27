<template>
    <div id="em-attachments">
        <div class="head">
          <div class="prev-next-files" v-if="fnums.length > 1">
            <div class="prev" :class="{'active': fnumPosition > 0}" @click="prevFile">
              <!-- 
                For new header
                <span class="material-icons">
                navigate_before
              </span> -->
              <i class="small arrow left icon"></i>
            </div>
            <div class="next" :class="{'active': fnumPosition < fnums.length - 1}" @click="nextFile">
              <!--
                For new header
                <span class="material-icons">
                navigate_next
              </span> -->
              <i class="small arrow right icon"></i>
            </div>
          </div>
          <div class="displayed-user">
            <p class="name">{{ displayedUser.firstname }} {{ displayedUser.lastname }} </p>
            <p class="email">{{ displayedUser.email }}</p>
          </div>
        </div>
        <div class="wrapper" :class="{'loading': loading}">
          <div id="filters">
            <input id="searchbar" type="text" ref="searchbar" :placeholder="translate('SEARCH')" @input="searchInFiles">
            <div class="actions">
              <div v-if="canExport" class="btn-icon-text" @click="exportAttachments">
                <span class="material-icons">
                  file_upload
                </span>
                <span>
                  {{ translate('EXPORT') }}
                </span>
              </div>
              <span v-if="canDelete" class="material-icons delete" @click="deleteAttachments">
                delete_outlined
              </span>
            </div>
          </div>
          <table v-if="attachments.length">
            <thead>
                <tr>
                    <th>
                      <input type="checkbox" @change="updateAllCheckedAttachments">
                    </th>
                    <th @click="orderBy('filename')">{{ translate('NAME') }}</th>
                    <th @click="orderBy('timedate')">{{ translate('SEND_DATE') }}</th>
                    <th class="desc" @click="orderBy('description')">{{ translate('DESCRIPTION') }}</th>
                    <th class="status" @click="orderBy('is_validated')">{{ translate('STATUS') }}</th>
                    <th @click="orderBy('modified_by')">{{ translate('MODIFIED_BY') }}</th>
                    <th @click="orderBy('modified')">{{ translate('MODIFICATION_DATE') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr 
                  v-for="attachment in displayedAttachments" 
                  :key="attachment.aid"
                  :class="{'checked': checkedAttachments.includes(attachment.aid)}">
                    <td>
                      <input class="attachment-check" type="checkbox" @change="updateCheckedAttachments(attachment.aid)" :checked="checkedAttachments.includes(attachment.aid)">
                    </td>
                    <td class="td-document" @click="openModal(attachment)">{{ attachment.value }}</td>
                    <td>{{ formattedDate(attachment.timedate) }}</td>
                    <td class="desc">{{ attachment.description }}</td>
                    <td class="status valid-state" :class="{
                      'success': attachment.is_validated == 1, 
                      'error': attachment.is_validated == -2
                      }">
                      <span v-if="attachment.is_validated == 1">{{ translate('VALID') }}</span>
                      <span v-else-if="attachment.is_validated == -2">{{ translate('INVALID') }}</span>
                      <span v-else>{{ translate('WAITING') }}</span>
                    </td>
                    <td>{{ getUserNameById(attachment.modified_by) }}</td>
                    <td>{{ formattedDate(attachment.modified) }}</td>
                </tr>
            </tbody>
          </table>
          <p v-else>Aucun dossier rattaché à cet utilisateur</p>
        </div>
        <!-- TODO: create component for Modal -->
        <modal 
          id="edit-modal" 
          name="edit"
          height="70%"
          width="70%"
          :minWidth="690"
          :minHeight="550"
          styles="display:flex;flex-direction:column;justify-content:center;align-items:center;">
          <div class="modal-head">
            <div class="flex-start">
              <span class="material-icons" @click="closeModal">
                navigate_before
              </span>
              <span>{{ selectedAttachment.filename }}</span>
            </div>
            <div class="flex-end">
              <div class="prev-next-attachments">
                <div class="prev" :class="{'active': selectedAttachmentPosition > 0}" @click="prevAttachment">
                  <span class="material-icons">
                    navigate_before
                  </span>
                </div>
                <span class="lvl">{{ selectedAttachmentPosition + 1 }} / {{ displayedAttachments.length }}</span>
                <div class="next" :class="{'active': selectedAttachmentPosition < displayedAttachments.length - 1}" @click="nextAttachment">
                  <span class="material-icons">
                    navigate_next
                  </span>
                </div>
              </div>
              <a :href="attachmentPath" class="download btn-icon-text" download>
                <span class="material-icons">
                  file_download
                </span>

                <span>{{ translate('LINK_TO_DOWNLOAD') }}</span>
              </a>
            </div>
          </div>
          <div class="modal-body">
            <AttachmentPreview></AttachmentPreview>
            <AttachmentEdit @closeModal="closeModal" @saveChanges="updateAttachment" :fnum="displayedFnum"></AttachmentEdit>
          </div>
        </modal>
    	  <div class="vue-em-loader" v-if="loading"></div>
    </div>
</template>

<script>
import AttachmentPreview from '../components/AttachmentPreview.vue'
import AttachmentEdit from '../components/AttachmentEdit.vue'
import attachmentService from '../services/attachment.js';
import userService from '../services/user.js';
import fileService from '../services/file.js';
import mixin from '../mixins/mixin.js';

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
  mixins: [mixin],
  data() {
    return {
      loading: true,
      attachments: [],
      fnums: [],
      users: {},
      displayedUser: {},
      displayedFnum: this.fnum,
      checkedAttachments: [],
      selectedAttachment: {},
      lastSort: "",
      canExport: false,
      canDelete: false,
    };
  },
  mounted() {
    this.getFnums();
    this.getUsers();
    this.getAttachments();
    this.setAccessRights();
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
      if (!this.$store.state.attachment.attachments[this.displayedFnum]) {
        this.loading = true;
        this.attachments = await attachmentService.getAttachmentsByFnum(this.displayedFnum);

        this.$store.dispatch('attachment/setAttachmentsOfFnum', {
          fnum: [this.displayedFnum],
          attachments: this.attachments
        });
      } else {
        this.attachments = this.$store.state.attachment.attachments[this.displayedFnum];
      }

      this.loading = false;
    },   
    updateAttachment() {
      this.lastSort = "";
      this.getAttachments();
      this.$modal.hide('edit');
      this.selectedAttachment = {};
    },
    async setAccessRights() {
      if (!this.$store.state.user.rights[this.displayedFnum]) {
        const response = await userService.getAccessRights(this.$store.state.user.currentUser, this.displayedFnum);

        if (response.status == true) {
          this.$store.dispatch('user/setAccessRights', {
            fnum: this.displayedFnum,
            rights: response.rights
          });
        }
      } 

      this.canExport = this.$store.state.user.rights[this.displayedFnum] ? this.$store.state.user.rights[this.displayedFnum].canExport : false;
      this.canDelete = this.$store.state.user.rights[this.displayedFnum] ? this.$store.state.user.rights[this.displayedFnum].canDelete : false;
    },
    async exportAttachments() {
      if (this.canExport) {
        attachmentService.exportAttachments(this.displayedUser.id, this.displayedFnum, this.checkedAttachments).then((response) => {
          if (response.data.status == true) {
            window.open(response.data.link, '_blank');
          }
        })
      }
    },
    async deleteAttachments() {
      if (this.canDelete) {
        // remove all checked attachments from attachments array
        this.attachments = this.attachments.filter(attachment => !this.checkedAttachments.includes(attachment.aid));

        // delete all checkedAttachments
        const response = await attachmentService.deleteAttachments(this.displayedFnum, this.checkedAttachments);
        if (response.status == true) {
          // Display tooltip deleted succesfully  
        }
      }
    },

    // navigation functions
    prevFile() {
      this.displayedFnum = this.fnums[this.fnumPosition - 1];
      this.setDisplayedUser();
      this.getAttachments();
      this.setAccessRights();
    },
    nextFile() {
      this.displayedFnum = this.fnums[this.fnumPosition + 1];
      this.setDisplayedUser();
      this.getAttachments();
      this.setAccessRights();
    },
    prevAttachment() {
      this.selectedAttachment = this.displayedAttachments[this.selectedAttachmentPosition - 1];
      this.$store.dispatch('attachment/setSelectedAttachment', this.selectedAttachment);
    },
    nextAttachment() {
      this.selectedAttachment = this.displayedAttachments[this.selectedAttachmentPosition + 1];
      this.$store.dispatch('attachment/setSelectedAttachment', this.selectedAttachment);
    },

    // Front methods
    searchInFiles() {
      this.attachments.forEach((attachment, index) => {
        // if attachment description contains the search term, show it
        // lowercase the search term to avoid case sensitivity
        if (attachment.description.toLowerCase().includes(this.$refs["searchbar"].value.toLowerCase()) || attachment.value.toLowerCase().includes(this.$refs["searchbar"].value.toLowerCase())) {
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
      this.$store.dispatch('attachment/setSelectedAttachment', attachment);
    },
    closeModal() {
      this.$modal.hide('edit');
      this.selectedAttachment = {};
      this.$store.dispatch('attachment/setSelectedAttachment', {});
    }
  },
  computed: {
    displayedAttachments() {
      return this.attachments.filter(attachment => {
        return (attachment.show == true || attachment.show == undefined) && attachment.can_be_viewed;
      });
    },
    fnumPosition() {
      return this.fnums.indexOf(this.displayedFnum);
    },
    selectedAttachmentPosition() {
      return this.displayedAttachments.indexOf(this.selectedAttachment);
    },
    attachmentPath() {
      return this.$store.state.attachment.attachmentPath + this.displayedUser.id + '/' + this.selectedAttachment.filename;
    }
  }
};
</script>

<style lang="scss" scoped>
#em-attachments {
  font-size: 14px;

  .head {
    /**
    * New Header Style, keep for later
    */
    // height: 40px;
    // display: flex;
    // flex-direction: row;
    // justify-content: flex-start;
    // align-items: center;

    // margin-bottom: 16px;

    // .displayed-user {
    //   .name {
    //     font-size: 18px;
    //     font-weight: 800;
    //     line-height: 23px;
    //   }

    //   .email {
    //     font-size: 12px;
    //   }
    // }

    // .prev-next-files {
    //   display: flex;
    //   flex-direction: row;
    //   justify-content: space-between;
    //   align-items: center;

    //   >div {
    //     pointer-events: none;
    //     display: flex;
    //     flex-direction: row;
    //     justify-content: center;
    //     align-items: center;
    //     margin: 0 8px;
    //     height: 40px;
    //     width: 40px;
    //     border: 1px solid #E3E5E8;

    //     &.prev {
    //       margin-right: 0;
    //       border-radius: 4px 0px 0px 4px;
    //     }

    //     &.next {
    //       border-radius: 0px 4px 4px 0px;
    //     }

    //     &.active {
    //       pointer-events: auto;
    //       cursor: pointer;
    //     }
    //   }
    // }

    /** 
    * Old Header Style
    * todo: remove this later
    */
    width: 100%;
    display: flex;
    flex-direction: row-reverse;
    justify-content: space-between;
    align-items: center;
    margin-top: 1px; 
    padding: 10px;
    min-height: 50px;
    background-color: var(--primary-color);

    .displayed-user {
      display: flex;
      flex-direction: row;
      justify-content: flex-start;
      align-items: baseline;

      p {
        color: var(--text-light-color);
      }

      .name {
        font-size: 18px;
        font-weight: 800;
        line-height: 23px;
      }

      .email {
        margin-left: 8px !important;
        font-size: 12px;
      }
    }

    .prev-next-files {
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
      margin-right: 12px;

      >div {
        pointer-events: none;
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        color: transparent;
        transition: all .3s;

        i {
          height: 30px;
          width: 30px;
          display: flex;
          justify-content: center;
          align-items: center;
          margin: 0;
        }

        &.active {
          color: var(--text-light-color);
          pointer-events: auto;
          cursor: pointer;
        }

        &:hover {
          border-radius: 4px;
          background-color: var(--text-light-color);
          color: var(--primary-color);
        }
      }
    }
  }

  #filters {
    margin-bottom: 20px;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;

    .actions {
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: flex-end;

      >div {
        margin-right: 8px;
      }
    }

    input {
      align-self: flex-start;
      width: 221px;
    }
  }

  .wrapper {
    margin: 20px;
    width: calc(100% - 40px);

    &.loading {
      min-height: calc(100vh - 400px);
      visibility: hidden;
    }

    .material-icons.delete {
      transition: all .3s;
      width: 30px;

      &:hover {
        cursor: pointer;
        color: var(--error-color);
      }
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

    td, th {
      width: fit-content;
    }

    th.desc, td.desc {
      max-width: 250px;
      width: initial;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    th.status, td.status {
      min-width: 100px;
      white-space: nowrap;
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

      .valid-state {
        span {
          padding: 4px 8px;
          border-radius: 4px;

          color: var(--warning-color);
          background-color:  var(--warning-bg-color);
        }

        &.success {
          span {
            color: var(--success-color);
            background-color: var(--success-bg-color); 
          }
        }

        &.error {
          span {
            color: var(--error-color);
            background-color:  var(--error-bg-color); 
          }
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

  .modal-head {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border-bottom: 1px solid var(--border-color);

    .flex-start {
      display: flex;
      flex-direction: row;
      justify-content: flex-start;
      align-items: center;

      span:first-child {
        margin: 0 8px 0 20px;
        cursor: pointer;
      }
    }

    .flex-end {
      display: flex;
      flex-direction: row;
      justify-content: flex-end;
      align-items: center;
    
      .download {
        height: 32px;
        margin-right: 24px;
        color: black;

        .material-icons {
          font-size: 18px;
        }

        span:last-child {
          margin-top: -1px;
          margin-left: 4px;
        }
      }

      .prev-next-attachments {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        margin: 0 8px 0 0;
        
        .lvl {
          padding: 6px 8px 7px 8px;
          background-color: var(--grey-bg-color);
        }

        .prev {
          margin-right: 4px;
          border-radius: 4px 0px 0px 4px;
        }

        .next {
          margin-left: 4px;
          border-radius: 0px 4px 4px 0px;
        }

        .prev, .next {
          display: flex;
          flex-direction: row;
          justify-content: center;
          align-items: center;
          pointer-events: none;
          height: 32px;
          width: 32px;
          background-color: var(--grey-bg-color);
          
          span {
            color: initial;
          }

          &.active {
            pointer-events: auto;
            cursor: pointer;
          }
        }
      }
    }
  }

  .modal-body {
    height: 100%;
    width: 100%;
    max-height: 100%;
    display: flex;
    padding: 0;
  }
}
</style>
