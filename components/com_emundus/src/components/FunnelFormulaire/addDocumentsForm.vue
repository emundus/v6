<template>
  <div class="container-evaluation">
    <transition :name="'slide-down'" type="transition">
      <div class="w-form">
        <vue-dropzone
            ref="dropzone"
            id="customdropzone"
            :include-styling="false"
            :options="dropzoneOptions"
            :useCustomSlot=true
            v-on:vdropzone-file-added="afterAdded"
            v-on:vdropzone-removed-file="afterRemoved"
            v-on:vdropzone-success="onComplete"
            v-on:vdropzone-error="catchError">
          <div class="dropzone-custom-content" id="dropzone-message">
            <em class="fas fa-file-upload"></em>
            {{ DropHere }}
          </div>
        </vue-dropzone>

        <hr>

        <ul style="padding-left: 0;margin: 0" class="w-100">
          <li class="list-group-item"
              v-for="(document, indexDoc) in documents"
              :key="indexDoc">
            <div class="em-flex-row em-flex-space-between">
                <span class="draggable">
                  {{ document.name }}
                </span>
              <button type="button" @click="deleteDoc(indexDoc,document.id)" class="buttonDeleteDoc">
                <em class="fas fa-times"></em>
              </button>
            </div>
            <a @click="editName(document)" class="cta-block pointer"
               style="font-size: 27px;float: right;position: relative;bottom: -20px;">
              <em class="fas fa-pen"></em>
            </a>
          </li>
        </ul>
      </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import vueDropzone from 'vue2-dropzone';

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
  name: "addDocumentsForm",

  components: {
    vueDropzone
  },

  props: {
    funnelCategorie: String,
    profileId: Number,
    campaignId: Number,
    langue: String,
    menuHighlight: Number,
    manyLanguages: Number
  },

  data() {
    return {
      dropzoneOptions: {
        url: 'index.php?option=com_emundus&controller=settings&task=uploadformdoc&pid=' + this.profileId,
        maxFilesize: 10,
        maxFiles: 1,
        autoProcessQueue: true,
        addRemoveLinks: true,
        thumbnailWidth: null,
        thumbnailHeight: null,
        acceptedFiles: 'image/*,application/pdf,.doc,.csv,.xls,.xlsx,.docx,.odf',
        previewTemplate: getTemplate(),
        dictCancelUpload: this.translate("COM_EMUNDUS_ONBOARD_CANCEL_UPLOAD"),
        dictCancelUploadConfirmation: this.translate("COM_EMUNDUS_ONBOARD_CANCEL_UPLOAD_CONFIRMATION"),
        dictRemoveFile: this.translate("COM_EMUNDUS_ONBOARD_REMOVE_FILE"),
        dictInvalidFileType: this.translate("COM_EMUNDUS_ONBOARD_INVALID_FILE_TYPE"),
        dictFileTooBig: this.translate("COM_EMUNDUS_ONBOARD_FILE_TOO_BIG"),
        dictMaxFilesExceeded: this.translate("COM_EMUNDUS_ONBOARD_MAX_FILES_EXCEEDED"),
      },
      documents: [],
      Retour: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: this.translate("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      DropHere: this.translate("COM_EMUNDUS_ONBOARD_DROP_FILE_HERE"),
      Error: this.translate("COM_EMUNDUS_ONBOARD_ERROR"),
      DocumentName: this.translate("COM_EMUNDUS_ONBOARD_DOCUMENT_NAME"),
    };
  },

  methods: {
    getDocumentsDropfiles() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=campaign&task=getdocumentsform",
        params: {
          pid: this.profileId,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.documents = response.data.documents;
      });
    },
    editName(doc) {
      Swal.fire({
        title: '',
        html: '<div class="form-group campaign-label">' +
            '<label for="campLabel">' + this.DocumentName + '</label><input type="text" id="label_' + doc.id + '" value="' + doc.name + '"/>' +
            '</div>',
        confirmButtonColor: '#de6339',
        showCloseButton: true,
        allowOutsideClick: false,
        customClass: {
          popup: 'swal-popup-custom',
        }
      }).then((value) => {
        if (value) {
          let newname = document.getElementById('label_' + doc.id).value
          axios({
            method: "post",
            url: "index.php?option=com_emundus&controller=campaign&task=editdocumentform",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              pid: this.profileId,
              did: doc.id,
              name: newname
            })
          }).then(() => {
            doc.name = newname;
          });
        }
      });
    },
    deleteDoc(index, id) {
      this.documents.splice(index, 1);
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=campaign&task=deletedocumentform",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          pid: this.profileId,
          did: id,
        })
      });
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
      this.documents.push(JSON.parse(response.xhr.response));
      this.$refs.dropzone.removeFile(response);
    },
    catchError: function (file, message) {
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
  },

  created() {
    this.getDocumentsDropfiles()
  }
};
</script>
<style scoped>
.fa-file-upload {
  font-size: 25px;
  margin-right: 20px;
}

.list-group-item {
  border: 2px solid #ececec;
  margin-bottom: 10px;
  border-radius: 5px;
  height: 100px;
}
</style>
