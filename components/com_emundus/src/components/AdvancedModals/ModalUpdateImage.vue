<template>
  <!-- modalC -->
  <span :id="'modalUpdateImage'">
    <modal
        :name="'modalUpdateImage'"
        height="auto"
        transition="little-move-left"
        :min-width="200"
        :min-height="200"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
        @closed="beforeClose"
        @before-open="beforeOpen"
    >
      <div class="fixed-header-modal">
            <div class="topright">
              <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalUpdateImage')">
                <em class="fas fa-times"></em>
              </button>
            </div>
          <div class="update-field-header">
            <h2 class="update-title-header">
             {{ updateBackground }}
            </h2>
          </div>
        </div>
      <div class="modalC-content">
        <vue-dropzone
            ref="dropzone"
            id="customdropzone"
            :include-styling="false"
            :options="dropzoneOptions"
            :useCustomSlot=true
            v-on:vdropzone-file-added="afterAdded"
            v-on:vdropzone-thumbnail="thumbnail"
            v-on:vdropzone-removed-file="afterRemoved"
            v-on:vdropzone-complete="onComplete"
            v-on:vdropzone-error="catchError">
          <div class="dropzone-custom-content" id="dropzone-message">
            <em class="fas fa-file-image"></em>
            {{ DropHere }}
          </div>
        </vue-dropzone>
      </div>
      <div class="em-flex-row em-flex-space-between mb-1">
        <button type="button" class="bouton-sauvergarder-et-continuer w-retour"
                @click.prevent="$modal.hide('modalUpdateImage')">
          {{ Retour }}
        </button>
        <button type="button" class="bouton-sauvergarder-et-continuer"
                @click.prevent="uploadNewBackground()">
          {{ Continuer }}
        </button>
      </div>
    </modal>
  </span>
</template>

<script>
import vueDropzone from 'vue2-dropzone'
import Swal from "sweetalert2";

const qs = require("qs");

const getTemplate = () => `
<div class="dz-preview dz-file-preview">
  <div class="dz-image">
    <div data-dz-thumbnail-bg></div>
  </div>
  <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
  <div class="dz-error-message"><span data-dz-errormessage></span></div>
  <div class="dz-success-mark"><i class="fa fa-check"></i></div>
  <div class="dz-error-mark"><i class="fa fa-close"></i></div>
</div>
`;

export default {
  name: "modalUpdateLogo",
  props: {},
  components: {
    vueDropzone
  },
  data() {
    return {
      dropzoneOptions: {
        url: 'index.php?option=com_emundus&controller=settings&task=updatehomebackground',
        maxFilesize: 10,
        maxFiles: 1,
        autoProcessQueue: false,
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
        dictFileTooBig: this.translate("COM_EMUNDUS_ONBOARD_FILE_TOO_BIG"),
        dictMaxFilesExceeded: this.translate("COM_EMUNDUS_ONBOARD_MAX_FILES_EXCEEDED"),
      },
      updateBackground: this.translate("COM_EMUNDUS_ONBOARD_UPDATE_BACKGROUND"),
      Retour: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: this.translate("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      DropHere: this.translate("COM_EMUNDUS_ONBOARD_DROP_HERE"),
      Error: this.translate("COM_EMUNDUS_ONBOARD_ERROR"),
    };
  },
  methods: {
    beforeClose(event) {
    },
    beforeOpen(event) {
    },
    afterAdded() {
      document.getElementById('dropzone-message').style.display = 'none';
    },
    afterRemoved() {
      if (this.$refs.dropzone.getAcceptedFiles().length === 0) {
        document.getElementById('dropzone-message').style.display = 'block';
      }
    },
    onComplete: function (response) {
      if (response.status == 'success') {
        this.$emit("UpdateImage", response.dataURL);
        this.$modal.hide('modalUpdateImage');
      }
    },
    catchError: function (file, message, xhr) {
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
    uploadNewBackground() {
      this.$refs.dropzone.processQueue();
    }
  }
};
</script>

<style scoped>
.fa-file-image {
  font-size: 25px;
  margin-right: 20px;
}
</style>
