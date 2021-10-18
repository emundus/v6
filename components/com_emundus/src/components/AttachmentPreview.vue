<template>
    <div ref="a-preview" id="attachment-preview" :class="{'overflow-x': overflowX, 'overflow-y': overflowY}">
    </div>
</template>

<script>
import attachmentService from '../services/attachment';

export default {
    data() {
        return {
            attachment: this.$store.state.attachment.selectedAttachment,
            preview: '',
            overflowX: false,
            overflowY: false,
        }
    },
    mounted() {
        this.attachment = this.$store.state.attachment.selectedAttachment;
        this.getPreview();
    },
    methods: {
        async getPreview() {
            let data;
            if (!this.$store.state.attachment.previews[this.attachment.aid]) {
                data = await attachmentService.getPreview(this.$store.state.user.displayedUser, this.attachment);

                // store preview data
                this.$store.commit('attachment/setPreview', {preview: data, id: this.attachment.aid});
            } else {
                data = this.$store.state.attachment.previews[this.attachment.aid];
            }

            if (data.status) {
                this.preview = data.content;
                this.overflowX = data.overflowX; 
                this.overflowY = data.overflowY;

                if (this.$refs['a-preview'].shadowRoot === null)  {
                    this.$refs['a-preview'].attachShadow({mode: 'open'});
                }
            } else {
                this.overflowX = false; 
                this.overflowY = false;
                this.preview = '';
            }

            this.$refs['a-preview'].shadowRoot.innerHTML = this.preview != null ? this.preview : '';
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

    &.overflow-x {
        overflow-x: scroll;
    }

    &.overflow-y {
        overflow-y: scroll;
    }
}
</style>