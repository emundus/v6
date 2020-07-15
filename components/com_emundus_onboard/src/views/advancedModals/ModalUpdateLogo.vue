<template>
  <!-- modalC -->
  <span :id="'modalUpdateLogo'">
    <modal
            :name="'modalUpdateLogo'"
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
      <div class="modalC-content">
        <div class="update-field-header">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalUpdateLogo')">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{updateLogo}}
          </h2>
        </div>
        <vue-dropzone ref="myVueDropzone" id="customdropzone" :options="dropzoneOptions">
        </vue-dropzone>
      </div>
      <div class="col-md-12 mb-1">
        <a class="bouton-sauvergarder-et-continuer-3"
           @click.prevent="uploadNewLogo()">
          {{ Continuer }}
        </a>
        <a class="bouton-sauvergarder-et-continuer-3 w-retour"
           @click.prevent="$modal.hide('modalUpdateLogo')">
          {{Retour}}
        </a>
      </div>
    </modal>
  </span>
</template>

<script>
  import axios from "axios";
  import vue2Dropzone from 'vue2-dropzone'
  import 'vue2-dropzone/dist/vue2Dropzone.min.css'
  const qs = require("qs");

  export default {
    name: "modalUpdateLogo",
    props: { },
    components: {
      vueDropzone: vue2Dropzone
    },
    data() {
      return {
        dropzoneOptions: {
          url: '',
          maxFilesize: 10,
          addRemoveLinks: true,
          dictDefaultMessage: '<i class="fas fa-file-image"></i>' + Joomla.JText._("COM_EMUNDUS_ONBOARD_DROP_HERE")
        },
        updateLogo: Joomla.JText._("COM_EMUNDUS_ONBOARD_UPDATE_LOGO"),
        Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
        Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
        DropHere: Joomla.JText._("COM_EMUNDUS_ONBOARD_DROP_HERE"),
      };
    },
    methods: {
      beforeClose(event) {},
      beforeOpen(event) {
      },
    },
  };
</script>

<style scoped>
  .modalC-content {
    height: 100%;
    box-sizing: border-box;
    padding: 10px;
    font-size: 15px;
    overflow: auto;
  }
  .topright {
    font-size: 25px;
    float: right;
  }
  .btnCloseModal {
    background-color: inherit;
  }
  .update-field-header{
    margin-bottom: 1em;
  }

  .update-title-header{
    margin-top: 0;
    display: flex;
    align-items: center;
  }

  #customdropzone {
    font-family: 'Arial', sans-serif;
    letter-spacing: 0.2px;
    color: #777;
    transition: background-color .2s linear;
    height: 200px;
    padding: 40px;
    border: dashed;
    border-radius: 5px;
    margin: 10px;
    justify-content: center;
    align-items: center;
    display: flex;
  }

  .dz-default.dz-message{
    text-align: center !important;
  }

  #customdropzone .dz-preview {
    width: 160px;
    display: inline-block
  }
  #customdropzone .dz-preview .dz-image {
    width: 80px;
    height: 80px;
    margin-left: 40px;
    margin-bottom: 10px;
  }
  #customdropzone .dz-preview .dz-image > div {
    width: inherit;
    height: inherit;
    border-radius: 50%;
    background-size: contain;
  }
  #customdropzone .dz-preview .dz-image > img {
    width: 100%;
  }

  #customdropzone .dz-preview .dz-details {
    color: white;
    transition: opacity .2s linear;
    text-align: center;
  }
  #customdropzone .dz-success-mark, .dz-error-mark, .dz-remove {
    display: none;
  }

  .fa-file-image{
    font-size: 25px;
    margin-right: 20px;
  }
</style>
