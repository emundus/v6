<template>
    <div ref="a-preview" id="attachment-preview" :class="{'overflow': useShadow}">
    </div>
</template>

<script>
import attachmentService from '../services/attachment';

export default {
    data() {
        return {
            attachment: this.$store.state.attachment.selectedAttachment,
            preview: '',
            useShadow: false
        }
    },
    mounted() {
        this.attachment = this.$store.state.attachment.selectedAttachment;
        this.getPreview();
    },
    methods: {
        async getPreview() {
            const data = await attachmentService.getPreview(this.$store.state.user.displayedUser, this.attachment);

            if (data.status) {
                this.preview = data.content;
                this.useShadow = data.useShadow;
                if (this.$refs['a-preview'].shadowRoot === null)  {
                    this.$refs['a-preview'].attachShadow({mode: 'open'});
                }
            } else {
                this.useShadow = false; 
                this.preview = '';
            }
            this.$refs['a-preview'].shadowRoot.innerHTML = this.preview;
        }
    },
    watch: {
        '$store.state.attachment.selectedAttachment': function() {
            // check if selected attchment is not an empty object
            if (Object.keys(this.$store.state.attachment.selectedAttachment).length !== 0) {
                this.attachment = this.$store.state.attachment.selectedAttachment;
                this.getPreview();
            }
        }
    }
}
</script>

<style lang="scss" scoped>
#attachment-preview {
    height: 100%;
    width: 50%;
    overflow: hidden;

    &.overflow {
        overflow-y: scroll;
        overflow-x: hidden; 
    }
}
</style>