<template>
  <div class="container-evaluation">
    <transition :name="'slide-down'" type="transition">
    <div class="w-form">
      <ul style="padding-left: 0">
        <draggable
            v-model="documents"
            tag="ul"
            class="list-group"
            handle=".handle"
            v-bind="dragOptions"
        >
          <transition-group type="transition" :value="!drag ? 'flip-list' : null">
            <li class="list-group-item"
                :id="'itemDoc' + document.id"
                v-for="(document, indexDoc) in documents"
                :key="indexDoc">
              <em class="fas fa-grip-vertical handle" style="color: #cecece;"></em>
              <div style="display: inline;">
                <span class="draggable">
                  {{ document.title }}
                  <span class="document-allowed_types">({{ document.ext }})</span>
                </span>
                <button type="button" class="buttonDeleteDoc" style="margin-left: 0">
                  <em class="fas fa-pencil-alt"></em>
                </button>
                <button type="button" @click="deleteDoc(indexDoc,document.id)" class="buttonDeleteDoc">
                  <em class="fas fa-times"></em>
                </button>
              </div>
            </li>
          </transition-group>
        </draggable>
      </ul>

      <hr>

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
    </div>
    </transition>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";
import vueDropzone from 'vue2-dropzone'

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
        url: 'index.php?option=com_emundus_onboard&controller=settings&task=uploaddropfiledoc&cid=' + this.campaignId,
        maxFilesize: 10,
        maxFiles: 1,
        autoProcessQueue: true,
        addRemoveLinks: true,
        thumbnailWidth: null,
        thumbnailHeight: null,
        acceptedFiles: 'image/*,application/pdf,.doc,.csv,.xls',
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
    /*uploadNewLogo() {
      this.$refs.dropzone.processQueue();
    }*/
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
</style>
