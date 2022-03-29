<template>
  <div id="em-attachments">
    <div class="head">
      <div class="displayed-user">
        <p class="name">
          {{ canSee ? displayedUser.firstname + " " + displayedUser.lastname : displayedFnum}}
        </p>
        <p class="email">{{ displayedUser.email }} </p>

        <p class="attachment-progress" v-if="progress">  - {{ progress }}% {{ translate('COM_EMUNDUS_ATTACHMENTS_COMPLETED') }}</p>
      </div>
      <div class="prev-next-files">
        <div
            v-if="fnums.length > 1"
            class="prev"
            :class="{ active: fnumPosition > 0 }"
            @click="changeFile(fnumPosition - 1)"
        >
          <span class="material-icons">arrow_back</span>
        </div>
        <div
            v-if="fnums.length > 1"
            class="next"
            :class="{ active: fnumPosition < fnums.length - 1 }"
            @click="changeFile(fnumPosition + 1)"
        >
          <span class="material-icons">arrow_forward</span>
        </div>
      </div>
    </div>
    <div class="wrapper" :class="{ loading: loading}">
      <div id="filters">
        <div class="searchbar-wrapper">
          <input
              id="searchbar"
              type="text"
              ref="searchbar"
              :placeholder="translate('COM_EMUNDUS_ACTIONS_SEARCH')"
              @input="searchInFiles"
          />
          <span class="material-icons search">search</span>
          <span class="material-icons clear" @click="resetSearch">clear</span>
        </div>
        <div class="actions">
          <select
              v-if="Object.entries(thisAttachmentCategories).length > 0"
              name="category"
              class="category-select"
              ref="categoryFilter"
              @change="filterByCategory"
          >
            <option value="all">{{ translate("COM_EMUNDUS_EMAILS_SELECT_CATEGORY") }}</option>
            <option
                v-for="(category, key) in thisAttachmentCategories"
                :key="key"
                :value="key"
            >
              {{ category }}
            </option>
          </select>
          <div
              v-if="canExport"
              class="btn-icon-text"
              @click="exportAttachments"
              :class="{ disabled: checkedAttachments.length < 1 }"
          >
            <span class="material-icons export">file_upload</span>
            <span>{{ translate("COM_EMUNDUS_EXPORTS_EXPORT") }}</span>
          </div>
          <span
              class="material-icons refresh"
              @click="refreshAttachments(true)"
              :title="translate('COM_EMUNDUS_ATTACHMENTS_REFRESH_TITLE')"
          >
						autorenew
					</span>
          <span
              v-if="canDelete"
              class="material-icons delete"
              :class="{ disabled: checkedAttachments.length < 1 }"
              @click="confirmDeleteAttachments"
              :title="translate('COM_EMUNDUS_ATTACHMENTS_DELETE_TITLE')"
          >
						delete_outlined
					</span>
        </div>
      </div>
      <div v-if="attachments.length" class="table-wrapper">
        <table
            :class="{ loading: loading }"
            aria-describedby="Table of attachments information"
        >
          <thead>
          <tr>
            <th id="check-th">
              <input
                  class="attachment-check"
                  type="checkbox"
                  @change="updateAllCheckedAttachments"
              />
            </th>
            <th id="name" @click="orderBy('value')">
              {{ translate("NAME") }}
              <span
                  v-if="sort.orderBy == 'value' && sort.order == 'asc'"
                  class="material-icons"
              >arrow_upward</span
              >
              <span
                  v-if="sort.orderBy == 'value' && sort.order == 'desc'"
                  class="material-icons"
              >arrow_downward</span
              >
            </th>
            <th id="date" class="date" @click="orderBy('timedate')">
              {{ translate("COM_EMUNDUS_ATTACHMENTS_SEND_DATE") }}
              <span
                  v-if="sort.orderBy == 'timedate' && sort.order == 'asc'"
                  class="material-icons"
              >arrow_upward</span
              >
              <span
                  v-if="sort.orderBy == 'timedate' && sort.order == 'desc'"
                  class="material-icons"
              >arrow_downward</span
              >
            </th>
            <th id="desc" class="desc" @click="orderBy('description')">
              {{ translate("DESCRIPTION") }}
              <span
                  v-if="sort.orderBy == 'description' && sort.order == 'asc'"
                  class="material-icons"
              >arrow_upward</span
              >
              <span
                  v-if="sort.orderBy == 'description' && sort.order == 'desc'"
                  class="material-icons"
              >arrow_downward</span
              >
            </th>
            <th id="category" class="category" @click="orderBy('category')">
              {{ translate("COM_EMUNDUS_ATTACHMENTS_CATEGORY") }}
              <span
                  v-if="sort.orderBy == 'category' && sort.order == 'asc'"
                  class="material-icons"
              >arrow_upward</span
              >
              <span
                  v-if="sort.orderBy == 'category' && sort.order == 'desc'"
                  class="material-icons"
              >arrow_downward</span
              >
            </th>
            <th id="status" class="status" @click="orderBy('is_validated')">
              {{ translate("COM_EMUNDUS_ATTACHMENTS_CHECK") }}
              <span
                  v-if="sort.orderBy == 'is_validated' && sort.order == 'asc'"
                  class="material-icons"
              >arrow_upward</span
              >
              <span
                  v-if="sort.orderBy == 'is_validated' && sort.order == 'desc'"
                  class="material-icons"
              >arrow_downward</span
              >
            </th>
            <th v-if="canSee" id="user" @click="orderBy('user_id')">
              {{ translate("COM_EMUNDUS_ATTACHMENTS_UPLOADED_BY") }}
              <span
                  v-if="sort.orderBy == 'user_id' && sort.order == 'asc'"
                  class="material-icons"
              >arrow_upward</span
              >
              <span
                  v-if="sort.orderBy == 'user_id' && sort.order == 'desc'"
                  class="material-icons"
              >arrow_downward</span
              >
            </th>
            <th v-if="canSee" id="modified_by" @click="orderBy('modified_by')">
              {{ translate("COM_EMUNDUS_ATTACHMENTS_MODIFIED_BY") }}
              <span
                  v-if="sort.orderBy == 'modified_by' && sort.order == 'asc'"
                  class="material-icons"
              >arrow_upward</span
              >
              <span
                  v-if="sort.orderBy == 'modified_by' && sort.order == 'desc'"
                  class="material-icons"
              >arrow_downward</span
              >
            </th>
            <th id="modified" class="date" @click="orderBy('modified')">
              {{ translate("COM_EMUNDUS_ATTACHMENTS_MODIFICATION_DATE") }}
              <span
                  v-if="sort.orderBy == 'modified' && sort.order == 'asc'"
                  class="material-icons"
              >arrow_upward</span
              >
              <span
                  v-if="sort.orderBy == 'modified' && sort.order == 'desc'"
                  class="material-icons"
              >arrow_downward</span
              >
            </th>
            <th id="permissions" class="permissions">
              {{ translate("COM_EMUNDUS_ATTACHMENTS_PERMISSIONS") }}
            </th>
          </tr>
          </thead>
          <tbody>
          <AttachmentRow
              v-for="attachment in displayedAttachments"
              :key="attachment.aid"
              :attachment="attachment"
              :checkedAttachmentsProp="checkedAttachments"
              :canUpdate="canUpdate"
              :canSee="canSee"
              @open-modal="openModal(attachment)"
              @update-checked-attachments="updateCheckedAttachments"
              @update-status="updateStatus"
              @change-permission="changePermission"
          >
          </AttachmentRow>
          </tbody>
        </table>
      </div>
      <p v-else>
        {{ translate("COM_EMUNDUS_ATTACHMENTS_NO_ATTACHMENTS_FOUND") }}
      </p>
    </div>

    <modal
        id="edit-modal"
        name="edit"
        height="70%"
        width="70%"
        :minWidth="690"
        :minHeight="550"
        styles="display:flex;flex-direction:column;justify-content:center;align-items:center;"
    >
      <div class="modal-head">
        <div class="flex-start">
          <span>{{ selectedAttachment.filename }}</span>
        </div>
        <div class="flex-end">
          <a
              :href="attachmentPath"
              class="download btn-icon-text"
              download
              v-if="canDownload"
          >
            <span class="material-icons"> file_download </span>
            <span>{{ translate("COM_EMUNDUS_ATTACHMENTS_LINK_TO_DOWNLOAD") }}</span>
          </a>
          <div class="prev-next-attachments">
            <div
                class="prev"
                :class="{ active: selectedAttachmentPosition > 0 }"
                @click="changeAttachment(selectedAttachmentPosition - 1, true)"
            >
              <span class="material-icons"> navigate_before </span>
            </div>
            <span class="lvl"
            >{{ selectedAttachmentPosition + 1 }} /
							{{ displayedAttachments.length }}</span
            >
            <div
                class="next"
                :class="{
								active:
									selectedAttachmentPosition < displayedAttachments.length - 1,
							}"
                @click="changeAttachment(selectedAttachmentPosition + 1)"
            >
              <span class="material-icons"> navigate_next </span>
            </div>
          </div>
          <span class="material-icons em-pointer" @click="closeModal">
					  close
					</span>
        </div>
      </div>
      <transition :name="slideTransition" @before-leave="beforeLeaveSlide">
        <div class="modal-body" v-if="!modalLoading && displayedUser.user_id && displayedFnum">
          <AttachmentPreview
              @fileNotFound="canDownload = false"
              @canDownload="canDownload = true"
              :user="displayedUser.user_id"
          ></AttachmentPreview>
          <AttachmentEdit
              @closeModal="closeModal"
              @saveChanges="updateAttachment"
              :fnum="displayedFnum"
          ></AttachmentEdit>
        </div>
      </transition>
    </modal>
    <div class="vue-em-loader em-loader" v-if="loading"></div>
  </div>
