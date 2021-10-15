<template>
    <div id="attachment-preview">
        <div v-html="preview"></div>
    </div>
</template>

<script>
import attachmentService from '../services/attachment';

export default {
    data() {
        return {
            attachment: {},
            preview: '',
        }
    },
    mounted() {
        this.attachment = this.$store.state.attachment.selectedAttachment;
        this.getPreview();
    },
    methods: {
        async getPreview() {
            this.preview = await attachmentService.getPreview(this.$store.state.user.displayedUser, this.attachment);
        }
    }
}
</script>

<style lang="scss" scoped>
#attachment-preview {
    height: 100%;
    width: 50%;

    div {
        height: 100%;
        margin: auto;
        display: flex;
        justify-content: center;
        align-items: center;
    }
}
</style>