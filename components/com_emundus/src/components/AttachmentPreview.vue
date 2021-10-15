<template>
    <div id="attachment-preview">
        <div v-html="preview"></div>
    </div>
</template>

<script>
import attachmentService from '../services/attachment';

export default {
    props: {
        attachment: {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            preview: '',
        }
    },
    mounted() {
        this.getPreview();
    },
    methods: {
        getPreview() {
            attachmentService.getPreview(this.$store.state.user.currentUser, this.attachment).then(response => {
                this.preview = response.data;
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