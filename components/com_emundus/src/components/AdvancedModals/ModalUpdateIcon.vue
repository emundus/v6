<template>
  <!-- modalC -->
  <span :id="'modalUpdateIcon'">
    <modal
        :name="'modalUpdateIcon'"
        height="auto"
        transition="nice-modal-fade"
        :min-width="200"
        :min-height="200"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
        @closed="beforeClose"
        @before-open="beforeOpen"
    >

      <div class="em-flex-row em-flex-space-between em-mb-16">
        <span class="em-h4">
          {{updateIcon}}
        </span>
        <button class="em-pointer em-transparent-button" @click.prevent="$modal.hide('modalUpdateIcon')">
          <span class="material-icons">close</span>
        </button>
      </div>

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
          {{DropHere}}
        </div>
      </vue-dropzone>

      <div class="em-flex-row em-flex-space-between em-mb-8 em-mt-16">
        <button type="button" class="em-secondary-button em-w-auto"
                @click.prevent="$modal.hide('modalUpdateIcon')">
          {{Retour}}
        </button>
        <button type="button" class="em-primary-button em-w-auto"
                @click.prevent="uploadNewLogo()">
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
  name: "modalUpdateIcon",
  props: { },
  components: {
    vueDropzone
  },
  data() {
    return {
      dropzoneOptions: {
        url: 'index.php?option=com_emundus&controller=settings&task=updateicon',
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
      updateIcon: this.translate("COM_EMUNDUS_ONBOARD_UPDATE_ICON"),
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
      if(this.$refs.dropzone.getAcceptedFiles().length === 0){
        document.getElementById('dropzone-message').style.display = 'block';
      }
    },
    onComplete: function(response){
      if(response.status == 'success'){
        this.$emit("UpdateIcon",response.dataURL);
        this.$modal.hide('modalUpdateIcon');
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
};
</script>

<style scoped lang="scss">
/**** CUSTOM VUE DROPZONE ****/
#customdropzone {
  letter-spacing: 0.2px;
  background: #fff;
  color: #777;
  transition: background-color .2s linear;
  height: 200px;
  padding: 40px;
  border: dashed;
  border-radius: 5px;
  justify-content: center;
  align-items: center;
  display: flex;
  cursor: pointer;
  .dz-preview {
    width: 100%;
    display: inline-block;
    text-align: center;
    .dz-image {
      width: auto;
      height: 100px;
      >div {
        width: inherit;
        height: inherit;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
      }
      >img {
        width: 100%;
      }
    }
    .dz-details {
      color: black;
      transition: opacity .2s linear;
      text-align: center;
    }
  }
  .dz-success-mark {
    display: none;
  }
}
.dz-default.dz-message {
  text-align: center !important;
}
.dz-error-mark {
  display: none;
}
/**** END ****/
</style>
