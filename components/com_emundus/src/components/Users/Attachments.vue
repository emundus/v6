<template>
  <div class="fabrikGroup em-container-profile-view-attach" v-if="attachments_allowed.length > 0">
    <div class="em-flex-row em-flex-space-between em-small-flex-column">
      <h3 class="em-h3">{{ translate('COM_EMUNDUS_USERS_MY_DOCUMENTS') }}</h3>
      <button
          v-if="allowedAttachmentsICanAdd.length > 0"
          class="em-w-auto"
          type="button"
          :class="isapplicant == 1 ? 'btn btn-danger' : 'em-secondary-button'"
          @click="showform = true"
          :style="!showform ? 'opacity: 1' : 'opacity: 0'"
      >
        {{ translate('COM_EMUNDUS_USERS_MY_DOCUMENTS_ADD') }}
      </button>
    </div>
    <p class="em-mt-8" v-if="intro.length > 0">{{ intro }}</p>

    <transition :name="'slide-down'" type="transition">
      <div v-if="showform">
        <hr>

        <div class="em-flex-col-end">
          <button class="em-pointer em-transparent-button" type="button" @click="showform = false">
            <span class="material-icons">close</span>
          </button>
        </div>

        <div class="em-mb-16">
          <label class="fabrikLabel">{{ translate('COM_EMUNDUS_USERS_MY_DOCUMENTS_DOCUMENT_TYPE') }}</label>
          <select class="em-w-100 em-mt-8" v-model="form.attachment_id">
            <option :value="0">{{ translate('COM_EMUNDUS_USERS_MY_DOCUMENTS_PLEASE_SELECT') }}</option>
            <option v-for="attachment_allowed in allowedAttachmentsICanAdd" :value="attachment_allowed.id" :key="attachment_allowed.id">{{ attachment_allowed.value}}</option>
          </select>
        </div>
        <div class="em-mb-16" v-show="form.attachment_id != 0">
          <vue-dropzone
              ref="dropzone"
              :key="reloadDropzone"
              id="customdropzone"
              :include-styling="false"
              :options="dropzoneOptions"
              :useCustomSlot=true
              v-on:vdropzone-file-added="afterAdded"
              v-on:vdropzone-removed-file="afterRemoved"
              v-on:vdropzone-complete="onComplete"
              v-on:vdropzone-error="catchError"
              v-on:vdropzone-sending="addAdditionnalParams">
            <div class="dropzone-custom-content" id="dropzone-message">
              {{ translate('COM_EMUNDUS_USERS_MY_DOCUMENTS_DROP_HERE') }}
            </div>
          </vue-dropzone>
        </div>

        <hr>
      </div>
    </transition>

    <div class="em-mt-16">
      <div v-for="attachment in attachments" class="em-flex-row em-flex-space-between em-mb-16">
        <div>
          <p class="em-label">{{ attachment.value }}</p>
          <span v-if="isDisplayValidationState && attachment.expires_date == null" class="em-help-text">
            {{ translate(displayValidationState(attachment.validation)) }}
          </span>
          <span class="em-help-text">{{ translate('COM_EMUNDUS_USERS_MY_DOCUMENTS_DROP_DATE') }}{{ formattedDate(attachment.date_time,'LL') }}</span>
          <span v-if="attachment.expires_date != null" class="em-expires-text em-red-500-color">{{ translate('COM_EMUNDUS_USERS_MY_DOCUMENTS_EXPIRES_DATE') }}{{ formattedDate(attachment.expires_date,'LL') }}</span>
        </div>
        <div class="em-flew-row em-small-flex-column">
          <a :href="'/' + attachment.filename" download target="_blank"><span class="material-icons em-material-icons-download">file_download</span></a>
          <a @click="deleteAttachment(attachment.default_id, attachment.filename)" class="em-ml-8 em-m-xs-0 em-pointer"><span class="material-icons em-material-icons-download em-red-500-color">delete</span></a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
/* IMPORT YOUR COMPONENTS */
import vueDropzone from 'vue2-dropzone';
import Swal from "sweetalert2";

