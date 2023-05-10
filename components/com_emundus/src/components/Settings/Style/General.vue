<template>
  <div>
    <ModalUpdateColors
        @UpdateColors="updateColors"
    />

    <!-- LOGO -->
    <div class="em-grid-2" v-show="!loading">
      <div class="em-style-options em-mb-32">
        <div class="em-flex-row">
          <div>
            <h4 class="em-text-neutral-800 em-h4 em-flex-row em-mb-8">
              Logo
              <span class="material-icons-outlined em-ml-4 em-font-size-16 em-pointer" @click="displayLogoTip">help_outline</span>
            </h4>
            <p><em>{{ translate('COM_EMUNDUS_FORM_BUILDER_ALLOWED_FORMATS') }} : jpeg, jpg, png, gif, svg</em></p>
            <p><em>{{ translate('COM_EMUNDUS_FORM_BUILDER_LOGO_RECOMMENDED') }}</em></p>
          </div>
        </div>

        <div class="em-logo-box pointer em-mt-16" v-if="!logo_updating">
          <img class="logo-settings" :src="imageLink" :srcset="'/'+imageLink"  @error="hideLogo = true">
          <p v-if="hideLogo">{{ translate('COM_EMUNDUS_ONBOARD_INSERT_LOGO') }}</p>
        </div>
        <div class="em-mt-16" v-if="logo_updating">
          <vue-dropzone
              ref="dropzone"
              id="customdropzone"
              :include-styling="false"
              :options="logoDropzoneOptions"
              :useCustomSlot=true
              v-on:vdropzone-file-added="afterAdded"
              v-on:vdropzone-thumbnail="thumbnail"
              v-on:vdropzone-removed-file="afterRemoved"
              v-on:vdropzone-complete="onComplete"
              v-on:vdropzone-error="catchError">
            <div class="dropzone-custom-content" id="dropzone-message">
              {{ translate("COM_EMUNDUS_ONBOARD_DROP_HERE") }}
            </div>
          </vue-dropzone>
        </div>

        <button @click="logo_updating = !logo_updating" class="em-mt-8 em-primary-button">
          <span v-if="!logo_updating">{{ translate("COM_EMUNDUS_ONBOARD_UPDATE_LOGO") }}</span>
          <span v-else>{{ translate('COM_EMUNDUS_ONBOARD_CANCEL') }}</span>
        </button>
      </div>

      <!-- FAVICON -->
      <div class="em-style-options em-mb-32">
        <div class="em-flex-row">
          <div>
            <h4 class="em-text-neutral-800 em-h4 em-flex-row em-mb-8">
              {{ translate("COM_EMUNDUS_ONBOARD_ICON") }}
              <span class="material-icons-outlined em-ml-4 em-font-size-16 em-pointer" @click="displayFaviconTip">help_outline</span>
            </h4>
            <p><em>{{ translate('COM_EMUNDUS_FORM_BUILDER_ALLOWED_FORMATS') }} : jpeg, jpg, png</em></p>
            <p><em>{{ translate('COM_EMUNDUS_FORM_BUILDER_ICON_RECOMMENDED') }}</em></p>
          </div>
        </div>

        <div class="em-logo-box pointer em-mt-16" v-if="!favicon_updating">
          <img class="logo-settings" v-if="!hideIcon" :src="iconLink" :srcset="iconLink" @error="hideIcon = true">
          <p v-if="hideIcon">{{ translate('COM_EMUNDUS_ONBOARD_INSERT_ICON') }}</p>
        </div>
        <div class="em-mt-16" v-if="favicon_updating">
          <vue-dropzone
              ref="dropzone"
              id="customdropzone"
              :include-styling="false"
              :options="faviconDropzoneOptions"
              :useCustomSlot=true
              v-on:vdropzone-file-added="afterAdded"
              v-on:vdropzone-thumbnail="thumbnail"
              v-on:vdropzone-removed-file="afterRemoved"
              v-on:vdropzone-complete="onComplete"
              v-on:vdropzone-error="catchError">
            <div class="dropzone-custom-content" id="dropzone-message">
              {{ translate("COM_EMUNDUS_ONBOARD_DROP_HERE") }}
            </div>
          </vue-dropzone>
        </div>

        <button @click="favicon_updating = !favicon_updating" class="em-mt-8 em-primary-button">
          <span v-if="!favicon_updating">{{ translate("COM_EMUNDUS_ONBOARD_UPDATE_ICON") }}</span>
          <span v-else>{{ translate('COM_EMUNDUS_ONBOARD_CANCEL') }}</span>
        </button>
      </div>

      <!-- COLORS -->
      <div class="em-style-options em-mb-32">
        <div>
          <h4 class="em-text-neutral-800 em-h4 em-flex-row em-mb-8">
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

      <!-- BANNER -->
      <div v-if="bannerLink" class="em-h-auto em-flex-col em-mb-32" style="align-items: start">
        <div class="em-flex-row">
          <div>
            <h4 class="em-text-neutral-800 em-h4 em-mb-8 em-flex-row">
              {{ translate("COM_EMUNDUS_ONBOARD_BANNER") }}
              <span class="material-icons-outlined em-ml-4 em-font-size-16 em-pointer" @click="displayBannerTip">help_outline</span>
            </h4>
            <span><em>{{ translate('COM_EMUNDUS_FORM_BUILDER_ALLOWED_FORMATS') }} : jpeg, png</em></span><br/>
            <span><em>{{ translate('COM_EMUNDUS_FORM_BUILDER_RECOMMENDED_SIZE') }} : 1440x200px</em></span>
          </div>
        </div>

        <div class="em-logo-box pointer em-mt-16" v-if="!banner_updating">
          <img class="logo-settings" style="width: 180px" :src="bannerLink" :srcset="'/'+bannerLink" :alt="InsertBanner">
        </div>
        <div class="em-mt-16" v-if="banner_updating">
          <vue-dropzone
              ref="dropzone"
              id="customdropzone"
              :include-styling="false"
              :options="bannerDropzoneOptions"
              :useCustomSlot=true
              v-on:vdropzone-file-added="afterAdded"
              v-on:vdropzone-thumbnail="thumbnail"
              v-on:vdropzone-removed-file="afterRemoved"
              v-on:vdropzone-complete="onComplete"
              v-on:vdropzone-error="catchError">
            <div class="dropzone-custom-content" id="dropzone-message">
              {{ translate("COM_EMUNDUS_ONBOARD_DROP_HERE") }}
            </div>
          </vue-dropzone>
        </div>

        <button @click="banner_updating = !banner_updating" class="em-mt-8 em-primary-button">
          <span v-if="!banner_updating">{{ translate("COM_EMUNDUS_ONBOARD_UPDATE_BANNER") }}</span>
          <span v-else>{{ translate('COM_EMUNDUS_ONBOARD_CANCEL') }}</span>
        </button>
      </div>

    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>

