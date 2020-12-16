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
      <ModalUpdateIcon
          @UpdateIcon="updateIcon"
        />
        <div class="section-sub-menu col-lg-5 mr-2 col-sm-12">
          <h2 style="margin: 0">Logo</h2>
          <div class="d-flex"></div>
            <img class="logo-settings" :src="imageLink">
            <a class="settings-edit-icon cta-block pointer" @click="$modal.show('modalUpdateLogo')">
              <em class="fas fa-pen" data-toggle="tooltip" data-placement="top"></em>
            </a>
        </div>

        <div class="section-sub-menu col-lg-5 col-sm-12">
          <h2 style="margin: 0">Icône</h2>
          <div class="d-flex"></div>
          <img class="logo-settings" style="max-width: 50px" :src="iconLink">
          <a class="settings-edit-icon cta-block pointer" @click="$modal.show('modalUpdateIcon')">
            <em class="fas fa-pen" data-toggle="tooltip" data-placement="top"></em>
          </a>
        </div>
        <!--<h2>{{Colors}}</h2>
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
        </div>-->
    </div>
</template>

<script>
    import axios from "axios";
    import ModalUpdateLogo from "../../views/advancedModals/ModalUpdateLogo";
    import VSwatches from 'vue-swatches'
    import 'vue-swatches/dist/vue-swatches.css'
    import ModalUpdateIcon from "@/views/advancedModals/ModalUpdateIcon";

    const qs = require("qs");

    export default {
        name: "editStyle",

        components: {
          ModalUpdateIcon,
            ModalUpdateLogo,
            VSwatches
        },

        props: {
            actualLanguage: String
        },

        data() {
            return {
                imageLink: '/images/custom/logo.png',
                iconLink: '/images/custom/favicon.png',
                primary: '',
                secondary: '',
                changes: false,
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
            updateIcon(image) {
              this.iconLink = image;
              this.$forceUpdate();
            },

            /*updateColor(type,color) {
                this.$emit("LaunchLoading");
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
                  this.$emit("StopLoading");
                });
            },*/
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
            this.changes = false;
            axios({
                method: "get",
                url: 'index.php?option=com_emundus_onboard&controller=settings&task=getappcolors',
            }).then((rep) => {
                this.primary = rep.data.primary;
                this.secondary = rep.data.secondary;
                setTimeout(() => {
                  this.changes = true;
                },1000);
            });
        },

        /*watch: {
            primary: function(value){
                document.getElementById('primary').style.backgroundColor = value;
                if(this.changes) {
                  //this.updateColor('primary', value);
                }
            },
            secondary: function(value){
                document.getElementById('secondary').style.backgroundColor = value;
                if(this.changes) {
                  //this.updateColor('secondary', value);
                }
            }
        }*/
    };
</script>
<style scoped>
  .section-sub-menu{
    padding: 20px;
    margin: 0;
    height: 200px;
  }
  .settings-edit-icon{
    display: block;
    width: 100%;
    text-align: end;
    font-size: 20px;
  }
</style>
