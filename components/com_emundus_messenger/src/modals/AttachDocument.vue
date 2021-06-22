<template>
  <div class="messages__vue_attach_document">
    <span :id="'attach_documents'">
      <modal
          :name="'attach_documents'"
          transition="nice-modal-fade"
          :adaptive="true"
          height="auto"
          width="30%"
          :scrollable="true"
          :delay="100"
          :clickToClose="true"
          @closed="beforeClose"
      >
        <div class="messages__attach_content">
          <ul class="messages__attach_actions_tabs" v-if="!applicant">
            <li class="messages__attach_action" @click="action = 1" :class="action === 1 ? 'messages__attach_action__current' : ''">{{translations.sendDocument}}</li>
            <li class="messages__attach_action" @click="action = 2" :class="action === 2 ? 'messages__attach_action__current' : ''">{{translations.askDocument}}</li>
          </ul>

          <div v-if="action === 1">
            <label v-if="applicant">{{translations.sendDocument}}</label>
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
                {{translations.DropHere}}
              </div>
            </vue-dropzone>
          </div>
          <div v-if="action === 2"></div>
          <button type="button" class="messages__button_send"
                  @click.prevent="sendMessage()">
            {{ translations.send }}
          </button>
        </div>
      </modal>
    </span>
    <div class="loader" v-if="loading"></div>
  </div>
</template>

<script>
import axios from "axios";
import vueDropzone from 'vue2-dropzone'

import "../assets/css/bootstrap.css";
import "../assets/css/messenger.scss";
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
  name: "AttachDocument",
  props: {
    user: Number,
    fnum: String,
    applicant: Boolean
  },
  components: {
    vueDropzone
  },
  data() {
    return {
      translations:{
        sendDocument: Joomla.JText._("COM_EMUNDUS_MESSENGER_SEND_DOCUMENT"),
        askDocument: Joomla.JText._("COM_EMUNDUS_MESSENGER_ASK_DOCUMENT"),
        DropHere: Joomla.JText._("COM_EMUNDUS_MESSENGER_DROP_HERE"),
        send: Joomla.JText._("COM_EMUNDUS_MESSENGER_SEND"),
      },
      dropzoneOptions: {
        url: 'index.php?option=com_emundus_messenger&controller=messages&task=uploaddocument&fnum=' + this.fnum,
        maxFilesize: 10,
        maxFiles: 1,
        autoProcessQueue: false,
        addRemoveLinks: true,
        thumbnailWidth: null,
        thumbnailHeight: null,
        previewTemplate: getTemplate(),
      },
      types: [],
      message: '',
      loading: false,
      action: 1,
    };
  },

  methods: {
    beforeClose() {},

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
        this.$emit("pushAttachmentMessage",JSON.parse(response.xhr.response).data);
      }
    },

    catchError: function(file, message, xhr){
      Swal.fire({
        title: Joomla.JText._("COM_EMUNDUS_ONBOARD_ERROR"),
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

    getTypesByCampaign(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_messenger&controller=messages&task=getdocumentsbycampaign",
      }).then(response => {
        this.types = response.data.data;
      });
    },

    sendMessage(){
      this.$refs.dropzone.processQueue();
    },
  },
}
</script>
