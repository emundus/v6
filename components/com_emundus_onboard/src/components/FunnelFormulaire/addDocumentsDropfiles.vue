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
          {{DropHere}}
        </div>
      </vue-dropzone>

      <hr>

      <ul style="padding-left: 0;margin: 0" class="w-100">
        <draggable
            v-model="documents"
            tag="ul"
            class="list-group"
            style="margin: 0"
            handle=".handle"
            v-bind="dragOptions"
            @end="updateDocumentsOrder"
        >
          <transition-group type="transition" :value="!drag ? 'flip-list' : null">
            <li class="list-group-item"
                :id="'itemDoc' + document.id"
                v-for="document in documents"
                :key="document.id">
              <div class="d-flex justify-content-between">
                <div class="d-flex w-100">
                  <em class="fas fa-grip-vertical handle" style="color: #cecece;"></em>
                  <span class="draggable em-overflow-ellipsis em-max-width-250 em-mr-4">
                    {{ document.title }}
                  </span>
                  <span class="document-allowed_types">({{ document.ext }})</span>
                  <a @click="editName(document)" class="cta-block pointer">
                    <em class="fas fa-pen em-font-size-16"></em>
                  </a>
                </div>
                <button type="button" @click="deleteDoc(indexDoc,document.id)" class="buttonDeleteDoc">
                  <em class="fas fa-times"></em>
                </button>
              </div>
            </li>
          </transition-group>
        </draggable>
      </ul>
    </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import vueDropzone from 'vue2-dropzone';
import draggable from "vuedraggable";

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
  name: "addDocumentsDropfiles",

  components: {
    vueDropzone,
    draggable
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
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=uploaddropfiledoc&cid=' + this.campaignId,
        maxFilesize: 10,
        maxFiles: 1,
        autoProcessQueue: true,
        addRemoveLinks: true,
        thumbnailWidth: null,
        thumbnailHeight: null,
        acceptedFiles: 'image/*,application/pdf,.doc,.csv,.xls,.xlsx,.docx,.odf',
        previewTemplate: getTemplate(),
        dictCancelUpload: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL_UPLOAD"),
        dictCancelUploadConfirmation: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL_UPLOAD_CONFIRMATION"),
        dictRemoveFile: Joomla.JText._("COM_EMUNDUS_ONBOARD_REMOVE_FILE"),
        dictInvalidFileType: Joomla.JText._("COM_EMUNDUS_ONBOARD_INVALID_FILE_TYPE"),
        dictFileTooBig: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILE_TOO_BIG"),
        dictMaxFilesExceeded: Joomla.JText._("COM_EMUNDUS_ONBOARD_MAX_FILES_EXCEEDED"),
      },
      documents: [],
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      DropHere: Joomla.JText._("COM_EMUNDUS_ONBOARD_DROP_FILE_HERE"),
      Error: Joomla.JText._("COM_EMUNDUS_ONBOARD_ERROR"),
      DocumentName: Joomla.JText._("COM_EMUNDUS_ONBOARD_DOCUMENT_NAME"),
      drag: false,
    };
  },

  methods: {
    getDocumentsDropfiles() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=campaign&task=getdocumentsdropfiles",
        params: {
          cid: this.campaignId,
        },
        paramsSerializer: params => {
           return qs.stringify(params);
        }
      }).then(response => {
          this.documents = response.data.documents;
      });
    },
    updateDocumentsOrder(){
      this.documents.forEach((document,index) => {
        document.ordering = index;
      });
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=campaign&task=updateorderdropfiledocuments",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          documents: this.documents,
        })
      });
    },
    editName(doc){
      Swal.fire({
        title: '',
        html: '<div class="form-group campaign-label">' +
            '<label for="campLabel">' + this.DocumentName + '</label><input type="text" id="label_' + doc.id + '" value="' + doc.title + '"/>' +
            '</div>',
        confirmButtonColor: '#de6339',
        showCloseButton: true,
        allowOutsideClick: false,
        customClass: {
          popup: 'swal-popup-custom',
        }
      }).then((value) => {
        if(value){
          let newname = document.getElementById('label_' + doc.id).value
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=campaign&task=editdocumentdropfile",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              did: doc.id,
              name: newname
            })
          }).then(() => {
            doc.title = newname;
          });
        }
      });
    },
    deleteDoc(index,id) {
      this.documents.splice(index, 1);
      axios({
        method: "post",
        url: "index.php?option=com_emundus_onboard&controller=campaign&task=deletedocumentdropfile",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          did : id,
        })
      });
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
      this.documents.push(JSON.parse(response.xhr.response));
      this.$refs.dropzone.removeFile(response);
    },
    catchError: function(file, message){
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
  },

  computed: {
    dragOptions() {
      return {
        animation: 200,
        group: "description",
        disabled: false,
        ghostClass: "ghost"
      };
    },
  },

  created() {
    this.getDocumentsDropfiles()
  }
};
</script>
<style scoped>
  .fa-file-upload{
    font-size: 25px;
    margin-right: 20px;
  }

  .list-group-item{
    border: 2px solid #ececec;
    margin-bottom: 10px;
    border-radius: 5px;
  }
</style>
