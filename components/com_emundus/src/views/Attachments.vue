<template>
    <div id="em-attachments">
        <div class="head">
          <div class="prev-next-files" v-if="fnums.length > 1">
            <div class="prev" :class="{'active': fnumPosition > 0}" @click="changeFile(fnumPosition - 1)">
              <!--
                For new header
                <span class="material-icons">
                navigate_before
              </span> -->
              <i class="small arrow left icon"></i>
            </div>
            <div class="next" :class="{'active': fnumPosition < fnums.length - 1}" @click="changeFile(fnumPosition + 1)">
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
            <div class="searchbar-wrapper">
              <input id="searchbar" type="text" ref="searchbar" :placeholder="translate('SEARCH')" @input="searchInFiles">
              <span class="material-icons">search</span>
            </div>
            <div class="actions">
              <select name="category" @change="filterByCategory">
                <option value="all">{{ translate('SELECT_CATEGORY') }}</option>
                <option v-for="(category, key) in categories" :key="key" :value="key">{{ category }} </option>
              </select>
              <div v-if="canExport" class="btn-icon-text" @click="exportAttachments" :class="{'disabled': checkedAttachments.length < 1}">
                <span class="material-icons export">
                  file_upload
                </span>
                <span>
                  {{ translate('EXPORT') }}
                </span>
              </div>
              <span class="material-icons refresh" @click="refreshAttachments" :title="translate('COM_EMUNDUS_ATTACHMENTS_REFRESH_TITLE')">
                autorenew
              </span>
              <span v-if="canDelete" class="material-icons delete" :class="{'disabled': checkedAttachments.length < 1}" @click="confirmDeleteAttachments" :title="translate('COM_EMUNDUS_ATTACHMENTS_DELETE_TITLE')">
                delete_outlined
              </span>
            </div>
          </div>
          <table v-if="attachments.length" :class="{'loading': loading}">
            <thead>
                <tr>
                    <th>
                      <input class="attachment-check" type="checkbox" @change="updateAllCheckedAttachments">
                    </th>
                    <th @click="orderBy('value')"> 
                      {{ translate('NAME') }}
                      <span v-if="sort.orderBy == 'value' && sort.order == 'asc'" class="material-icons">arrow_upward</span>
                      <span v-if="sort.orderBy == 'value' && sort.order == 'desc'" class="material-icons">arrow_downward</span>
                    </th>
                    <th class='date' @click="orderBy('timedate')"> 
                      {{ translate('COM_EMUNDUS_ATTACHMENTS_SEND_DATE') }}
                      <span v-if="sort.orderBy == 'timedate' && sort.order == 'asc'" class="material-icons">arrow_upward</span>
                      <span v-if="sort.orderBy == 'timedate' && sort.order == 'desc'" class="material-icons">arrow_downward</span>
                    </th>
                    <th class='category' @click="orderBy('category')"> 
                      {{ translate('COM_EMUNDUS_ATTACHMENTS_CATEGORY') }}
                      <span v-if="sort.orderBy == 'category' && sort.order == 'asc'" class="material-icons">arrow_upward</span>
                      <span v-if="sort.orderBy == 'category' && sort.order == 'desc'" class="material-icons">arrow_downward</span>
                    </th>
                    <th class='desc' @click="orderBy('description')"> 
                      {{ translate('DESCRIPTION') }}
                      <span v-if="sort.orderBy == 'description' && sort.order == 'asc'" class="material-icons">arrow_upward</span>
                      <span v-if="sort.orderBy == 'description' && sort.order == 'desc'" class="material-icons">arrow_downward</span>
                    </th>
                    <th class='status' @click="orderBy('is_validated')"> 
                      {{ translate('STATUS') }}
                      <span v-if="sort.orderBy == 'is_validated' && sort.order == 'asc'" class="material-icons">arrow_upward</span>
                      <span v-if="sort.orderBy == 'is_validated' && sort.order == 'desc'" class="material-icons">arrow_downward</span>
                    </th>
                    <th @click="orderBy('modified_by')"> 
                      {{ translate('COM_EMUNDUS_ATTACHMENTS_MODIFIED_BY') }}
                      <span v-if="sort.orderBy == 'modified_by' && sort.order == 'asc'" class="material-icons">arrow_upward</span>
                      <span v-if="sort.orderBy == 'modified_by' && sort.order == 'desc'" class="material-icons">arrow_downward</span>
                    </th>
                    <th class='date' @click="orderBy('modified')"> 
                      {{ translate('COM_EMUNDUS_ATTACHMENTS_MODIFICATION_DATE') }}
                      <span v-if="sort.orderBy == 'modified' && sort.order == 'asc'" class="material-icons">arrow_upward</span>
                      <span v-if="sort.orderBy == 'modified' && sort.order == 'desc'" class="material-icons">arrow_downward</span>
                    </th>
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
                    <td class='date'>{{ formattedDate(attachment.timedate) }}</td>
                    <td class='category'>{{ categories[attachment.category] ? translate(categories[attachment.category]) : attachment.category }}</td>
                    <td class="desc">{{ attachment.description }}</td>
                    <td class="status valid-state" :class="{
                      'success': attachment.is_validated == 1, 
                      'error': attachment.is_validated == -2
                      }">
                      <span v-if="attachment.is_validated == 1">{{ translate('VALID') }}</span>
                      <span v-else-if="attachment.is_validated == -2">{{ translate('INVALID') }}</span>
                      <span v-else>{{ translate('COM_EMUNDUS_ATTACHMENTS_WAITING') }}</span>
                    </td>
                    <td>{{ getUserNameById(attachment.modified_by) }}</td>
                    <td class='date'>{{ formattedDate(attachment.modified) }}</td>
                </tr>
            </tbody>
          </table>
          <p v-else>{{ translate('COM_EMUNDUS_ATTACHMENTS_NO_ATTACHMENTS_FOUND') }}</p>
        </div>
        
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
                <div class="prev" :class="{'active': selectedAttachmentPosition > 0}" @click="changeAttachment(selectedAttachmentPosition - 1, true)">
                  <span class="material-icons">
                    navigate_before
                  </span>
                </div>
                <span class="lvl">{{ selectedAttachmentPosition + 1 }} / {{ displayedAttachments.length }}</span>
                <div class="next" :class="{'active': selectedAttachmentPosition < displayedAttachments.length - 1}" @click="changeAttachment(selectedAttachmentPosition + 1)">
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
          <transition :name="slideTransition" @before-leave="beforeLeaveSlide">
            <div class="modal-body" v-if="!modalLoading">
              <AttachmentPreview></AttachmentPreview>
              <AttachmentEdit @closeModal="closeModal" @saveChanges="updateAttachment" :fnum="displayedFnum"></AttachmentEdit>
            </div>
          </transition>
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
import Swal from "sweetalert2";

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
      categories: {},
      fnums: [],
      users: {},
      displayedUser: {},
      displayedFnum: this.fnum,
      checkedAttachments: [],
      selectedAttachment: {},
      sort: {
        last: "",
        order: "",
        orderBy: ""
      },
      canExport: false,
      canDelete: false,
      modalLoading: false,
      slideTransition: "slide-fade"
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
    async getCategories() {
      const response = await attachmentService.getAttachmentCategories();
      if (response.status) {
        // translate categories values
        Object.entries(response.categories).forEach(([key, value]) => {
          response.categories[key] = this.translate(value);
        });

        // Add attachment categories if not already given by the server
        this.attachments.forEach(attachment => {
          if (!response.categories[attachment.category] && attachment.category != "") {
            response.categories[attachment.category] = this.translate(attachment.category);
          }
        });

        // remove empty categories
        delete response.categories[""];
        
        this.$store.dispatch('attachment/setCategories', response.categories);
        this.categories = this.$store.state.attachment.categories;
      }
    },
    async getAttachments() {
      if (!this.$store.state.attachment.attachments[this.displayedFnum]) {
        this.refreshAttachments();
      } else {
        this.loading = true;
        this.attachments = this.$store.state.attachment.attachments[this.displayedFnum];
        this.categories = this.$store.state.attachment.categories;
        this.loading = false;
      }
    },
    async refreshAttachments() {
        this.loading = true;
        this.resetOrder();
        this.checkedAttachments = [];
        this.$refs['searchbar'].value = "";
        this.attachments = await attachmentService.getAttachmentsByFnum(this.displayedFnum);

        this.$store.dispatch('attachment/setAttachmentsOfFnum', {
          fnum: [this.displayedFnum],
          attachments: this.attachments
        });

        this.getCategories();
        this.loading = false;
    }, 
    updateAttachment() {
      this.resetOrder();
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

    confirmDeleteAttachments() {
      if (this.canDelete) {
        let text = this.translate('CONFIRM_DELETE_SELETED_ATTACHMENTS');

        this.checkedAttachments.forEach(aid => {
          this.attachments.forEach((attachment) => {
            if (attachment.aid == aid) {
              text += attachment.value + ", ";
            }
          });
        });

        Swal.fire(
          {
            title: this.translate('DELETE_SELECTED_ATTACHMENTS'),
            text: text,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: this.translate('JYES'),
            cancelButtonText: this.translate('JNO')
          }
        ).then((result) => {
          if (result.value) {
            this.deleteAttachments();
          }
        });
      } else {
        Swal.fire(
          {
            title: this.translate('ERROR'),
            text: this.translate('YOU_NOT_HAVE_PERMISSION_TO_DELETE_ATTACHMENTS'),
            type: 'error',
            confirmButtonColor: '#3085d6',
            confirmButtonText: this.translate('JCLOSE')
          }
        );
      }
    },
    async deleteAttachments() {
      if (this.canDelete) {
        // remove all checked attachments from attachments array
        this.attachments = this.attachments.filter(attachment => !this.checkedAttachments.includes(attachment.aid));

        // delete all checkedAttachments
        const response = await attachmentService.deleteAttachments(this.displayedFnum, this.displayedUser.id, this.checkedAttachments);
        if (response.status == true) {
          // Display tooltip deleted succesfully  
        }
      } else {
        Swal.fire(
          {
            title: this.translate('ERROR'),
            text: this.translate('YOU_NOT_HAVE_PERMISSION_TO_DELETE_ATTACHMENTS'),
            type: 'error',
            confirmButtonColor: '#3085d6',
            confirmButtonText: this.translate('JCLOSE')
          }
        );
      }
    },

    // navigation functions
    changeFile(position) {
      this.displayedFnum = this.fnums[position];
      this.setDisplayedUser();
      this.getAttachments();
      this.setAccessRights();
    },
    changeAttachment(position, reverse = false) {
      this.slideTransition = reverse ? "slide-fade-reverse" : "slide-fade";
      this.modalLoading = true;
      this.selectedAttachment = this.displayedAttachments[position];
      this.$store.dispatch('attachment/setSelectedAttachment', this.selectedAttachment);

      setTimeout(() => {
        this.modalLoading = false;
      }, 500);
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
    resetOrder() {
      this.sort = {
        last: "",
        order: "",
        orderBy: ""
      }
    },
    orderBy(key) {
      // if last sort is the same as the current sort, reverse the order
      if (this.sort.last == key) {
        this.sort.order = this.sort.order == "asc" ? "desc" : "asc";
        this.attachments.reverse();
      } else {
        // sort in ascending order by key
        this.attachments.sort((a, b) => {
          if (a[key] < b[key]) {
            return -1;
          }
          if (a[key] > b[key]) {
            return 1;
          }
          return 0;
        });

        this.sort.order = "asc";
      }

      this.sort.orderBy = key;
      this.sort.last = key;
    },
    filterByCategory(e) {
      this.attachments.forEach(attachment => {
        if (e.target.value == "all") {
          attachment.show = true;
        } else {
          if (attachment.category == e.target.value) {
            attachment.show = true;
          } else {
            // remove attachments from checkedAttachment list
            this.checkedAttachments = this.checkedAttachments.filter(aid => aid !== attachment.aid);
            attachment.show = false;
          }
        }
      });
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
    },

    // Transition hooks
    beforeLeaveSlide(el) {
      if (this.slideTransition == "slide-fade") {
        el.style.transform = "translateX(-100%)";
      }

      el.setAttribute('class', 'modal-body ' + this.slideTransition + '-leave-active ' + this.slideTransition + '-leave-to');
    },
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

<style lang='scss' scoped>
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

    .searchbar-wrapper {
      position: relative;
      .material-icons {
        position: absolute;
        top: 11px;
        left: 18px;
      }

      #searchbar {
        padding-left: 40px;
        height: 40px;
        border: 1px solid var(--border-color);
      }
    }

    .actions {
      display: flex;
      flex-direction: row;
      align-items: center;
      justify-content: flex-end;

      .export {
        margin-right: 8px;
      }

      select {
        height: 37px;
        border: 1px solid var(--border-color);
      }

      >div, >select {
        margin-right: 8px;
        
        &.disabled {
          color: var(--disabled-color);
          pointer-events: none;

          .material-icons {
            color: var(--disabled-color);
          }
        }
      }
    }
    
    .refresh {
      transition: transform .6s;
      cursor: pointer;
      margin: 0 0 0 8px;

      &:hover {
        transform: rotate(360deg);
        color: var(--primary-color);
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
      min-height: 50vh;
    }

    .material-icons.delete {
      transition: all .3s;
      width: 30px;
      color: var(--grey-color);

      &.disabled {
        color: var(--disabled-color);
        pointer-events: none;
      }

      &:hover {
        cursor: pointer;
        color: var(--error-color);
      }
    }
  }

  table {
    &.loading {
      visibility: hidden;
    }

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
          white-space: nowrap;

          .material-icons {
            transform: translateY(3px);
          }
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

    .attachment-check {
      width: 15px;
      height: 15px;
      border-radius: 0px;
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

  .slide-fade-enter-active {
    transition: all .5s ease;
  }
  .slide-fade-leave-active {
    transition: all .5s cubic-bezier(1.0, 0.5, 0.8, 1.0);
  }
  .slide-fade-enter {
    transform: translateX(100%);
  }
  .slide-fade-leave-to {
    transform: translateX(-100%);
    opacity: 0;
  }

  .slide-fade-reverse-enter-active {
    transition: all .5s ease;
  }
  .slide-fade-reverse-leave-active {
    transition: all .5s cubic-bezier(1.0, 0.8, 0.5, 1.0);
  }
  .slide-fade-reverse-enter {
    transform: translateX(-100%);
  }
  .slide-fade-reverse-leave-to {
    transform: translateX(100%);
    opacity: 0;
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
