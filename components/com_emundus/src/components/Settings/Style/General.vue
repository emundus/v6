<template>
  <div>
    <ModalUpdateColors
        @UpdateColors="updateColors"
    />

    <!-- LOGO -->
    <div class="em-grid-2">
      <div class="em-h-auto em-flex-col em-mb-32" style="align-items: start">
        <div class="em-flex-row">
          <div>
            <h3 class="em-text-neutral-800" style="margin: 0">Logo</h3>
            <span><em>{{ translate('COM_EMUNDUS_FORM_BUILDER_ALLOWED_FORMATS') }} : jpeg, png, gif, svg</em></span>
          </div>
          <span class="material-icons em-pointer" style="margin-left: 125px" v-if="logo_updating" @click="logo_updating = !logo_updating">close</span>
        </div>

        <div class="em-logo-box pointer em-mt-16" @click="logo_updating = !logo_updating" v-if="!logo_updating">
          <img class="logo-settings" :src="imageLink" :srcset="'/'+imageLink" :alt="InsertLogo">
        </div>
        <div class="em-mt-16">
          <vue-dropzone
              v-if="logo_updating"
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
      </div>

      <!-- FAVICON -->
      <div class="em-h-auto em-flex-col em-mb-32" style="align-items: start">
        <div class="em-flex-row">
          <div>
            <h3 class="em-text-neutral-800" style="margin: 0">{{ translate("COM_EMUNDUS_ONBOARD_ICON") }}</h3>
            <span><em>{{ translate('COM_EMUNDUS_FORM_BUILDER_ALLOWED_FORMATS') }} : jpeg, png</em></span>
          </div>
          <span class="material-icons em-pointer" style="margin-left: 125px" v-if="favicon_updating" @click="favicon_updating = !favicon_updating">close</span>
        </div>

        <div class="em-logo-box pointer em-mt-16" @click="favicon_updating = !favicon_updating" v-if="!favicon_updating">
          <img class="logo-settings" :src="iconLink" :srcset="'/'+iconLink" :alt="InsertIcon">
        </div>
        <div class="em-mt-16">
          <vue-dropzone
              v-if="favicon_updating"
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
      </div>

      <!-- COLORS -->
      <div class="em-h-auto em-flex-col em-mb-32" style="align-items: start">
        <div class="em-flex-row" style="margin-bottom: 52px">
            <h3 class="em-text-neutral-800" style="margin: 0">{{ translate("COM_EMUNDUS_ONBOARD_COLORS") }}</h3>
        </div>

        <div class="em-logo-box pointer em-mt-16" @click="$modal.show('modalUpdateColors')">
          <div class="color-preset" :style="'background-color:' + primary + ';border-right: 25px solid' + secondary">
          </div>
        </div>
      </div>

      <!-- BANNER -->
      <div v-if="bannerLink" class="em-h-auto em-flex-col em-mb-32" style="align-items: start">
        <div class="em-flex-row">
          <div>
            <h3 class="em-text-neutral-800" style="margin: 0">{{ translate("COM_EMUNDUS_ONBOARD_BANNER") }}</h3>
            <span><em>{{ translate('COM_EMUNDUS_FORM_BUILDER_ALLOWED_FORMATS') }} : jpeg, png</em></span><br/>
            <span><em>{{ translate('COM_EMUNDUS_FORM_BUILDER_RECOMMENDED_SIZE') }} : 1440x200px</em></span>
          </div>
          <span class="material-icons em-pointer" style="margin-left: 125px" v-if="banner_updating" @click="banner_updating = !banner_updating">close</span>
        </div>

        <div class="em-logo-box pointer em-mt-16" @click="banner_updating = !banner_updating" v-if="!banner_updating">
          <img class="logo-settings" :src="bannerLink" :srcset="'/'+bannerLink" :alt="InsertBanner">
        </div>
        <div class="em-mt-16">
          <vue-dropzone
              v-if="banner_updating"
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
      iconLink: 'images/custom/favicon.png',
      bannerLink: null,
      primary: '',
      secondary: '',
      changes: false,
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
        this.imageLink = 'images/custom/' + rep.data.filename;
      }

      setTimeout(() => {
        this.changes = true;
      },1000);
      this.loading = false;
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
    updateView(ext = 'png') {
      this.imageLink = 'images/custom/logo_custom.'+ext+'?' + new Date().getTime();
      this.$forceUpdate();
    },
    updateIcon() {
      this.iconLink = 'images/custom/favicon.png?' + new Date().getTime();
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
    removeIcon() {
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_REMOVE_ICON"),
        text: this.translate("COM_EMUNDUS_ONBOARD_REMOVE_ICON_TEXT"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#12db42',
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true
      }).then(result => {
        if (result.value) {
          axios({
            method: "post",
            url:
                "index.php?option=com_emundus&controller=settings&task=removeicon",
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

    beforeClose(event) {
    },
    beforeOpen(event) {
    },
    afterAdded() {
      document.getElementById('dropzone-message').style.display = 'none';
    },
    afterRemoved() {
      if(this.$refs.dropzone.getAcceptedFiles().length === 0){
        document.getElementById('dropzone-message').style.display = 'block';
      }
    },
    onComplete: function(response){
      const ext = response.name.split('.').pop();
      if(response.status == 'success'){
        if(this.logo_updating) {
          this.logo_updating = false;
          this.updateView(ext);
        }
        if(this.favicon_updating) {
          this.favicon_updating = false;
          this.updateIcon();
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
</style>