</template>

<script>
import AttachmentPreview from "../components/AttachmentPreview.vue";
import AttachmentEdit from "../components/AttachmentEdit.vue";
import AttachmentRow from "../components/AttachmentRow.vue";
import attachmentService from "../services/attachment.js";
import userService from "../services/user.js";
import fileService from "../services/file.js";
import mixin from "../mixins/mixin.js";
import Swal from "sweetalert2";

export default {
  name: "Attachments",
  components: {
    AttachmentPreview,
    AttachmentEdit,
    AttachmentRow,
  },
  props: {
    user: {
      type: String,
      required: true,
    },
    fnum: {
      type: String,
      required: true,
    },
  },
  mixins: [mixin],
  data() {
    return {
      loading: true,
      attachments: [],
      categories: {},
      fnums: [],
      users: [],
      displayedUser: {},
      displayedFnum: this.fnum,
      checkedAttachments: [],
      selectedAttachment: {},
      progress: "",
      sort: {
        last: "",
        order: "",
        orderBy: "",
      },
      canSee: true,
      canExport: false,
      canDelete: false,
      canDownload: true,
      canUpdate: false,
      modalLoading: false,
      slideTransition: "slide-fade",
      changeFileEvent: null,
    };
  },
  created() {
    this.changeFileEvent = new Event("changeFile");
    this.canSee = !this.$store.state.global.anonyme;
  },
  mounted() {
    this.loading = true;
    this.getFormProgress();
    this.getFnums()
        .then(
            () => {
              this.getUsers().then(
                  () => {
                    this.getAttachments().then(
                        () => {
                          this.setAccessRights().then(
                              () => {
                                this.loading = false;
                              }
                          );
                        }
                    ).catch((e) => {
                      this.loading = false;
                      this.displayErrorMessage(e);
                    });
                  },
              ).catch((e) => {
                this.loading = false;
                this.displayErrorMessage(e);
              });
            }
        ).catch((e) => {
      this.loading = false;
      this.displayErrorMessage(e);
    });
  },
  methods: {
    // Getters and setters
    async getFormProgress() {
      const response = await attachmentService.getAttachmentProgress(this.displayedFnum);

      if (response.status) {
        this.progress = response.progress[0].attachment_progress;
      }
    },
    async getFnums() {
      const fnumsOnPage = document.getElementsByClassName('em_file_open');
      for (let fnum of fnumsOnPage) {
        this.fnums.push(fnum.id);
      }
    },
    async getUsers() {
      this.$store.dispatch("user/setCurrentUser", this.user);
      await this.setDisplayedUser();
    },
    async setDisplayedUser() {
      const response = await fileService.getFnumInfos(this.displayedFnum);

      if (response && response.fnumInfos) {
        const foundUser = this.users && this.users.length ? this.users.find((user) => user.user_id == response.fnumInfos.applicant_id) : false;

        if (!foundUser) {
          const resp = await userService.getUserById(
              response.fnumInfos.applicant_id
          );
          if (resp.status) {
            this.users.push(resp.user[0]);
            this.displayedUser = resp.user[0];
            this.$store.dispatch(
                "user/setDisplayedUser",
                this.displayedUser.user_id
            );
          } else {
            this.displayErrorMessage(
                this.translate("COM_EMUNDUS_ATTACHMENTS_USER_NOT_FOUND")
            );
          }
        } else {
          this.displayedUser = foundUser;
          this.$store.dispatch(
              "user/setDisplayedUser",
              this.displayedUser.user_id
          );
        }
      } else {
        this.displayErrorMessage(
            this.translate("COM_EMUNDUS_ATTACHMENTS_USER_NOT_FOUND")
        );
      }
    },
    async getAttachments() {
      if (!this.$store.state.attachment.attachments[this.displayedFnum]) {
        await this.refreshAttachments();
      } else {
        this.attachments = this.$store.state.attachment.attachments[this.displayedFnum];
        this.categories = this.$store.state.attachment.categories;
      }
    },
    async refreshAttachments(addLoading = false) {
      if (addLoading === true) {
        this.loading = true;
      }

      this.resetOrder();
      this.checkedAttachments = [];
      this.$refs["searchbar"].value = "";
      const response = await attachmentService.getAttachmentsByFnum(this.displayedFnum);

      if (response.status) {
        this.attachments = response.attachments;
        this.$store.dispatch("attachment/setAttachmentsOfFnum", {
          fnum: [this.displayedFnum],
          attachments: this.attachments,
        });

        const categoriesResponse = await this.getAttachmentCategories();

        if (categoriesResponse) {
          this.categories = categoriesResponse;
        } else {
          this.categories = {};
        }
      } else {
        this.displayErrorMessage(
            this.translate("COM_EMUNDUS_ATTACHMENTS_ERROR_GETTING_ATTACHMENTS")
        );
      }

      if (addLoading === true) {
        this.loading = false;
      }
    },
    updateAttachment() {
      this.resetOrder();
      this.getAttachments();
      this.$modal.hide("edit");
      this.selectedAttachment = {};
    },
    updateStatus($event, selectedAttachment) {
      if (this.canUpdate) {
        if (this.attachments.length < 1) {
          return;
        }

        this.attachments.forEach((attachment, key) => {
          if (attachment.aid == selectedAttachment.aid) {
            this.resetOrder();
            this.attachments[key].is_validated = $event.target.value;

            let formData = new FormData();
            formData.append("fnum", this.displayedFnum);
            formData.append("user", this.$store.state.user.currentUser);
            formData.append("id", this.attachments[key].aid);
            formData.append("is_validated", this.attachments[key].is_validated);

            attachmentService
                .updateAttachment(formData)
                .then((response) => {
                  if (response && response.status === false) {
                    this.displayErrorMessage(response.msg);
                  }
                })
                .catch((error) => {
                  this.displayErrorMessage(error);
                });
          }
        });
      }
    },
    changePermission(permission, selectedAttachment) {
      if (this.canUpdate) {
        this.attachments.forEach((attachment, key) => {
          if (attachment.aid == selectedAttachment.aid) {
            this.resetOrder();
            this.attachments[key][permission] =
                this.attachments[key][permission] === "1" ? "0" : "1";

            let formData = new FormData();
            formData.append("fnum", this.displayedFnum);
            formData.append("user", this.$store.state.user.currentUser);
            formData.append("id", this.attachments[key].aid);
            formData.append(permission, this.attachments[key][permission]);

            attachmentService.updateAttachment(formData).then((response) => {
              if (!response.status) {
                this.displayErrorMessage(response.msg);
              }
            });
          }
        });
      } else {
        this.displayErrorMessage(
            this.translate("COM_EMUNDUS_ATTACHMENTS_UNAUTHORIZED_ACTION")
        );
      }
    },

    async setAccessRights() {
      if (!this.$store.state.user.rights[this.displayedFnum]) {
        const response = await userService.getAccessRights(
            this.$store.state.user.currentUser,
            this.displayedFnum
        );

        if (response.status === true) {
          this.$store.dispatch("user/setAccessRights", {
            fnum: this.displayedFnum,
            rights: response.rights,
          });
        }
      }

      this.canExport = this.$store.state.user.rights[this.displayedFnum]
          ? this.$store.state.user.rights[this.displayedFnum].canExport
          : false;
      this.canDelete = this.$store.state.user.rights[this.displayedFnum]
          ? this.$store.state.user.rights[this.displayedFnum].canDelete
          : false;
      this.canUpdate = this.$store.state.user.rights[this.displayedFnum]
          ? this.$store.state.user.rights[this.displayedFnum].canUpdate
          : false;
    },
    async exportAttachments() {
      if (this.canExport) {
        attachmentService
            .exportAttachments(
                this.displayedUser.id,
                this.displayedFnum,
                this.checkedAttachments
            )
            .then((response) => {
              if (response.data.status === true) {
                window.open(response.data.link, "_blank");
              } else {
                this.displayErrorMessage(response.data.msg);
              }
            });
      }
    },

    confirmDeleteAttachments() {
      if (this.canDelete) {
        let html =
            "<p>" +
            this.translate("CONFIRM_DELETE_SELETED_ATTACHMENTS") +
            "</p><br>";

        let list = "";
        this.checkedAttachments.forEach((aid) => {
          this.attachments.forEach((attachment) => {
            if (attachment.aid == aid) {
              list += attachment.value + ", ";
            }
          });
        });

        // remove last ", "
        list = list.substring(0, list.length - 2);
        html += "<p>" + list + "</p>";

        Swal.fire({
          title: this.translate("DELETE_SELECTED_ATTACHMENTS"),
          html: html,
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: "#3085d6",
          cancelButtonColor: "#d33",
          confirmButtonText: this.translate("JYES"),
          cancelButtonText: this.translate("JNO"),
          reverseButtons: true,
        }).then((result) => {
          if (result.value) {
            this.deleteAttachments();
          }
        });
      } else {
        this.displayErrorMessage(
            this.translate("YOU_NOT_HAVE_PERMISSION_TO_DELETE_ATTACHMENTS")
        );
      }
    },
    async deleteAttachments() {
      if (this.canDelete) {
        // remove all checked attachments from attachments array
        this.attachments = this.attachments.filter(
            (attachment) => !this.checkedAttachments.includes(attachment.aid)
        );

        // delete all checkedAttachments
        const response = await attachmentService.deleteAttachments(
            this.displayedFnum,
            this.displayedUser.id,
            this.checkedAttachments
        );

        if (response.status === true) {
          // Display tooltip deleted succesfully
        }
      } else {
        this.displayErrorMessage(
            this.translate("YOU_NOT_HAVE_PERMISSION_TO_DELETE_ATTACHMENTS")
        );
      }
    },

    // navigation functions
    changeFile(position) {
      this.loading = true;

      const oldFnumPosition = this.fnumPosition;
      this.displayedFnum = this.fnums[position];
      this.getFormProgress();
      this.attachments = [];
      this.$store.dispatch("attachment/setCheckedAttachments", []);
      this.setAccessRights();
      this.resetOrder();
      this.resetSearch();
      this.resetCategoryFilters();

      fileService.getFnumInfos(this.displayedFnum).then((response) => {
        if (response.status === true) {
          this.changeFileEvent.detail = {
            fnum: response.fnumInfos,
            next: position > oldFnumPosition ? true : false,
            previous: position < oldFnumPosition ? true : false,
          };

          document
              .querySelector(".com_emundus_vue")
              .dispatchEvent(this.changeFileEvent);
        } else {
          this.displayErrorMessage(response.msg);
        }
      });

      this.setDisplayedUser()
          .then(() => {
            this.getAttachments()
                .then(() => {
                  this.attachments.forEach((attachment) => {
                    attachment.show = true;
                  });
                  this.loading = false;
                })
                .catch((error) => {
                  this.displayErrorMessage(error);
                  this.loading = false;
                });
          })
          .catch((error) => {
            this.displayErrorMessage(error);
            this.loading = false;
          });
    },
    changeAttachment(position, reverse = false) {
      this.slideTransition = reverse ? "slide-fade-reverse" : "slide-fade";
      this.modalLoading = true;
      this.selectedAttachment = this.displayedAttachments[position];
      this.$store.dispatch(
          "attachment/setSelectedAttachment",
          this.selectedAttachment
      );

      setTimeout(() => {
        this.modalLoading = false;
      }, 500);
    },

    // Front methods
    searchInFiles() {
      this.attachments.forEach((attachment, index) => {
        // if attachment description contains the search term, show it
        // lowercase the search term to avoid case sensitivity
        if (
            attachment.description
                .toLowerCase()
                .includes(this.$refs["searchbar"].value.toLowerCase()) ||
            attachment.value
                .toLowerCase()
                .includes(this.$refs["searchbar"].value.toLowerCase())
        ) {
          this.attachments[index].show = true;
        } else {
          // remove attachments from checkedAttachment list
          this.checkedAttachments = this.checkedAttachments.filter(
              (aid) => aid !== attachment.aid
          );
          this.attachments[index].show = false;
        }
      });
    },
    resetSearch() {
      this.attachments.forEach((attachment, index) => {
        this.attachments[index].show = true;
      });
      this.$refs["searchbar"].value = "";
    },
    resetOrder() {
      this.sort = {
        last: "",
        order: "",
        orderBy: "",
      };
    },
    resetCategoryFilters() {
      if (this.$refs["categoryFilter"]) {
        this.$refs["categoryFilter"].value = "all";
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
      this.attachments.forEach((attachment) => {
        if (e.target.value == "all") {
          attachment.show = true;
        } else {
          if (attachment.category == e.target.value) {
            attachment.show = true;
          } else {
            // remove attachments from checkedAttachment list
            this.checkedAttachments = this.checkedAttachments.filter(
                (aid) => aid !== attachment.aid
            );
            attachment.show = false;
          }
        }
      });
    },
    updateAllCheckedAttachments(e) {
      if (e.target.checked) {
        // check all input that has class attachment-check and add them to the checkedAttachments array
        this.checkedAttachments = this.displayedAttachments.map(
            (attachment) => attachment.aid
        );
      } else {
        this.checkedAttachments = [];
      }

      this.$store.dispatch(
          "attachment/setCheckedAttachments",
          this.checkedAttachments
      );
    },
    updateCheckedAttachments(attachments) {
      // check that attachments is an array
      if (Array.isArray(attachments)) {
        this.checkedAttachments = attachments;
      } else {
        console.warn("updateCheckedAttachments() expects an array as argument");

        this.displayErrorMessage(
            "Something went wrong while updating the checked attachments"
        );
        this.checkedAttachments = [];
      }
    },

    // Modal methods
    openModal(attachment) {
      if (this.displayedUser.user_id && this.displayedFnum) {
        this.$modal.show("edit");
        this.selectedAttachment = attachment;
        this.$store.dispatch("attachment/setSelectedAttachment", attachment);
      }
    },
    closeModal() {
      this.$modal.hide("edit");
      this.selectedAttachment = {};
      this.$store.dispatch("attachment/setSelectedAttachment", {});
    },
    displayErrorMessage(msg) {
      Swal.fire({
        title: this.translate("ERROR"),
        text: msg,
        type: "error",
        confirmButtonColor: "#3085d6",
        confirmButtonText: this.translate("COM_EMUNDUS_ATTACHMENTS_CLOSE"),
      });
    },

    // Transition hooks
    beforeLeaveSlide(el) {
      if (this.slideTransition == "slide-fade") {
        el.style.transform = "translateX(-100%)";
      }

      el.setAttribute(
          "class",
          "modal-body " +
          this.slideTransition +
          "-leave-active " +
          this.slideTransition +
          "-leave-to"
      );
    },
  },
  computed: {
    displayedAttachments() {
      return this.attachments.filter((attachment) => {
        return (
            (attachment.show === true || attachment.show == undefined) &&
            attachment.can_be_viewed
        );
      });
    },
    fnumPosition() {
      return this.fnums.indexOf(this.displayedFnum);
    },
    selectedAttachmentPosition() {
      return this.displayedAttachments.indexOf(this.selectedAttachment);
    },
    attachmentPath() {
      return (
          this.$store.state.attachment.attachmentPath +
          this.displayedUser.user_id +
          "/" +
          this.selectedAttachment.filename
      );
    },
    thisAttachmentCategories() {
      let displayedCategories = {};
      if (Object.entries(this.categories).length > 0) {
        for (let category in this.categories) {
          for (let attachment in this.attachments) {
            // if the attachment category is the same as the category
            if (this.attachments[attachment].category == category) {
              // add the category to the displayedCategories object
              displayedCategories[category] = this.categories[category];
            }
          }
        }
      }

      return displayedCategories;
    },
  },
  watch: {
    "$store.state.global.anonyme": function () {
      this.canSee = !this.$store.state.global.anonyme;
    }
  }
};
</script>

<style lang='scss'>
#em-attachments {
  font-size: 14px;

  .head {
    width: 100%;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    margin-top: 1px;
    padding: 10px;
    min-height: 50px;
    background-color: var(--night-blue);

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
      width: 75px;

      > div {
        pointer-events: none;
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        color: transparent;
        transition: all 0.3s;

        span {
          height: 30px;
          width: 30px;
          display: flex;
          justify-content: center;
          align-items: center;
          margin: 0;
          opacity: 0;
        }

        &.active {
          span {
            color: var(--text-light-color);
            opacity: 1;
          }
          pointer-events: auto;
          cursor: pointer;
        }

        &:hover {
          border-radius: 4px;
          background-color: transparent;
          span {
            color: var(--primary-color);
          }
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
      .material-icons.search {
        position: absolute;
        top: 11px;
        left: 18px;
      }

      .material-icons.clear {
        position: absolute;
        top: 11px;
        right: 10px;
        cursor: pointer;
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

      > div,
      > select {
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
      transition: transform 0.6s;
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
      transition: all 0.3s;
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

  .table-wrapper {
    width: 100%;
    overflow-x: scroll;
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

    tr,
    th {
      height: 49px;
      background: transparent;
      background-color: transparent;
    }

    td,
    th {
      width: fit-content;
    }

    th.desc,
    td.desc {
      max-width: 250px;
      width: initial;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    th.status,
    td.status {
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

        .prev,
        .next {
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
    transition: all 0.5s ease;
  }
  .slide-fade-leave-active {
    transition: all 0.5s cubic-bezier(1, 0.5, 0.8, 1);
  }
  .slide-fade-enter {
    transform: translateX(100%);
  }
  .slide-fade-leave-to {
    transform: translateX(-100%);
    opacity: 0;
  }

  .slide-fade-reverse-enter-active {
    transition: all 0.5s ease;
  }
  .slide-fade-reverse-leave-active {
    transition: all 0.5s cubic-bezier(1, 0.8, 0.5, 1);
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
