<template>
    <div id="attachment-preview">
        <h2>Preview</h2>
    </div>
</template>

<script>
import attachment from '../services/attachment';

export default {
    props: {
        attachment: {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            attachmentType: ""
        }
    },
    mounted() {
        this.attachmentType = this.attachment.attachment_type;
        // this.loadAttachment();
    },
    methods: {
        loadAttachment() {
            attachment.loadAttachment(this.attachment.aid, this.attachmentType).then(response => {
                this.$refs.attachmentPreview.innerHTML = response.data;
            });
        }
    }
}
</script>

<style lang="scss" scoped>
#attachment-preview {
    height: 100%;
    width: 50%;
}
</style>