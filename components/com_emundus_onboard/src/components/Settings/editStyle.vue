<template>
  <div class="em-flex-column">
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

    <div class="em-w-80" style="display:flex; flex-direction: column">

      <!-- LOGO -->
      <div class="em-h-auto em-flex-row col-md-4 em-mb-32" style="align-items: start">
        <div class="em-logo-box pointer" @click="$modal.show('modalUpdateLogo')">
          <img class="logo-settings" :src="imageLink" :srcset="'/'+imageLink" :alt="InsertLogo">
        </div>
        <div class="w-100 em-ml-24">
          <div class="em-flex-row em-flex-space-between">
            <h2 style="margin: 0">Logo</h2>
            <a class="pointer em-main-500-color" @click="$modal.show('modalUpdateLogo')">
              {{ translate('COM_EMUNDUS_ONBOARD_MODIFY') }}
            </a>
          </div>
        </div>
      </div>

      <!-- FAVICON -->
      <div class="em-h-auto em-flex-row col-md-4 em-mb-32" style="align-items: start">
        <div class="em-logo-box pointer" @click="$modal.show('modalUpdateIcon')">
          <img class="logo-settings" :src="iconLink" :srcset="'/'+iconLink" :alt="InsertIcon">
        </div>
        <div class="w-100 em-ml-24">
          <div class="em-flex-row em-flex-space-between">
            <h2 style="margin: 0">{{Icon}}</h2>
            <a class="pointer em-main-500-color" @click="$modal.show('modalUpdateIcon')">
              {{ translate('COM_EMUNDUS_ONBOARD_MODIFY') }}
            </a>
          </div>
        </div>
      </div>

      <!-- COLORS -->
      <div class="em-h-auto em-flex-row col-md-4 em-mb-32" style="align-items: start">
        <div class="em-logo-box pointer" @click="$modal.show('modalUpdateColors')">
          <div class="color-preset" :style="'background-color:' + primary + ';border-right: 25px solid' + secondary">
          </div>
        </div>
        <div class="w-100 em-ml-24">
          <div class="em-flex-row em-flex-space-between">
            <h2 style="margin: 0">{{Colors}}</h2>
            <a class="pointer em-main-500-color" @click="$modal.show('modalUpdateColors')">
              {{ translate('COM_EMUNDUS_ONBOARD_MODIFY') }}
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import axios from "axios";
import VSwatches from 'vue-swatches'
import 'vue-swatches/dist/vue-swatches.css'
import Swal from "sweetalert2";
import ModalUpdateIcon from "@/components/AdvancedModals/ModalUpdateIcon";
import ModalUpdateLogo from "@/components/AdvancedModals/ModalUpdateLogo";
import ModalUpdateColors from "@/components/AdvancedModals/ModalUpdateColors";

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

    imageExists(url, callback) {
      var img = new Image();
      img.onload = function() { callback(true); };
      img.onerror = function() { callback(false); };
      img.src = url;
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

    this.imageExists(this.imageLink, (exists) => {
      if(!exists){
        this.imageLink = 'images/custom/logo.png';
      }
    });

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
  height: 50px;
  border-radius: 50%;
  width: 50px;
}
</style>
