<template>
	<tr
		class="attachment-row"
		:class="{ checked: checkedAttachments.includes(attachment.aid) }"
		:key="attachment.aid"
	>
		<td v-if="columns.includes('check')">
			<input
				class="attachment-check"
				type="checkbox"
				@change="updateCheckedAttachments(attachment.aid)"
				:checked="checkedAttachments.includes(attachment.aid)"
			/>
		</td>
		<td v-if="columns.includes('name')" class="td-document" @click="openModal">
			<span v-if="!attachment.existsOnServer" class="material-icons-outlined warning file-not-found em-mr-16" :title="translate('COM_EMUNDUS_ATTACHMENTS_FILE_NOT_FOUND')">warning</span>
			<span :title="attachment.value">{{ attachment.value }}</span>
		</td>
		<td v-if="columns.includes('date')" class="date">{{ formattedDate(attachment.timedate) }}</td>
		<td v-if="columns.includes('desc')" class="desc"> {{ strippedHtml(attachment.upload_description) }}</td>
		<td v-if="columns.includes('category')" class="category">
			{{ category }}
		</td>
		<td v-if="columns.includes('status')"
			class="status valid-state"
			:class="{success: attachment.is_validated == 1,warning: attachment.is_validated == 2,error: attachment.is_validated == 0}"
		>
			<select @change="(e) => updateStatus(e)" :disabled="canUpdate === false ? true : false">
				<option value="1" :selected="attachment.is_validated == 1">{{ translate("VALID") }}</option>
				<option value="0" :selected="attachment.is_validated == 0">{{ translate("INVALID") }}</option>
				<option value="2" :selected="attachment.is_validated == 2">{{ translate("COM_EMUNDUS_ATTACHMENTS_WARNING") }}</option>
				<option value="-2" :selected="attachment.is_validated == -2 || attachment.is_validated === null">{{ translate("COM_EMUNDUS_ATTACHMENTS_WAITING") }}</option>
			</select>
		</td>
		<td v-if="canSee && columns.includes('user')">{{ getUserNameById(attachment.user_id) }}</td>
		<td v-if="canSee && columns.includes('modified_by')">{{ getUserNameById(attachment.modified_by) }}</td>
		<td class="date" v-if="columns.includes('modified')">{{ formattedDate(attachment.modified) }}</td>
		<td v-if="columns.includes('permissions')" class="permissions">
			<span v-if="attachment.profiles.length > 0"
				class="material-icons-outlined visibility-permission em-pointer"
				:class="{ active: attachment.can_be_viewed == '1' }"
				@click="changePermission('can_be_viewed', attachment)"
				:title="translate('COM_EMUNDUS_ATTACHMENTS_PERMISSION_VIEW')">visibility</span>
			<span v-if="attachment.profiles.length > 0"
				class="material-icons-outlined delete-permission em-pointer"
				:class="{ active: attachment.can_be_deleted == '1' }"
				@click="changePermission('can_be_deleted', attachment)"
				:title="translate('COM_EMUNDUS_ATTACHMENTS_PERMISSION_DELETE')">delete_outlined</span>
		</td>
    <td v-if="sync && columns.includes('sync')">
      <div v-if="attachment.sync > 0">
        <span
            v-if="attachment.sync_method == 'write' && !syncLoading"
            class="material-icons sync em-pointer"
            :class="{
              success: synchronizeState == 1,
              error: synchronizeState != 1,
            }"
            :title="translate('COM_EMUNDUS_ATTACHMENTS_SYNC_WRITE')"
            @click="synchronizeAttachments(attachment.aid)"
        >
          cloud_upload
        </span>
        <span
            v-else-if="attachment.sync_method == 'read' && !syncLoading"
            class="material-icons sync em-pointer"
            :class="{
              success: synchronizeState == 1,
              error: synchronizeState != 1,
            }"
            :title="translate('COM_EMUNDUS_ATTACHMENTS_SYNC_READ')"
            @click="synchronizeAttachments(attachment.aid)"
        >
          cloud_download
        </span>
        <div v-if="syncLoading" class="sync-loader em-loader"></div>
      </div>
    </td>
	</tr>
</template>

<script>
import mixin from "../../mixins/mixin.js";
import syncService from "../../services/sync.js";

