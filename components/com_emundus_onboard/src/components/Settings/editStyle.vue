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
    <ModalUpdateColors
        @UpdateColors="updateColors"
    />
    <div class="section-sub-menu col-lg-5 mr-2 col-sm-12">
      <h2 style="margin: 0">Logo</h2>
      <div class="d-flex"></div>
      <img class="logo-settings" :src="imageLink" :srcset="'/'+imageLink" :alt="InsertLogo">
      <a class="settings-edit-icon cta-block pointer" @click="$modal.show('modalUpdateLogo')">
        <em class="fas fa-pen" data-toggle="tooltip" data-placement="top"></em>
      </a>
    </div>

    <div class="section-sub-menu col-lg-5 col-sm-12">
      <h2 style="margin: 0">{{Icon}}</h2>
      <div class="d-flex"></div>
      <img class="logo-settings" :src="iconLink" :srcset="'/'+iconLink" :alt="InsertIcon">
      <a class="settings-edit-icon cta-block pointer" style="top: 20px" @click="removeIcon">
        <em class="fas fa-times" data-toggle="tooltip" data-placement="top"></em>
      </a>
      <a class="settings-edit-icon cta-block pointer" @click="$modal.show('modalUpdateIcon')">
        <em class="fas fa-pen" data-toggle="tooltip" data-placement="top"></em>
      </a>
    </div>
    <div class="section-sub-menu col-lg-5 col-sm-12 mt-2">
      <h2 style="margin: 0">{{Colors}}</h2>
      <div class="d-flex">
        <div class="color-preset" :style="'background-color:' + primary + ';border-right: 30px solid' + secondary">
        </div>
        <a class="settings-edit-icon cta-block pointer" @click="$modal.show('modalUpdateColors')">
          <em class="fas fa-pen" data-toggle="tooltip" data-placement="top"></em>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import ModalUpdateLogo from "../../views/advancedModals/ModalUpdateLogo";
import VSwatches from 'vue-swatches'
import 'vue-swatches/dist/vue-swatches.css'
import ModalUpdateIcon from "@/views/advancedModals/ModalUpdateIcon";
import Swal from "sweetalert2";
import ModalUpdateColors from "@/views/advancedModals/ModalUpdateColors";

const qs = require("qs");

export default {
  name: "editStyle",

  components: {
    ModalUpdateColors,
    ModalUpdateIcon,
    ModalUpdateLogo,
    VSwatches
  },

  props: {
    actualLanguage: String
  },

  data() {
    return {
      imageLink: 'images/custom/logo_custom.png',
      iconLink: 'images/custom/favicon.png',
      primary: '',
      secondary: '',
      swatches: [
        '#DCC6E0', '#947CB0', '#663399', '#6BB9F0', '#19B5FE', '#013243', '#7BEFB2', '#3FC380', '#1E824C', '#FFFD7E',
        '#FFFD54', '#F7CA18', '#FABE58', '#E87E04', '#D35400', '#EC644B', '#CF000F', '#E5283B', '#E08283', '#D2527F',
        '#DB0A5B', '#999999'
      ],
      changes: false,
      PrimaryColor: Joomla.JText._("COM_EMUNDUS_ONBOARD_PRIMARY_COLOR"),
      SecondaryColor: Joomla.JText._("COM_EMUNDUS_ONBOARD_SECONDARY_COLOR"),
      Colors: Joomla.JText._("COM_EMUNDUS_ONBOARD_COLORS"),
      Icon: Joomla.JText._("COM_EMUNDUS_ONBOARD_ICON"),
      InsertLogo: Joomla.JText._("COM_EMUNDUS_ONBOARD_INSERT_LOGO"),
      InsertIcon: Joomla.JText._("COM_EMUNDUS_ONBOARD_INSERT_ICON"),
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
    updateColors(colors){
      this.primary = colors.primary;
      this.secondary = colors.secondary;
    },
    removeIcon() {
      Swal.fire({
        title: Joomla.JText._("COM_EMUNDUS_ONBOARD_REMOVE_ICON"),
        text: Joomla.JText._("COM_EMUNDUS_ONBOARD_REMOVE_ICON_TEXT"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#12db42',
        confirmButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          axios({
            method: "post",
            url:
                "index.php?option=com_emundus_onboard&controller=settings&task=removeicon",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
          }).then((rep) => {
            this.iconLink = '';
            this.$forceUpdate();
          });
        }
      });
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
  width: 30px;
  text-align: end;
  font-size: 20px;
  position: absolute;
  right: 20px;
  bottom: 15px;
}
.color-preset{
  height: 100px;
  margin: 30px;
  border-radius: 50%;
  width: 100px;
}
</style>