import vueDropzone from 'vue2-dropzone';
import Multiselect from 'vue-multiselect';
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
    Multiselect,
    vueDropzone
  },
  data() {
    return {
      loading: false,
      logo_updating: false,
      favicon_updating: false,
      banner_updating: false,

      imageLink: '',
      iconLink: window.location.origin + '//images/custom/favicon.png' + '?' + new Date().getTime(),
      bannerLink: null,
      primary: '',
      secondary: '',
      changes: false,
      hideIcon: false,
      hideLogo: false,
      InsertLogo: this.translate("COM_EMUNDUS_ONBOARD_INSERT_LOGO"),
      InsertIcon: this.translate("COM_EMUNDUS_ONBOARD_INSERT_ICON"),
      InsertBanner: this.translate("COM_EMUNDUS_ONBOARD_INSERT_BANNER"),

      logoDropzoneOptions: {
        url: 'index.php?option=com_emundus&controller=settings&task=updatelogo',
        maxFilesize: 10,
        maxFiles: 1,
        autoProcessQueue: true,
        addRemoveLinks: true,
        thumbnailWidth: null,
        thumbnailHeight: null,
        resizeMimeType: 'image/png',
        acceptedFiles: 'image/*',
        previewTemplate: getTemplate(),
        dictCancelUpload: this.translate("COM_EMUNDUS_ONBOARD_CANCEL_UPLOAD"),
        dictCancelUploadConfirmation: this.translate("COM_EMUNDUS_ONBOARD_CANCEL_UPLOAD_CONFIRMATION"),
        dictRemoveFile: this.translate("COM_EMUNDUS_ONBOARD_REMOVE_FILE"),
        dictInvalidFileType: this.translate("COM_EMUNDUS_ONBOARD_INVALID_FILE_TYPE"),
        dictFileTooBig: this.translate("COM_EMUNDUS_ONBOARD_FILE_TOO_BIG") + ' (10Mo).',
        dictMaxFilesExceeded: this.translate("COM_EMUNDUS_ONBOARD_MAX_FILES_EXCEEDED"),
      },
      faviconDropzoneOptions: {
        url: 'index.php?option=com_emundus&controller=settings&task=updateicon',
        maxFilesize: 10,
        maxFiles: 1,
        autoProcessQueue: true,
        addRemoveLinks: true,
        thumbnailWidth: null,
        thumbnailHeight: null,
        resizeMimeType: 'image/png',
        acceptedFiles: 'image/png,image/jpeg',
        previewTemplate: getTemplate(),
        dictCancelUpload: this.translate("COM_EMUNDUS_ONBOARD_CANCEL_UPLOAD"),
        dictCancelUploadConfirmation: this.translate("COM_EMUNDUS_ONBOARD_CANCEL_UPLOAD_CONFIRMATION"),
        dictRemoveFile: this.translate("COM_EMUNDUS_ONBOARD_REMOVE_FILE"),
        dictInvalidFileType: this.translate("COM_EMUNDUS_ONBOARD_INVALID_FILE_TYPE"),
        dictFileTooBig: this.translate("COM_EMUNDUS_ONBOARD_FILE_TOO_BIG") + ' : 10Mo',
        dictMaxFilesExceeded: this.translate("COM_EMUNDUS_ONBOARD_MAX_FILES_EXCEEDED"),
      },
      bannerDropzoneOptions: {
        url: 'index.php?option=com_emundus&controller=settings&task=updatebanner',
        maxFilesize: 10,
        maxFiles: 1,
        autoProcessQueue: true,
        addRemoveLinks: true,
        thumbnailWidth: null,
        thumbnailHeight: null,
        resizeMimeType: 'image/png',
        acceptedFiles: 'image/png,image/jpeg',
        previewTemplate: getTemplate(),
        dictCancelUpload: this.translate("COM_EMUNDUS_ONBOARD_CANCEL_UPLOAD"),
        dictCancelUploadConfirmation: this.translate("COM_EMUNDUS_ONBOARD_CANCEL_UPLOAD_CONFIRMATION"),
        dictRemoveFile: this.translate("COM_EMUNDUS_ONBOARD_REMOVE_FILE"),
        dictInvalidFileType: this.translate("COM_EMUNDUS_ONBOARD_INVALID_FILE_TYPE"),
        dictFileTooBig: this.translate("COM_EMUNDUS_ONBOARD_FILE_TOO_BIG") + ' : 10Mo',
        dictMaxFilesExceeded: this.translate("COM_EMUNDUS_ONBOARD_MAX_FILES_EXCEEDED"),
      },
    }
  },

  created() {
    this.loading = true;
    this.changes = false;

    axios({
      method: "get",
      url: 'index.php?option=com_emundus&controller=settings&task=getlogo',
    }).then((rep) => {
      if(rep.data.filename == null){
        this.imageLink = 'images/custom/logo.png';
      } else {
        this.imageLink = 'images/custom/' + rep.data.filename + '?' + new Date().getTime();
      }

      setTimeout(() => {
        this.changes = true;
      },1000);
    });

    axios({
      method: "get",
      url: 'index.php?option=com_emundus&controller=settings&task=getfavicon',
    }).then((rep) => {
      if(rep.data.filename == null){
        this.iconLink = 'images/custom/favicon.png';
      } else {
        this.iconLink = rep.data.filename + '?' + new Date().getTime();
      }

      setTimeout(() => {
        this.changes = true;
      },1000);
    });

    axios({
      method: "get",
      url: 'index.php?option=com_emundus&controller=settings&task=getbanner',
    }).then((rep) => {
      if(rep.data.filename != null){
        this.bannerLink = rep.data.filename;
      }

      setTimeout(() => {
        this.changes = true;
      },1000);
    });

    axios({
      method: "get",
      url: 'index.php?option=com_emundus&controller=settings&task=getbanner',
    }).then((rep) => {
      if(rep.data.filename != null){
        this.bannerLink = rep.data.filename;
      }

      setTimeout(() => {
        this.changes = true;
      },1000);
      this.loading = false;
    });

    axios({
      method: "get",
      url: 'index.php?option=com_emundus&controller=settings&task=getappcolors',
    }).then((rep) => {
      this.primary = rep.data.primary;
      this.secondary = rep.data.secondary;
      setTimeout(() => {
        this.changes = true;
      },1000);
      this.loading = false;
    });
  },

  methods:{
    updateView(response) {
      this.hideLogo = false;
      this.imageLink = 'images/custom/'+response.filename+'?' + new Date().getTime();
      document.querySelector('img[src="/images/custom/'+response.old_logo+'"]').src = '/images/custom/'+response.filename+'?' + new Date().getTime();
      this.$forceUpdate();
    },
    updateIcon(response) {
      this.hideIcon = false;
      this.iconLink = window.location.origin + '//images/custom/'+response.filename+'?' + new Date().getTime();
      document.querySelector('link[type="image/x-icon"]').href = window.location.origin + '//images/custom/'+response.filename+'?' + new Date().getTime();
      document.querySelector('.tchooz-vertical-logo a img').src = window.location.origin + '//images/custom/'+response.filename+'?' + new Date().getTime();
      this.$forceUpdate();
    },
    updateBanner(ext = 'png') {
      this.bannerLink = 'images/custom/default_banner.'+ext+'?' + new Date().getTime();
      this.$forceUpdate();
    },
    updateBanner() {
      this.bannerLink = 'images/custom/default_banner.png?' + new Date().getTime();
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
    afterAdded() {
      document.getElementById('dropzone-message').style.display = 'none';
    },
    afterRemoved() {
      if(this.$refs.dropzone.getAcceptedFiles().length === 0){
        if(this.banner_updating || this.logo_updating || this.favicon_updating) {
          document.getElementById('dropzone-message').style.display = 'block';
        }
      }
    },
    onComplete: function(response){
      const ext = response.name.split('.').pop();
      if(response.status == 'success'){
        if(this.logo_updating) {
          this.logo_updating = false;
          this.updateView(JSON.parse(response.xhr.response));
        }
        if(this.favicon_updating) {
          this.favicon_updating = false;
          this.updateIcon(JSON.parse(response.xhr.response));
        }
        if(this.banner_updating) {
          this.banner_updating = false;
          this.updateBanner(ext);
        }
        if(this.banner_updating) {
          this.banner_updating = false;
          this.updateBanner();
        }
      }
    },
    catchError: function(file, message, xhr){
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_ERROR"),
        text: message,
        type: "error",
        showCancelButton: false,
        showConfirmButton: false,
        timer: 3000,
      });
      this.$refs.dropzone.removeFile(file);
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
    uploadNewLogo() {
      this.$refs.dropzone.processQueue();
    },

    displayFaviconTip() {
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_ICON"),
        text: this.translate("COM_EMUNDUS_ONBOARD_ICON_TIP_TEXT"),
        showCancelButton: false,
        confirmButtonText: this.translate("COM_EMUNDUS_SWAL_OK_BUTTON"),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          confirmButton: 'em-swal-confirm-button',
          actions: "em-swal-single-action",
        },
      }).then(result => {

      });
    },

    displayLogoTip() {
      Swal.fire({
        title: 'Logo',
        text: this.translate("COM_EMUNDUS_ONBOARD_LOGO_TIP_TEXT"),
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

    displayBannerTip() {
      Swal.fire({
        title: this.translate('COM_EMUNDUS_ONBOARD_BANNER'),
        text: this.translate("COM_EMUNDUS_ONBOARD_BANNER_TIP_TEXT"),
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

    openFileInput(){
      setTimeout(() => {
        document.getElementsByClassName('dz-clickable')[0].click();
      }, 300);
    }
  },
  watch: {
    logo_updating: function(value){
      if(value){
        this.favicon_updating = false;
        this.banner_updating = false;
        this.openFileInput();
      }
    },
    favicon_updating: function(value){
      if(value){
        this.logo_updating = false;
        this.banner_updating = false;
        this.openFileInput();
      }
    },
    banner_updating: function(value){
      if(value){
        this.favicon_updating = false;
        this.logo_updating = false;
        this.openFileInput();
      }
    }
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
