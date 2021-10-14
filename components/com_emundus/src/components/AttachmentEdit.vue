<template>
    <div id="attachment-edit">
        <h2>{{ attachment.filename }}</h2>
        <div class="editableData"> 
            <label for="description">DESCRIPTION</label>
            <input name="description" type="text" :value="attachment.description"/>

            <label for="status">FILE_STATUS</label>
            <select name="status" value="attachment.is_validated">
                <option value="">Indéfini</option>
                <option value="valid">Valide</option>
                <option value="invalid">Non Valide</option>
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
        attachment: {
            type: Object,
            required: true
        }
    },
    methods: {
        async saveChanges() {
            const response = await attachment.updateAttachment(this.attachment);
            this.$emit('saveChanges', this.attachment);
        }
    }
}
</script>