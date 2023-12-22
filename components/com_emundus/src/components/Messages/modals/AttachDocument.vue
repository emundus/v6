<template>
  <div class="messages__vue_attach_document">
    <div :id="'attach_documents' + fnum">
      <div
          :name="'attach_documents' + fnum"
          transition="nice-modal-fade"
          :adaptive="true"
          height="auto"
          width="30%"
          :scrollable="true"
          :delay="100"
          :clickToClose="true"
          @closed="beforeClose"
          @opened="getTypesByCampaign"
      >
	      <div class="messages__attach_header em-p-16 em-w-100 em-flex-row-justify-end">
		      <span class="material-icons-outlined em-pointer" @click="$emit('close')">close</span>
	      </div>
        <div class="messages__attach_content">
          <ul class="messages__attach_actions_tabs" v-if="!applicant">
            <li class="messages__attach_action em-mr-8" @click="action = 1" :class="action === 1 ? 'messages__attach_action__current' : ''">{{translations.sendDocument}}</li>
            <li class="messages__attach_action em-mr-8" @click="action = 2" :class="action === 2 ? 'messages__attach_action__current' : ''">{{translations.askDocument}}</li>
          </ul>

	        <div class="messages_action_container em-pt-16">
		        <div v-if="action === 1">
	            <label v-if="applicant">{{translations.sendDocument}}</label>
	            <div v-if="applicant && types.length > 0" class="messages__attach_applicant_doc">
	              <label for="applicant_attachment_input">{{translations.typeAttachment}}</label>
	              <select v-model="attachment_input" id="applicant_attachment_input">
	                <option :value="0">{{translations.pleaseSelect }}</option>
	                <option v-for="type in types" :value="type.id">{{type.value}}</option>
	              </select>
	            </div>
	            <vue-dropzone
			            ref="dropzone"
			            id="customdropzone_messenger"
			            :include-styling="false"
			            :options="dropzoneOptions"
			            :useCustomSlot=true
			            v-on:vdropzone-file-added="afterAdded"
			            v-on:vdropzone-thumbnail="thumbnail"
			            v-on:vdropzone-removed-file="afterRemoved"
			            v-on:vdropzone-complete="onComplete"
			            v-on:vdropzone-error="catchError"
			            v-on:vdropzone-sending="sendingEvent">
	              <div class="dropzone-custom-content" id="dropzone-message">
	                <em class="fas fa-file-image"></em>
	                {{translations.DropHere}}
	              </div>
	            </vue-dropzone>
	            <button type="button" class="messages__send_button" @click="sendMessage" v-if="applicant">
	                  {{ translations.send }}
	            </button>
	          </div>
	          <div v-else>
	            <label for="attachment_input">{{translations.typeAttachment}}</label>
	            <select v-model="attachment_input" id="attachment_input">
	              <option :value="0">{{translations.pleaseSelect }}</option>
	              <option v-for="type in types" :value="type.id">{{type.value}}</option>
	            </select>
	          </div>
	        </div>
        </div>
      </div>
    </div>
    <div class="loader" v-if="loading"></div>
  </div>
</template>

<script>
import axios from "axios";
import vueDropzone from 'vue2-dropzone'

import "../../../assets/css/messenger.scss";
import Swal from "sweetalert2";

const qs = require("qs");

const getTemplate = () => `
<div class="dz-preview dz-file-preview">
  <div class="dz-image">
    <img src="/images/emundus/messenger/file_download.svg" style="max-width: 50px"/>
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
        typeAttachment: Joomla.JText._("COM_EMUNDUS_MESSENGER_TYPE_ATTACHMENT"),
        pleaseSelect: Joomla.JText._("COM_EMUNDUS_PLEASE_SELECT")
      },
      types: [],
      message_input: '',
      attachment_input: 0,
      loading: false,
      action: 1,
      dropzoneOptions: {
        url: 'index.php?option=com_emundus&controller=messenger&task=uploaddocument&fnum=' + this.fnum + '&applicant=' + this.applicant,
        maxFilesize: 10,
        maxFiles: 5,
        autoProcessQueue: false,
        addRemoveLinks: true,
        thumbnailWidth: null,
        thumbnailHeight: null,
        previewTemplate: getTemplate(),
      },
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
      this.message_input = '';
      if(response.status == 'success'){
        this.$emit("pushAttachmentMessage",JSON.parse(response.xhr.response).data);
      }
    },

    sendingEvent(file, xhr, formData){
      formData.append('message', this.message_input);
      formData.append('attachment', this.attachment_input);
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
        url: "index.php?option=com_emundus&controller=messenger&task=getdocumentsbycampaign",
        params: {
          fnum: this.fnum,
          applicant: this.applicant
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.types = response.data.data;
      });
    },

    askAttachment(){
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=messenger&task=askattachment",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          fnum: this.fnum,
          attachment: this.attachment_input,
          message: this.message_input,
        })
      }).then(response => {
        this.$emit("pushAttachmentMessage",response.data.data);
      });
    },

    sendMessage(message){
      if(this.action === 1) {
        if(!this.applicant) {
          this.message_input = message;
        }
        this.$refs.dropzone.processQueue();
      } else {
        if (this.attachment_input) {
          this.message_input = 'Demande de document : ';
	        const type = this.types.find(type => type.id == this.attachment_input);
					if (type) {
						this.message_input += type.value;
					}

          this.askAttachment();
        }
      }
    },
  },
}
</script>

<style scoped>
.messages__vue_attach_document {
	background-color: white;
}

.messages__send_button{
  margin: 20px 0;
  float: right;
}
</style>
