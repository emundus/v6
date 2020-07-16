<template>
    <div class="container-evaluation">
        <notifications
                group="foo-velocity"
                animation-type="velocity"
                :speed="500"
                position="bottom left"
                :classes="'vue-notification-custom'"
        />
        <ModalUpdateLogo
                @UpdateLogo="updateView"
        />
        <div class="d-flex">
            <h2 style="margin: 0">Logo</h2>
            <a style="margin-left: 1em" @click="$modal.show('modalUpdateLogo')">
                <em class="fas fa-pencil-alt" data-toggle="tooltip" data-placement="top"></em>
            </a>
        </div>
        <img class="logo-settings" :src="imageLink">
        <h2>{{Colors}}</h2>
        <div class="d-flex" style="margin-bottom: 20px;">
            <label style="margin: 0" class="col-md-2">{{PrimaryColor}} : </label>
            <div class="color-picker" id="primary">
                <input type="color" v-model="primary" class="color-input"/>
            </div>
        </div>
        <div class="d-flex">
            <label style="margin: 0" class="col-md-2">{{SecondaryColor}} : </label>
            <div class="color-picker" id="secondary">
                <input type="color" v-model="secondary" class="color-input"/>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from "axios";
    import ModalUpdateLogo from "../../views/advancedModals/ModalUpdateLogo";
    import VSwatches from 'vue-swatches'
    import 'vue-swatches/dist/vue-swatches.css'

    const qs = require("qs");

    export default {
        name: "editStyle",

        components: {
            ModalUpdateLogo,
            VSwatches
        },

        props: {
            actualLanguage: String
        },

        data() {
            return {
                imageLink: '/images/custom/logo.png',
                primary: '',
                secondary: '',
                PrimaryColor: Joomla.JText._("COM_EMUNDUS_ONBOARD_PRIMARY_COLOR"),
                SecondaryColor: Joomla.JText._("COM_EMUNDUS_ONBOARD_SECONDARY_COLOR"),
                Colors: Joomla.JText._("COM_EMUNDUS_ONBOARD_COLORS"),
            };
        },

        methods: {
            updateView(image) {
                this.imageLink = image;
                this.$forceUpdate();
            },

            updateColor(type,color) {
                axios({
                    method: "post",
                    url:
                        "index.php?option=com_emundus_onboard&controller=settings&task=updatecolor",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    data: qs.stringify({
                        type: type,
                        color: color
                    })
                }).then((rep) => {
                    if(rep.status == 1){
                        this.tip();
                    }
                });
            },
            /**
             * ** Methods for notify
             */
            tip(){
                this.show(
                    "foo-velocity",
                    Joomla.JText._("COM_EMUNDUS_ONBOARD_BUILDER_UPDATE"),
                    Joomla.JText._("COM_EMUNDUS_ONBOARD_COLOR_SUCCESS"),
                );
            },
            show(group, text = "", title = "Information") {
                this.$notify({
                    group,
                    title: `${title}`,
                    text: text,
                    duration: 3000
                });
            },
            clean(group) {
                this.$notify({ group, clean: true });
            },
        },

        created() {
            axios({
                method: "get",
                url: 'index.php?option=com_emundus_onboard&controller=settings&task=getappcolors',
            }).then((rep) => {
                this.primary = rep.data.primary;
                this.secondary = rep.data.secondary;
            });
        },

        watch: {
            primary: function(value){
                document.getElementById('primary').style.backgroundColor = value;
                this.updateColor('primary',value);
            },
            secondary: function(value){
                document.getElementById('secondary').style.backgroundColor = value;
                this.updateColor('secondary',value);
            }
        }
    };
</script>
<style>
    .fa-pencil-alt{
        color: #de6339;
        cursor: pointer;
    }
</style>
