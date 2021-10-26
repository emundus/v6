<template>
    <div id="attachment-edit">
        <div class="wrapper">
            <div class="editable-data"> 
                <h2>{{ attachment.value }}</h2>

                <div class="input-group">
                    <label for="description">{{ $t('attachments.desc') }} </label>
                    <textarea name="description" type="text" v-model="attachment.description">
                    </textarea>
                </div>

                <div class="input-group">
                    <label for="status">{{ $t('attachments.status') }}</label>
                    <select name="status" v-model="attachment.is_validated">
                        <option value=0> {{ $t('attachments.validation_states.0') }} </option>
                        <option value=1> {{ $t('attachments.validation_states.1') }} </option>
                        <option value=-2> {{ $t('attachments.validation_states["-2"]') }} </option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="replace"> {{ $t('replace') }}</label>
                    <input type="file" name="replace" @change="updateFile" :accept="allowedType">
                </div>
            </div>
            <div class="non-editable-data">
                <div>
                    <span>{{ $t('attachments.send_date') }}</span>
                    <span>{{ formattedDate(attachment.timedate) }}</span>
                </div>
                <div>
                    <span>{{ $t('attachments.modified_by') }}</span>
                    <span>{{ getUserNameById(attachment.modified_by) }}</span>
                </div>
                <div>
                    <span>{{ $t('attachments.modification_date') }}</span>
                    <span>{{ formattedDate(attachment.modified) }}</span>
                </div>
                <div v-if="attachment.file_size">
                    <span>{{ $t('attachments.file_size') }}</span>
                    <span> ... kb </span>
                </div>
            </div>
        </div>
        <div class="actions">
            <button @click="saveChanges" class="btn save-btn">{{ $t('save') }}</button>
        </div>
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
            file: null
        }
    },
    mounted() {
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
                this.$store.dispatch('attachment/updateAttachmentOfFnum', {
                    fnum: this.fnum,
                    attachment: this.attachment
                });
            }

            if (response.status.file_update) {
                // need to update file preview
                const data = await attachmentService.getPreview(this.$store.state.user.displayedUser, this.attachment.filename);

                // store preview data
                this.$store.dispatch('attachment/setPreview', {preview: data, id: this.attachment.aid});
            }

            this.$emit('saveChanges');
        },
        updateFile(event) {
            this.file = event.target.files[0];
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

    .wrapper {
        width: 100%;
        height: 100%;
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