<template>
    <div class="container-evaluation">
        <ModalUpdateLogo
        />
        <div class="d-flex">
            <h2 style="margin: 0">Logo</h2>
            <a style="margin-left: 1em" @click="$modal.show('modalUpdateLogo')">
                <em class="fas fa-pencil-alt" data-toggle="tooltip" data-placement="top"></em>
            </a>
        </div>
        <img src="/images/custom/logo.png" class="logo-settings">
        <h2>{{Colors}}</h2>
    </div>
</template>

<script>
    import axios from "axios";
    import ModalUpdateLogo from "../../views/advancedModals/ModalUpdateLogo";

    const qs = require("qs");

    export default {
        name: "editStyle",

        components: {ModalUpdateLogo},

        props: {
            actualLanguage: String
        },

        data() {
            return {
                Colors: Joomla.JText._("COM_EMUNDUS_ONBOARD_COLORS"),
            };
        },

        methods: {},

        created() {}
    };
</script>
<style>
    .fa-pencil-alt{
        color: #de6339;
        cursor: pointer;
    }
</style>