/* IMPORT YOUR SERVICES */
import attachmentService from "com_emundus/src/services/attachment";
import mixin from "com_emundus/src/mixins/mixin.js";

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
  name: "Attachments",
  components: {
    vueDropzone
  },
  props: {},
  mixins: [mixin],
  data: () => ({
    showform: false,
    reloadDropzone: 0,
    isExpiresDateDisplay: false,

    attachments: [],
    attachments_allowed: [],
    form:{
      attachment_id: 0,
      file: null
    },

    dropzoneOptions: {
      url: '/index.php?option=com_emundus&controller=users&task=uploaddefaultattachment',
      maxFilesize: 10,
      maxFiles: 1,
      acceptedFiles: 'application/pdf',
      autoProcessQueue: true,
      addRemoveLinks: true,
      thumbnailWidth: null,
      thumbnailHeight: null,
      previewTemplate: getTemplate()
    },
  }),
  created() {
    attachmentService.getProfileAttachmentsAllowed().then((response) => {
      this.attachments_allowed = response.attachments;

      if (this.attachments_allowed.length > 0) {
        attachmentService.getProfileAttachments().then((response) => {
          this.attachments = response.attachments;
        });
      }
    });
    this.displayExpiresDate();
  },
  methods: {
    afterAdded() {
      document.getElementById('dropzone-message').style.display = 'none';
    },
    afterRemoved() {
      if(this.$refs.dropzone.getAcceptedFiles().length === 0){
        document.getElementById('dropzone-message').style.display = 'block';
      }
    },
    onComplete(response) {
      if(response.status === 'success'){
        this.showform = false;
        attachmentService.getProfileAttachments().then((rep) => {
          this.attachments = rep.attachments;
        });
      }
    },
    catchError(file, message, xhr) {
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
    addAdditionnalParams(file, xhr, formData) {
      let attachment_found = this.attachments_allowed.find(obj => obj.id == this.form.attachment_id);
      formData.append('attachment_id', this.form.attachment_id);
      formData.append('attachment_lbl', attachment_found.lbl);
    },
    updateDropzoneAcceptedFiles(value) {
      if(typeof value !== 'undefined') {
        let extensions = value.split(';');
        let extensions_mimetype = [];
        for(let i = 0;i < extensions.length;i++){
          let mimetype = this.getMimeTypeFromExtension(extensions[i]);
          if(mimetype !== false) {
            extensions_mimetype.push(mimetype);
          }
        }
        this.$refs.dropzone.options.acceptedFiles = extensions_mimetype.join(',');
        this.reloadDropzone++;
      }
    },
    deleteAttachment(id,filename) {
      Swal.fire({
        title: this.translate('COM_EMUNDUS_USERS_MY_DOCUMENTS_DELETE'),
        text: this.translate('COM_EMUNDUS_USERS_MY_DOCUMENTS_DELETE_CONFIRM'),
        type: "warning",
        showCancelButton: true,
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
      }).then(result => {
        if (result.value) {
          attachmentService.deleteProfileAttachment(id,filename).then((response) => {
            if(response.deleted == true){
              Swal.fire({
                title: this.translate('COM_EMUNDUS_USERS_MY_DOCUMENTS_SUCCESS_DELETED'),
                type: "success",
                showCancelButton: false,
                showConfirmButton: false,
                timer: 2000,
                customClass: {
                  title: 'em-swal-title',
                },
              });
              this.attachments.splice(this.attachments.findIndex(item => item.default_id === id), 1);
            }
          });
        }
      });
    },
    displayExpiresDate(){
      attachmentService.isExpiresDateDisplayed().then((response) => {
        this.isExpiresDateDisplay = response.display_expires_date;
      });
    },
    displayValidationState(state){
      switch (state){
        case '0':
          return 'COM_EMUNDUS_USERS_MY_DOCUMENTS_STATE_WAITING';
        case '1':
          return 'COM_EMUNDUS_USERS_MY_DOCUMENTS_STATE_OK';
        case '-1':
          return 'COM_EMUNDUS_USERS_MY_DOCUMENTS_STATE_INVALID';
        default:
      }
    }
  },
  watch: {
    'form.attachment_id': function(value){
      if (value != 0) {
        let attachment_found = this.attachments_allowed.find(obj => obj.id == value);
        if (attachment_found) {
          this.updateDropzoneAcceptedFiles(attachment_found.allowed_types);
        }
      }
    }
  },
  computed:{
    isapplicant() {
      return this.$store.getters['global/datas'].isapplicant.value;
    },
    intro() {
      return this.$store.getters['global/datas'].attachmentintro.value;
    },
    isDisplayValidationState() {
      return this.$store.getters['global/datas'].displayvalidationstate.value != '0';
    },
    allowedAttachmentsICanAdd() {
      if (this.attachments.length < 1) {
        return this.attachments_allowed;
      } else {
        let allowedAttachments = [];
        let attachmentsByIds = {};

        this.attachments.forEach((attachment) => {
          if (attachmentsByIds[attachment.id] !== undefined && attachmentsByIds[attachment.id] !== null) {
            attachmentsByIds[attachment.id]++;
          } else {
            attachmentsByIds[attachment.id] = 1;
          }
        });

        this.attachments_allowed.forEach((attachment) => {
          if (attachment.nbmax > attachmentsByIds[attachment.id] || attachmentsByIds[attachment.id] === undefined) {
            allowedAttachments.push(attachment);
          }
        });

        return allowedAttachments;
      }
    }
  }
}
</script>

<style scoped>
.material-icons.em-material-icons-download{
  font-size: 24px !important;
}

.fabrikGroup {
 margin-bottom: 0 !important;
 margin-top: 0 !important;
}

legend {
  font-weight: 600;
}

.fabrikLabel, .em-label {
  font-family: var(--font);
  font-size: 16px;
  font-style: normal;
  font-weight: 500 !important;
  line-height: 19px;
  letter-spacing: 0.0015em;
  text-align: left;
  color: #2B2B2B !important;
  margin-bottom: 0 !important;
  padding-top: 0 !important;
}

.em-help-text {
  font-family: var(--font);
  font-size: 14px;
  font-style: normal;
  font-weight: 400;
  line-height: 17px;
  letter-spacing: 0.0025em;
  text-align: left;
  color: #727272;
  display: flex;
  margin-top: 12px;
}
.em-expires-text {
  font-family: var(--font);
  font-size: 14px;
  font-style: normal;
  font-weight: 400;
  line-height: 17px;
  letter-spacing: 0.0025em;
  text-align: left;
  display: flex;
  margin-top: 4px;
}

</style>
