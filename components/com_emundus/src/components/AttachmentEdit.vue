<template>
    <div id="attachment-edit">
        <div class="wrapper">
            <h2 class="title">{{ attachment.value }}</h2>
            <div class="editable-data">
                <div class="input-group">
                    <label for="description">{{ translate('DESCRIPTION') }} </label>
                    <textarea name="description" type="text" v-model="attachment.description" :disabled="!canUpdate">
                    </textarea>
                </div>

                <div class="input-group">
                    <label for="status">{{ translate('COM_EMUNDUS_ATTACHMENTS_CHECK') }}</label>
                    <select name="status" v-model="attachment.is_validated" :disabled="!canUpdate">
                        <option value=-2> {{ translate('COM_EMUNDUS_ATTACHMENTS_WAITING') }} </option>
                        <option value=2> {{ translate('COM_EMUNDUS_ATTACHMENTS_WARNING') }}</option>
                        <option value=1> {{ translate('VALID') }} </option>
                        <option value=0> {{ translate('INVALID') }} </option>
                    </select>
                </div>
                <div class="input-group" v-if="canUpdate">
                    <label for="replace"> {{ translate('COM_EMUNDUS_ATTACHMENTS_REPLACE') }}</label>
                    <input type="file" name="replace" @change="updateFile" :accept="allowedType">
                </div>
            </div>
            <div class="non-editable-data">
                <div>
                    <span>{{ translate('COM_EMUNDUS_ATTACHMENTS_SEND_DATE') }}</span>
                    <span>{{ formattedDate(attachment.timedate) }}</span>
                </div>
                <div v-if="attachment.user_id">
                    <span>{{ translate('COM_EMUNDUS_ATTACHMENTS_UPLOADED_BY') }}</span>
                    <span>{{ getUserNameById(attachment.user_id) }}</span>
                </div>
                <div v-if="attachment.category">
                    <span>{{ translate('COM_EMUNDUS_ATTACHMENTS_CATEGORY') }}</span>
                    <span>{{ translate(attachment.category) }}</span>
                </div>
                <div v-if="attachment.modified_by">
                    <span>{{ translate('COM_EMUNDUS_ATTACHMENTS_MODIFIED_BY') }}</span>
                    <span>{{ getUserNameById(attachment.modified_by) }}</span>
                </div>
                <div v-if="attachment.modified">
                    <span>{{ translate('COM_EMUNDUS_ATTACHMENTS_MODIFICATION_DATE') }}</span>
                    <span>{{ formattedDate(attachment.modified) }}</span>
                </div>
                <!-- TODO: add file size -->
                <!-- <div v-if="attachment.file_size">
                    <span>{{ translate('FILE_SIZE') }}</span>
                    <span> ... kb </span>
                </div> -->
            </div>
        </div>
        <div class="actions">
            <button v-if="canUpdate" @click="saveChanges" class="btn-primary-vue">{{ translate('COM_EMUNDUS_ATTACHMENTS_SAVE') }}</button>
        </div>

        <div v-if="error" class="error">{{ errorMessage }}</div>
    </div>
</template>

<script>
import attachmentService from '../services/attachment';
import mixin from '../mixins/mixin.js';

export default {
    name: 'AttachmentEdit',
    props: {
        fnum: {
            type: String,
            required: true
        }
    },
    mixins: [mixin],
    data() {
        return {
            attachment: {},
            file: null,
            canUpdate: false,
            error: false,
            errorMessage: ''
        }
    },
    mounted() {
        this.canUpdate = this.$store.state.user.rights[this.fnum] ? this.$store.state.user.rights[this.fnum].canUpdate : false;
        this.attachment = this.$store.state.attachment.selectedAttachment;
    },
    methods: {
        async saveChanges() {
            let formData = new FormData();
            formData.append('fnum', this.fnum);
            formData.append('user', this.$store.state.user.currentUser);
            formData.append('id', this.attachment.aid);
            formData.append('description', this.attachment.description);
            formData.append('is_validated', this.attachment.is_validated);

            if (this.file) {
                formData.append('file', this.file);
            }

            const response = await attachmentService.updateAttachment(formData);

            if (response.status.update) {
                this.attachment.modified_by = this.$store.state.user.currentUser;

                this.$store.dispatch('attachment/updateAttachmentOfFnum', {
                    fnum: this.fnum,
                    attachment: this.attachment
                });

                if (response.status.file_update) {
                    // need to update file preview
                    const data = await attachmentService.getPreview(this.$store.state.user.displayedUser, this.attachment.filename);

                    // store preview data
                    this.$store.dispatch('attachment/setPreview', {preview: data, id: this.attachment.aid});
                }

                this.$emit('saveChanges');
            } else {
                this.showError(response.msg);
            }
        },
        updateFile(event) {
            this.file = event.target.files[0];
        },
        showError(error) {
            this.error = true;
            this.errorMessage = error;

            setTimeout(() => {
                this.error = false;
                this.errorMessage = '';
            }, 3000);
        }
    },
    computed: {
        allowedType() {
            let allowed_type = '';

            if (this.attachment.filename) {
                allowed_type = '.' + this.attachment.filename.split('.').pop();
            }

            return allowed_type;
        }
    },
    watch: {
        '$store.state.attachment.selectedAttachment': function() {
            // check if selected attchment is not an empty object
            if (Object.keys(this.$store.state.attachment.selectedAttachment).length !== 0) {
                this.attachment = this.$store.state.attachment.selectedAttachment;
            }
        }
    }
}
</script>

<style lang="scss" scoped>
#attachment-edit {
    padding: 16px 16px 16px 10px;
    height: 100%;
    width: 40%;
    float: right;
    border-left: 1px solid var(--border-color);
    position: relative;

    .error {
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

            &:hover, &:focus {
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

            span:first-of-type{
                color: var(--grey-color);
            }

            &:last-child {
                border-bottom: none;
            }
        }
    }

    .actions {
        align-self: flex-end;

        button {
            transition: all .3s;
            padding: 8px 12px;

            &:last-of-type {
                margin-left: 10px;
            }
        }
    }

    .input-group {
        margin-top: 10px;

        display: flex;
        flex-direction: column;
    }
}
</style>