export default {
	name: "AttachmentRow",
	props: {
		attachment: {
			type: Object,
			required: true,
		},
		checkedAttachmentsProp: {
			type: Array,
			required: true,
		},
		canUpdate: {
			type: Boolean,
			default: false,
		},
    sync: {
      type: Boolean,
      default: false,
		},
    canSee: {
      type: Boolean,
      default: true,
    },
    columns: {
      type: Array,
	    default() {
		    return ['name', 'date', 'desc', 'category', 'status', 'user', 'modified_by', 'modified', 'permissions', 'sync'];
	    }
    }
	},
	mixins: [mixin],
	data() {
		return {
			categories: {},
			category: "",
			checkedAttachments: [],
      synchronizeState: false,
      syncLoading: false,
		};
	},
	mounted() {
		this.categories = this.$store.state.attachment.categories;
		if (Object.entries(this.categories).length > 0) {
			this.category = this.categories[this.attachment.category] ? this.categories[this.attachment.category] : '';
		}

		this.checkedAttachments = this.checkedAttachmentsProp;

    if (this.sync) {
      this.getSynchronizeState(this.attachment.aid).then((response) => {
        this.synchronizeState = response;
      }).catch((error) => {
        this.synchronizeState = false;
      });
    }
  },
	methods: {
		updateCheckedAttachments(aid) {
			if (this.checkedAttachments.includes(aid)) {
				this.checkedAttachments.splice(this.checkedAttachments.indexOf(aid), 1);
			} else {
				this.checkedAttachments.push(aid);
			}

			this.$emit("update-checked-attachments", this.checkedAttachments);
		},
		openModal() {
			this.$emit("open-modal", this.attachment);
		},
		updateStatus(e) {
			if (this.canUpdate) {
				this.$emit("update-status", e, this.attachment);
			}
		},
		changePermission(permission, attachment) {
			if (this.canUpdate) {
				this.$emit("change-permission", permission, attachment);
			}
		},
    async getSynchronizeState(aid) {
      const response = await syncService.checkAttachmentsExists([aid]);

      if (response.status) {
        return response.data[0];
      }

      return false;
    },
    synchronizeAttachments(aid)
    {
      if (aid.length > 0) {
        this.syncLoading = true;
        syncService.synchronizeAttachments([aid]).then((response) => {
          if (response && response.status === false) {
            //
          } else {
            this.getSynchronizeState(aid).then((response) => {
              this.synchronizeState = response;
              this.syncLoading = false;
            }).catch((error) => {
              this.synchronizeState = false;
              this.syncLoading = false;
            });
          }
        });
      }
    },
	},
	watch: {
		"$store.state.attachment.checkedAttachments": function () {
			this.checkedAttachments = this.$store.state.attachment.checkedAttachments;
		},
    "$store.state.attachment.categories": function () {
      this.categories = this.$store.state.attachment.categories;
      this.category = this.categories[this.attachment.category] ? this.categories[this.attachment.category] : "";
    }
	},
};
</script>

<style lang="scss">
.attachment-row {
	border-bottom: 1px solid #e0e0e0;

	&:hover:not(.checked) {
		background-color: #f2f2f3 !important;
	}

	&.checked {
		background-color: #f0f6fd !important;
	}

	.valid-state {
		select {
			padding: 4px 8px;
			border-radius: 4px;
			background-color: var(--grey-bg-color) !important;
			color: var(--grey-color) !important;
			border: none;
			width: max-content;
		}

		select::-ms-expand {
			display: none !important;
		}

		&.warning {
			select {
				color: var(--warning-color) !important;
				background-color: var(--warning-bg-color) !important;
			}
		}

		&.success {
			select {
				color: var(--success-color) !important;
				background-color: var(--success-bg-color) !important;
			}
		}

		&.error {
			select {
				color: var(--error-color) !important;
				background-color: var(--error-bg-color) !important;
			}
		}
	}
	.permissions {
		.material-icons, .material-icons-outlined {
			margin: 0 10px;
			opacity: 0.3;

			&.active {
				opacity: 1;
			}
		}
	}

  .material-icons {
    cursor: pointer;

    &.success {
      color: var(--success-color);
    }

    &.error {
      color: var(--error-color);
    }
  }

	.td-document {
		max-width: 250px;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
		cursor: pointer;

		.warning.file-not-found {
			color: var(--error-color);
			transform: translate(10px, 3px);
		}
	}

  .sync-loader {
    width: 30px !important;
    height: 30px !important;
  }
}
</style>
