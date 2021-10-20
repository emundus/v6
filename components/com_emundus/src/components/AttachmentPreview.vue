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
            style: ''
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
                this.style = data.style;

                if (this.$refs['a-preview'].shadowRoot === null)  {
                    this.$refs['a-preview'].attachShadow({mode: 'open'});
                }
            } else {
                this.overflowX = false; 
                this.overflowY = false;
                this.preview = '';
            }

            this.$refs['a-preview'].shadowRoot.innerHTML = this.preview != null ? this.preview : '';

            if (this.style == 'sheet') {
                this.addSheetStyles();
            } else if (this.style == 'presentation') {
                this.addPresentationStyles();
            }
        },
        addSheetStyles() {
            // get div elements of first level
            const pages = this.$refs['a-preview'].shadowRoot.querySelectorAll('div');
            pages.forEach((div, key) => {
                div.style.width = "fit-content";
                div.style.margin = "20px auto";
                div.style.boxShadow = "0px 0px 10px rgba(0, 0, 0, 0.1)";

                if (key > 0) {
                    div.style.display = "none";
                }
            });

            const navigation = this.$refs['a-preview'].shadowRoot.querySelector('.navigation');
            if (navigation) {
                navigation.style.display = 'flex';
                navigation.style.flexDirection = 'row';
                navigation.style.justifyContent = 'flex-start';
                navigation.style.alignItems = 'center';

                navigation.querySelectorAll('li').forEach((li, li_key) => {
                    li.style.listStyleType = "none";
                    li.style.margin = "0 10px";

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
        addPresentationStyles() {
            const slides = this.$refs['a-preview'].shadowRoot.querySelectorAll('.slide');
            slides.forEach((slide, key) => {
                slide.style.padding = "16px";
                slide.style.margin = "20px";
                slide.style.width = "calc(100% - 72px)";
                slide.style.boxShadow = "0px 0px 10px rgba(0, 0, 0, 0.1)";
                slide.style.borderRadius = "8px";
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