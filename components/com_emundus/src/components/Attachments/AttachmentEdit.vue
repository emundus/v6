<template>
  <div id="attachment-edit">
    <div class="wrapper">
      <h2 class="title">{{ attachment.value }}</h2>
      <div class="editable-data">
        <div class="input-group">
          <label for="description">{{ translate("DESCRIPTION") }}</label>
          <textarea
              name="description"
              id="description"
              type="text"
              v-model="attachmentDescription"
              :disabled="!canUpdate"
              @focusout="saveChanges"
          >
					</textarea>
        </div>

        <div
            class="input-group valid-state"
            :class="{
			    	success: attachmentIsValidated == 1,
			    	warning: attachmentIsValidated == 2,
			    	error: attachmentIsValidated == 0,
			    }"
        >
          <label for="status">{{ translate("COM_EMUNDUS_ATTACHMENTS_CHECK") }}</label>
          <select
              name="status"
              v-model="attachmentIsValidated"
              @change="updateAttachmentStatus"
              :disabled="!canUpdate"
          >
            <option value=1>{{ translate("VALID") }}</option>
            <option value=0>{{ translate("INVALID") }}</option>
            <option value=2>{{ translate("COM_EMUNDUS_ATTACHMENTS_WARNING") }}</option>
            <option value=-2>{{ translate("COM_EMUNDUS_ATTACHMENTS_WAITING") }}</option>
          </select>
        </div>
        <div class="input-group" v-if="canUpdate">
          <label for="replace">{{ translate("COM_EMUNDUS_ATTACHMENTS_REPLACE") }}</label>
          <input
              type="file"
              name="replace"
              @change="updateFile"
              :accept="allowedType"
          />
        </div>
        <div class="input-group" v-if="attachment.profiles && attachment.profiles.length > 0">
          <label for="can_be_viewed">{{ translate("COM_EMUNDUS_ATTACHMENTS_CAN_BE_VIEWED") }}</label>
          <input
              type="checkbox"
              name="can_be_viewed"
              v-model="attachmentCanBeViewed"
              :disabled="!canUpdate"
              @click="saveChanges"
          />
        </div>
        <div class="input-group" v-if="attachment.profiles && attachment.profiles.length > 0">
          <label for="can_be_deleted">{{ translate("COM_EMUNDUS_ATTACHMENTS_CAN_BE_DELETED") }}</label>
          <input
              type="checkbox"
              name="can_be_deleted"
              v-model="attachmentCanBeDeleted"
              :disabled="!canUpdate"
              @click="saveChanges"
          />
        </div>
      </div>
      <div class="non-editable-data">
        <div>
          <span>{{ translate("COM_EMUNDUS_ATTACHMENTS_SEND_DATE") }}</span>
          <span class="em-text-align-right">{{ formattedDate(attachment.timedate) }}</span>
        </div>
        <div v-if="attachment.user_id && canSee">
          <span>{{ translate("COM_EMUNDUS_ATTACHMENTS_UPLOADED_BY") }}</span>
          <span class="em-text-align-right">{{ getUserNameById(attachment.user_id) }}</span>
        </div>
        <div v-if="attachment.category">
          <span>{{ translate("COM_EMUNDUS_ATTACHMENTS_CATEGORY") }}</span>
          <span class="em-text-align-right">{{ this.categories[attachment.category] }}</span>
        </div>
        <div v-if="attachment.modified_by && canSee">
          <span>{{ translate("COM_EMUNDUS_ATTACHMENTS_MODIFIED_BY") }}</span>
          <span class="em-text-align-right">{{ getUserNameById(attachment.modified_by) }}</span>
        </div>
        <div v-if="attachment.modified">
          <span>{{ translate("COM_EMUNDUS_ATTACHMENTS_MODIFICATION_DATE") }}</span>
          <span class="em-text-align-right">{{ formattedDate(attachment.modified) }}</span>
        </div>
        <!-- TODO: add file size -->
      </div>
    </div>

    <div class="em-w-100 em-flex-row em-flex-space-between">
      <div id="toggle-display">
			  <span v-if="displayed" class="material-icons-outlined displayed em-pointer" @click="toggleDisplay(false)">
				  chevron_right
			  </span>
        <span v-else class="material-icons-outlined not-displayed em-pointer" @click="toggleDisplay(true)">
				  menu_open
			  </span>
      </div>
    </div>
    <div v-if="error" class="error-msg">{{ errorMessage }}</div>
  </div>
</template>

<script>
import attachmentService from "../../services/attachment";
import mixin from "../../mixins/mixin.js";

