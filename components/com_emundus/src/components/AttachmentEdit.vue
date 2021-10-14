<template>
    <div id="attachment-edit">
        <h2>{{ attachment.filename }}</h2>
        <div class="editableData"> 
            <label for="description">DESCRIPTION</label>
            <input name="description" type="text" v-model="description"/>

            <label for="status">FILE_STATUS</label>
            <select name="status" v-model="is_validated">
                <option value="">Indéfini</option>
                <option value=1>Valide</option>
                <option value=-2>Non Valide</option>
            </select>
        </div>
        <div class="actions">
            <button @click="$emit('closeModal')" class="btn close-btn">CLOSE</button>
            <button @click="saveChanges" class="btn save-btn">SAVE</button>
        </div>
    </div>
</template>

<script>
import attachment from '../services/attachment';

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