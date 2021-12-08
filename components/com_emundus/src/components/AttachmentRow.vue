<template>
  <tr class="attachment-row" :key="attachment.aid">
    <td>
      <input
        class="attachment-check"
        type="checkbox"
        @change="updateCheckedAttachments(attachment.aid)"
        :checked="checkedAttachments.includes(attachment.aid)"
      />
    </td>
    <td class="td-document" @click="openModal">
      <span>{{ attachment.value }}</span>
      <span v-if="!attachment.existsOnServer" class="material-icons warning file-not-found" :title="translate('COM_EMUNDUS_ATTACHMENTS_FILE_NOT_FOUND')">
        warning
      </span>    
    </td>
    <td class="date">{{ formattedDate(attachment.timedate) }}</td>
    <td class="desc">{{ attachment.description }}</td>
    <td class="category" v-if="categories !== null">
      {{ categories[attachment.category] }}
    </td>
    <td
      class="status valid-state"
      :class="{
        success: attachment.is_validated == 1,
        warning: attachment.is_validated == 2,
        error: attachment.is_validated == 0,
      }"
    >
      <select @change="(e) => updateStatus(e)">
        <option value="1" :selected="attachment.is_validated == 1">
          {{ translate("VALID") }}
        </option>
        <option value="0" :selected="attachment.is_validated == 0">
          {{ translate("INVALID") }}
        </option>
        <option value="2" :selected="attachment.is_validated == 2">
          {{ translate("COM_EMUNDUS_ATTACHMENTS_WARNING") }}
        </option>
        <option
          value="-2"
          :selected="
            attachment.is_validated == -2 || attachment.is_validated === null
          "
        >
          {{ translate("COM_EMUNDUS_ATTACHMENTS_WAITING") }}
        </option>
      </select>
    </td>
    <td>{{ getUserNameById(attachment.user_id) }}</td>
    <td>{{ getUserNameById(attachment.modified_by) }}</td>
    <td class="date">{{ formattedDate(attachment.modified) }}</td>
  </tr>
</template>

<script>
import mixin from "../mixins/mixin.js";

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
  },
  mixins: [mixin],
  data() {
    return {
      categories: {},
      checkedAttachments: [],
    };
  },
  mounted() {
    this.categories = this.$store.state.attachment.categories;
    if (Object.entries(this.categories).length < 1) {
      this.categories = this.getCategories();
    }

    this.checkedAttachments = this.checkedAttachmentsProp;
  },
  methods: {
    async getCategories() {
      this.categories = await this.getAttachmentCategories();
    },
    updateCheckedAttachments(aid) {
      if (this.checkedAttachments.includes(aid)) {
        this.checkedAttachments = this.checkedAttachments.filter(
          (attachment) => attachment !== aid
        );
      } else {
        this.checkedAttachments.push(aid);
      }

      this.$emit("update-checked-attachments", this.checkedAttachments);
    },
    openModal() {
      this.$emit("open-modal", this.attachment);
    },
    updateStatus(e) {
      this.$emit("update-status", e, this.attachment);
    },
  },
};
</script>

<style lang="scss">
.attachment-row {
    border-bottom: 1px solid #e0e0e0;

    &:hover:not(.checked) {
      background-color: #F2F2F3;
    }
    
    &.checked {
      background-color: #F0F6FD;
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
}

</style>