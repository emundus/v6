<template>
  <div>
    <ModalUpdateColors
        :key="primary + secondary"
        v-if="primary && secondary"
        :primary="primary"
        :secondary="secondary"
        @UpdateColors="updateColors"
    />


    <div class="em-grid-2" v-if="!loading">
      <!-- COLORS -->
      <div class="em-style-options em-mb-32">
        <div>
          <h4 class="em-text-neutral-800 em-flex-row em-mb-8">
            {{ translate("COM_EMUNDUS_ONBOARD_COLORS") }}
            <span class="material-icons-outlined em-ml-4 em-font-size-16 em-pointer" @click="displayColorsTip">help_outline</span>
          </h4>
          <span style="opacity: 0">Colors</span><br/>
          <span style="opacity: 0">Colors</span>
        </div>

        <div class="em-logo-box pointer em-mt-16">
          <div class="color-preset" :style="'background-color:' + primary + ';border-right: 25px solid' + secondary">
          </div>
        </div>

        <button class="em-mt-8 em-primary-button" @click="$modal.show('modalUpdateColors')">
          <span>{{ translate("COM_EMUNDUS_ONBOARD_UPDATE_COLORS") }}</span>
        </button>
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>

import Swal from "sweetalert2";
import axios from "axios";
import ModalUpdateColors from "../../AdvancedModals/ModalUpdateColors";

const getTemplate = () => `
<div class="dz-preview dz-file-preview">
  <div class="dz-image">
    <div data-dz-thumbnail-bg></div>
  </div>
  <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
  <div class="dz-error-message"><span data-dz-errormessage></span></div>
  <div class="dz-error-mark"><i class="fa fa-close"></i></div>
</div>
`;

export default {
  name: "global",
  props: { },
  components: {
    ModalUpdateColors,
  },
  data() {
    return {
      loading: false,


      primary: '',
      secondary: '',
      changes: false,
    }
  },

  async created() {
    this.loading = true;
    this.changes = false;

    await this.getAppColors();

    this.changes = true;
    this.loading = false;
  },

  methods:{


    getAppColors() {
      return new Promise((resolve) => {
        axios({
          method: "get",
          url: 'index.php?option=com_emundus&controller=settings&task=getappcolors',
        }).then((rep) => {
          this.primary = rep.data.primary;
          this.secondary = rep.data.secondary;

          resolve(true);
        });
      });
    },

    updateView(response) {
      this.hideLogo = false;
      this.imageLink = 'images/custom/' + response.filename + '?' + new Date().getTime();

      const oldLogo = document.querySelector('img[src="/images/custom/'+response.old_logo+'"]');
      if (oldLogo) {
        oldLogo.src = '/' + this.imageLink;
      }
      this.$forceUpdate();
    },





    updateColors(colors){
      this.primary = colors.primary;
      this.secondary = colors.secondary;
    },

    beforeClose(event) {
    },

    beforeOpen(event) {
    },

    thumbnail: function (file, dataUrl) {
      var j, len, ref, thumbnailElement;
      if (file.previewElement) {
        file.previewElement.classList.remove("dz-file-preview");
        ref = file.previewElement.querySelectorAll("[data-dz-thumbnail-bg]");
        for (j = 0, len = ref.length; j < len; j++) {
          thumbnailElement = ref[j];
          thumbnailElement.alt = file.name;
          thumbnailElement.style.backgroundImage = 'url("' + dataUrl + '")';
        }
        return setTimeout(((function (_this) {
          return function () {
            return file.previewElement.classList.add("dz-image-preview");
          };
        })(this)), 1);
      }
    },

    displayColorsTip() {
      Swal.fire({
        title: this.translate('COM_EMUNDUS_ONBOARD_COLORS'),
        text: this.translate("COM_EMUNDUS_FORM_BUILDER_COLORS_RECOMMENDED"),
        showCancelButton: false,
        confirmButtonText: this.translate("COM_EMUNDUS_SWAL_OK_BUTTON"),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          confirmButton: 'em-swal-confirm-button',
          actions: "em-swal-single-action",
        },
      });
    },


  },
  watch: {
  }
}
</script>

<style scoped>
.color-preset{
  height: 50px;
  border-radius: 50%;
  width: 50px;
}

.em-style-options {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
</style>
