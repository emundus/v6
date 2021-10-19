<template>
    <div ref="a-preview" id="attachment-preview" :class="{'overflow-x': overflowX, 'overflow-y': overflowY, 'sheet': parser == 'sheet'}">
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
            parser: ''
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
                data = await attachmentService.getPreview(this.$store.state.user.displayedUser, this.attachment.filename);

                // store preview data
                this.$store.commit('attachment/setPreview', {preview: data, id: this.attachment.aid});
            } else {
                data = this.$store.state.attachment.previews[this.attachment.aid];
            }

            if (data.status) {
                this.preview = data.content;
                this.overflowX = data.overflowX; 
                this.overflowY = data.overflowY;
                this.parser = data.parser;

                if (this.$refs['a-preview'].shadowRoot === null)  {
                    this.$refs['a-preview'].attachShadow({mode: 'open'});
                }
            } else {
                this.overflowX = false; 
                this.overflowY = false;
                this.preview = '';
            }

            this.$refs['a-preview'].shadowRoot.innerHTML = this.preview != null ? this.preview : '';

            if (this.parser == 'sheet') {
                this.addSheetStyles();
            }
        },
        addSheetStyles() {
            // get div elements of first level
            const pages = this.$refs['a-preview'].shadowRoot.querySelectorAll('div');
            const navigation = this.$refs['a-preview'].shadowRoot.querySelector('.navigation');

            navigation.style.display = 'flex';
            navigation.style.flexDirection = 'row';
            navigation.style.justifyContent = 'flex-start';
            navigation.style.alignItems = 'center';

            pages.forEach((div, key) => {
                div.style.width = "fit-content";
                div.style.margin = "20px auto";
                div.style.boxShadow = "0px 0px 10px rgba(0, 0, 0, 0.1)";

                if (key > 0) {
                    div.style.display = "none";
                }
            });

            navigation.querySelectorAll('li').forEach((li, li_key) => {
                li.style.margin = "0 10px";
                li.style.listStyleType = "none";

                li.addEventListener('click', () => {
                    pages.forEach((div, div_key) => {
                        if (div_key == li_key) {
                            div.style.display = "block";
                        } else {
                            div.style.display = "none";
                        }
                    });
                });
            });
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
    width: 60%;
    overflow: hidden;

    &.overflow-x {
        overflow-x: auto;
    }

    &.overflow-y {
        overflow-y: auto;
    }

    &.sheet {
        :host > div {
            width: fit-content;
            margin: auto;
        }
    }
}
</style>