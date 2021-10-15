<template>
    <div id="attachment-edit">
        <div class="editableData"> 
            <h2>{{ attachment.filename }}</h2>
            <div class="input-group">
                <label for="description">DESCRIPTION</label>
                <input name="description" type="text" v-model="description"/>
            </div>

            <div class="input-group">
            <label for="status">FILE_STATUS</label>
                <select name="status" v-model="is_validated">
                    <option value=0>Indéfini</option>
                    <option value=1>Validé</option>
                    <option value=-2>Invalide</option>
                </select>
            </div>
        </div>
        <div class="actions">
            <button @click="$emit('closeModal')" class="btn close-btn">CLOSE</button>
            <button @click="saveChanges" class="btn save-btn">SAVE</button>
        </div>
    </div>
</template>

<script>
import attachment from '../services/attachment';
import moment from 'moment';

export default {
    name: 'AttachmentEdit',
    props: {
        user: {
            type: String,
            required: true
        },
        fnum: {
            type: String,
            required: true
        },
        attachment: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            description: this.attachment.description,
            is_validated: this.attachment.is_validated
        }
    },
    methods: {
        async saveChanges() {
            const attachment_data = {
                id: this.attachment.aid,
                description: this.description,
                is_validated: this.is_validated
            };

            const response = await attachment.updateAttachment(this.fnum, this.user, attachment_data);
            this.$emit('saveChanges', attachment_data);
        }
    }
}
</script>

<style lang="scss" scoped>
#attachment-edit {
    margin : 0 10px;
    padding: 10px;
    height: 100%;
    width: 50%;
    float: right;

    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: flex-start;

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