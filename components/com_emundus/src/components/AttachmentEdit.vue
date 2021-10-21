<template>
    <div id="attachment-edit">
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
                    <option value=0>Indéfini</option>
                    <option value=1>Validé</option>
                    <option value=-2>Invalide</option>
                </select>
            </div>
        </div>
        <div class="non-editable-data">
            <div>
                <span>{{ $t('attachments.send_date') }}</span>
                <span>{{ attachment.timedate }}</span>
            </div>
            <div>
                <span>{{ $t('attachments.modified_by') }}</span>
                <span>{{ attachment.modified_by }}</span>
            </div>
            <div>
                <span>{{ $t('attachments.modification_date') }}</span>
                <span>{{ attachment.modified }}</span>
            </div>
            <div>
                <span>{{ $t('attachments.file_size') }}</span>
                <span> ... kb </span>
            </div>
        </div>
        <div class="actions">
            <button @click="saveChanges" class="btn save-btn">{{ $t('save') }}</button>
        </div>
    </div>
</template>

<script>
import attachment from '../services/attachment';

export default {
    name: 'AttachmentEdit',
    props: {
        fnum: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            attachment: {},
        }
    },
    mounted() {
        this.attachment = this.$store.state.attachment.selectedAttachment;
    },
    methods: {
        async saveChanges() {
            const attachment_data = {
                id: this.attachment.aid,
                description: this.attachment.description,
                is_validated: this.attachment.is_validated
            };

            const response = await attachment.updateAttachment(this.fnum, this.$store.state.user.currentUser, attachment_data);

            if (response.status) {
                this.$store.commit('attachment/updateAttachmentOfFnum', {
                    fnum: this.fnum,
                    attachment: this.attachment
                });
            }
            this.$emit('saveChanges', attachment_data);
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
            font-weight: 400;
            color: var(--grey-color);
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