<template>
    <div>
        <h1>Liste de dossiers</h1>
        <ul>
            <li :v-for="attachment in attachments">
                {{attachment.name}}
            </li>
        </ul>
    </div>
</template>

<script>
import attachmentService from '../services/attachment.js';

export default {
    name: 'Attachments',
    props: {
        user: {
            type: Number,
            required: true
        },
        fnum: {
            type: Number,
            required: true
        },
    },
    data() {
        return {
            attachments: [],
            loading: true,
        }
    },

    mounted() {
        // this.getAttachments();
    },
    methods: {
        async getAttachments() {
            this.attachments = await attachmentService.getAttachments(this.user, this.fnum);

        }
    }
}
</script>

<style lang="scss" scoped>

</style>