export default {
  name: "AttachmentEdit",
  props: {
    fnum: {
      type: String,
      required: true,
    },
    isDisplayed: {
      type: Boolean,
      default: true
    }
  },
  mixins: [mixin],
  data() {
    return {
      displayed: true,
      attachment: {},
      categories: {},
      file: null,
      canUpdate: false,
      canSee: true,
      error: false,
      errorMessage: "",
      attachmentIsValidated: "-2",
      attachmentCanBeViewed: false,
      attachmentCanBeDeleted: false,
      attachmentDescription: "",
    };
  },
  mounted() {
    this.displayed = this.isDisplayed;
    this.canUpdate = this.$store.state.user.rights[this.fnum] ? this.$store.state.user.rights[this.fnum].canUpdate : false;
    this.canSee = !this.$store.state.global.anonyme;
    this.attachment = this.$store.state.attachment.selectedAttachment;
    this.categories = this.$store.state.attachment.categories;

    this.attachmentCanBeViewed = this.attachment.can_be_viewed == "1";
    this.attachmentCanBeDeleted = this.attachment.can_be_deleted == "1";
    this.attachmentDescription = this.attachment.upload_description != null ? this.attachment.upload_description : '';
    this.attachmentIsValidated = this.attachment.is_validated;
  },
  methods: {
    async saveChanges() {
      let formData = new FormData();

      const canBeViewed = this.attachmentCanBeViewed ? "1" : "0";
      const canBeDeleted = this.attachmentCanBeDeleted ? "1" : "0";

      formData.append("fnum", this.fnum);
      formData.append("user", this.$store.state.user.currentUser);
      formData.append("id", this.attachment.aid);
      formData.append("description", this.attachmentDescription);
      formData.append("is_validated", this.attachmentIsValidated);
      formData.append("can_be_viewed", canBeViewed);
      formData.append("can_be_deleted", canBeDeleted);

      if (this.file) {
        formData.append("file", this.file);
      }

      const response = await attachmentService.updateAttachment(formData);

      if (response.status.update) {
        this.attachment.modified_by = this.$store.state.user.currentUser;
        this.attachment.upload_description = this.attachmentDescription != null ? this.attachmentDescription : '';
        this.attachment.is_validated = this.attachmentIsValidated;
        this.attachment.can_be_viewed = this.attachmentCanBeViewed;
        this.attachment.can_be_deleted = this.attachmentCanBeDeleted;

        this.$store.dispatch("attachment/updateAttachmentOfFnum", {
          fnum: this.fnum,
          attachment: this.attachment,
        });

        if (response.status.file_update) {
          // need to update file preview
          const data = await attachmentService.getPreview(
              this.$store.state.user.displayedUser,
              this.attachment.filename
          );

          // store preview data
          this.$store.dispatch("attachment/setPreview", {
            preview: data,
            id: this.attachment.aid,
          });
        }
      } else {
        this.showError(response.msg);
      }
    },
    updateFile(event) {
      this.file = event.target.files[0];
      this.saveChanges();
    },
    updateAttachmentStatus(event) {
      this.attachmentIsValidated = event.target.value;
      this.saveChanges();
    },
    showError(error) {
      this.error = true;
      this.errorMessage = error;

      setTimeout(() => {
        this.error = false;
        this.errorMessage = "";
      }, 3000);
    },
    toggleDisplay(displayed) {
      this.displayed = displayed;
      this.$emit('update-displayed', this.displayed);
    }
  },
  computed: {
    allowedType() {
      let allowed_type = "";

      if (this.attachment.filename) {
        allowed_type = "." + this.attachment.filename.split(".").pop();
      }

      return allowed_type;
    },
  },
  watch: {
    "$store.state.attachment.selectedAttachment": function () {
      // check if selected attachment is not an empty object
      const keys = Object.keys(this.$store.state.attachment.selectedAttachment);

      if (keys.length > 0) {
        this.attachment = this.$store.state.attachment.selectedAttachment;
      }
    },
  },
};
</script>

<style lang="scss">
#attachment-edit {
  padding: 16px 16px 16px 10px;
  height: 100%;
  float: right;
  border-left: 1px solid var(--border-color);
  position: relative;

  .error-msg {
    position: absolute;
    margin: 10px 10px;
    top: 0;
    left: 0;
    width: calc(100% - 20px);
    background-color: var(--error-bg-color);
    color: var(--error-color);
    font-size: 1.2em;
    padding: 16px;
  }

  .wrapper {
    width: 100%;
    height: 100%;

    .title {
      margin-bottom: 16px;
    }
  }

  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: flex-start;

  .editable-data {
    width: 100%;
    overflow: hidden;

    h2 {
      text-overflow: ellipsis;
      overflow: hidden;
    }

    label {
      font-size: 10px;
      font-weight: 400 !important;
      color: var(--grey-color);
    }

    textarea {
      border-radius: 0;
      border-color: transparent;
      background-color: var(--grey-bg-color);

      &:hover,
      &:focus {
        box-shadow: none;
      }
    }

    select {
      width: 100%;
      height: fit-content;
      padding: 16px 8px;
      border-radius: 0;
    }
  }

  .non-editable-data {
    width: 100%;
    margin-top: 16px;

    div {
      width: 100%;
      display: flex;
      flex-direction: row;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid var(--border-color);
      padding: 8px 0;

      span:first-of-type {
        color: var(--grey-color);
      }

      &:last-child {
        border-bottom: none;
      }
    }
  }

  .actions {
    align-self: flex-end;
    margin-right: 20px;

    button {
      transition: all 0.3s;
      padding: var(--em-coordinator-vertical) var(--em-coordinator-horizontal);
    }
  }

  .input-group {
    margin-top: 10px;
    display: flex;
    flex-direction: column;

    [type="checkbox"] {
      width: fit-content;
    }

    input {
      height: fit-content !important;
    }
  }

  .valid-state {
    select {
      padding: 4px 8px;
      border-radius: 4px;
      background-color: var(--grey-bg-color);
      color: var(--grey-color);
      border: none;
      width: max-content;
    }

    select::-ms-expand {
      display: none !important;
    }

    &.warning {
      select {
        color: var(--warning-color);
        background-color: var(--warning-bg-color);
      }
    }

    &.success {
      select {
        color: var(--success-color);
        background-color: var(--success-bg-color);
      }
    }

    &.error {
      select {
        color: var(--error-color);
        background-color: var(--error-bg-color);
      }
    }
  }

  #toggle-display {
    .not-displayed {
      position: absolute;
      bottom: 0;
      right: 15px;
      padding: 10px;
      background: white;
      border-top-left-radius: 4px;
      border: 1px solid #ececec;
    }
  }
}
</style>
