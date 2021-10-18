<template>
    <div id="attachment-edit">
        <div class="editableData"> 
            <h2>{{ attachment.filename }}</h2>
            <div class="input-group">
                <label for="description">DESCRIPTION</label>
                <input name="description" type="text" v-model="attachment.description"/>
            </div>

            <div class="input-group">
            <label for="status">FILE_STATUS</label>
                <select name="status" v-model="attachment.is_validated">
                    <option value=0>Indéfini</option>
                    <option value=1>Validé</option>
                    <option value=-2>Invalide</option>
                </select>
            </div>
        </div>
        <div class="actions">
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