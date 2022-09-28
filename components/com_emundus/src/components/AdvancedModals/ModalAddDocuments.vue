<template>
  <!-- modalC -->
  <span :id="'modalAddDocuments'">
    <modal
        :name="'modalAddDocuments'"
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

      <div class="em-flex-row em-flex-space-between em-mb-16">
        <span class="em-h4" v-if="currentDoc ==null">
          {{translations.createDocument}}
        </span>
        <span class="em-h4" v-if="currentDoc != null">
          {{translations.createDocument}}
        </span>
        <button class="em-pointer em-transparent-button" @click.prevent="$modal.hide('modalAddDocuments')">
          <span class="material-icons-outlined">close</span>
        </button>
      </div>

      <div>
        <div class="em-mb-16">
          <a class="em-flex-row">
            <div class="em-toggle">
              <input type="checkbox" class="em-toggle-check" name="require" v-model="req" @click="updateRequireMandatory()"/>
              <strong class="b em-toggle-switch"></strong>
              <strong class="b em-toggle-track"></strong>
            </div>
            <span for="require" class="em-ml-8 em-pointer" @click="updateRequireMandatory()">{{ translations.Required }}</span>
          </a>
        </div>

        <div class="em-mb-16" v-if="can_be_deleted">
          <button type="button" class="em-tertiary-button" @click="deleteModel">{{translations.DeleteDocTemplate}}</button>
        </div>

        <div class="em-mb-16" v-if="currentDoc ==null">
          <label for="modelName" class="em-w-100">{{ translations.DocTemplate }} :</label>
          <select v-model="doc" id="modelName" class="em-w-100" name="modelName" :disabled="Object.keys(models).length <= 0">
            <option :value="null"></option>
            <option v-for="(modelT, index) in models" :key="'option_' + index" :value="modelT.id">{{ modelT.name[langue] }}  ({{ modelT.allowed_types }})</option>
          </select>
        </div>

        <div class="em-mb-16">
          <label for="name">{{ translations.Name }}* :</label>
          <input type="text" class="em-w-100" maxlength="100" v-model="form.name[langue]" id="name" :class="{ 'is-invalid': errors.name}"/>
          <p v-if="errors.name" class="em-red-500-color">
            <span class="em-red-500-color">{{ translations.NameRequired }}</span>
          </p>
        </div>

        <div class="em-mb-16">
          <label for="description">{{ translations.Description }} :</label>
          <editor :height="'20em'" :text="form.description[langue]" :lang="langue" :enable_variables="false" :id="'editor_fr'" :key="dynamicComponent" v-model="form.description[langue]"></editor>
        </div>
        <div class="em-mb-16">
          <label for="nbmax">{{ translations.MaxPerUser }}* :</label>
          <input type="number" class="em-w-100" min="1" v-model="form.nbmax" id="nbmax"
                 :class="{ 'is-invalid': errors.nbmax}"/>
          <p v-if="errors.nbmax" class="em-red-500-color">
            <span class="em-red-500-color">{{ translations.MaxRequired }}</span>
          </p>
        </div>

        <div class="em-mb-16">
          <label for="nbmax" :class="{ 'is-invalid': errors.selectedTypes}">{{ translations.FileType }}* :</label>
          <div :class="{ 'is-invalid': errors.selectedUsers}">
            <div v-for="(type, index) in types" :key="index" class="em-flex-row em-mb-8">
              <input type="checkbox" v-model="form.selectedTypes[type.value]" @change="selectType(type)">
              <div class="em-ml-8">
                  <p>{{ type.title }} ({{ type.value }})</p>
              </div>
            </div>
          </div>
          <p v-if="errors.selectedTypes" class="em-red-500-color">
            <span class="em-red-500-color">{{ translations.TypeRequired }}</span>
          </p>
        </div>
      </div>

      <!-- image resolution -->
      <div id="imageResolutionZone" v-if="show == true">
          <hr/>
          <h4 class="image-resolution-header">{{ translations.ImageDimensionsTitle }}</h4>
          <div class="image-resolution-tooltips">
            <i>{{ translations.ImageResolutionTooltips }}</i>
        </div>
        <br/>
        <div class="form-group">
          <label for="image-min-width">{{ translations.ImageWidth }}</label>
          <div class="input-can-translate em-flex-row em-flex-space-between">
              <input type="number" maxlength="100" class="form__input field-general w-input mb-0" id="image-min-width" min="300" v-model="form.minResolution.width" style="max-width: 48%" @keyup="ZeroOrNegative()" v-on:keydown.tab="ZeroOrNegative()" :placeholder="translations.MinResolutionPlaceholder"/>
              <input type="number" maxlength="100" class="form__input field-general w-input mb-0" id="image-max-width" min="300" v-model="form.maxResolution.width" style="max-width: 48%" @keyup="ZeroOrNegative()" v-on:keydown.tab="ZeroOrNegative()" :placeholder="translations.MaxResolutionPlaceholder"/>
          </div>
          <transition name="fade">
              <span style="font-size: smaller; color:red" v-if=" errorWidth.error "> {{ errorWidth.message}} </span>
          </transition>
        </div>

        <div class="form-group">
          <label for="image-min-height">{{ translations.ImageHeight }}</label>
          <div class="input-can-translate em-flex-row em-flex-space-between">
              <input type="number" maxlength="100" class="form__input field-general w-input mb-0" id="image-min-height" min="300" v-model="form.minResolution.height" style="max-width: 48%" @keyup="ZeroOrNegative()" v-on:keydown.tab="ZeroOrNegative()" :placeholder="translations.MinResolutionPlaceholder"/>
              <input type="number" maxlength="100" class="form__input field-general w-input mb-0" id="image-max-height" min="300" v-model="form.maxResolution.height" style="max-width: 48%" @keyup="ZeroOrNegative()" v-on:keydown.tab="ZeroOrNegative()" :placeholder="translations.MaxResolutionPlaceholder"/>
          </div>
          <transition name="fade">
              <span style="font-size: smaller; color:red" v-if=" errorHeight.error"> {{ errorHeight.message}} </span>
          </transition>
        </div>

      </div>
      <div class="em-flex-row em-flex-space-between em-mb-8">
        <button
            type="button"
            class="em-secondary-button em-w-auto"
            @click.prevent="$modal.hide('modalAddDocuments')">
          {{ translations.Retour }}
        </button>
        <button type="button"
                class="em-primary-button em-w-auto"
                @click.prevent="createNewDocument()">
          {{ translations.Continuer }}
        </button>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";

const qs = require("qs");
import Translation from "../translation";
import Swal from "sweetalert2";
import Editor from "../../components/editor";

export default {
  name: "modalAddDocuments",
  props: {
    cid: Number,
    pid: Number,
    currentDoc: Number,
    manyLanguages: Number,
    langue: String,
  },
  components: {
    Translation,
    Editor,
  },
  data() {
    return {
      show: false,
      dynamicComponent: 0,
      errorWidth: {
        error: false,
        message: ""
      },
      errorHeight: {
        error: false,
        message: ""
      },
      doc: null,
      model: {
        allowed_types: '',
        can_be_deleted: false,
        category: "",
        description: {
          'fr': "",
          'en': "",

        },
        id: "",
        lbl: "",
        mandatory: 0,
        name: {
          'fr':'',
          'en':'',
        },
        nbmax: "",
        ocr_keywords: null,
        ordering: "",
        published: "",
        value: "",
        video_max_length:'',

        minResolution: {
          width: 300,
          height: 300,
        },
        // max resolution by default
        maxResolution: {
          width: null,
          height: null,
        },
      },
      form: {
        name: {
          fr: '',
          en: ''
        },
        description: {
          fr: '',
          en: ''
        },
        nbmax: 1,
        selectedTypes: {
          pdf: false,
          'jpeg;jpg;png': false,
          'doc;docx;odt;ppt;pptx': false,
          'xls;xlsx;odf': false,

        },
        mandatory: 0,
        //min resolution by default
        minResolution: {
          width: null,
          height: null,
        },
        // max resolution by default
        maxResolution: {
          width: null,
          height: null,
        },
      },
      can_translate: {
        name: false,
        description: false
      },
      req: false,
      errors: {
        name: false,
        nbmax: false,
        selectedTypes: false
      },
      types: [
        {
          title: this.translate("COM_EMUNDUS_ONBOARD_PDF_DOCUMENTS"),
          value: 'pdf'
        },
        {
          title: this.translate("COM_EMUNDUS_ONBOARD_PICTURES_DOCUMENTS"),
          value: 'jpeg;jpg;png'
        },
        {
          title: this.translate("COM_EMUNDUS_ONBOARD_OFFICE_DOCUMENTS"),
          value: 'doc;docx;odt;ppt;pptx'
        },
        {
          title: this.translate("COM_EMUNDUS_ONBOARD_EXCEL_DOCUMENTS"),
          value: 'xls;xlsx;odf'
        },
        {
          title: this.translate("COM_EMUNDUS_ONBOARD_AUDIO"),
          value: 'mp3;wav;aac;flac'
        },
      ],

      selectedTypes: [],
      models: [],
      can_be_deleted: false,
      translations: {
        DeleteDocTemplate: "COM_EMUNDUS_ONBOARD_DELETE_TEMPLATE_DOC",
        createDocument: "COM_EMUNDUS_ONBOARD_CREATE_DOCUMENT",
        editDocument: "COM_EMUNDUS_ONBOARD_EDIT_DOCUMENT",
        Retour: "COM_EMUNDUS_ONBOARD_ADD_RETOUR",
        Continuer: "COM_EMUNDUS_ONBOARD_OK",
        DocTemplate: "COM_EMUNDUS_ONBOARD_TEMPLATE_DOC",
        Name: "COM_EMUNDUS_ONBOARD_LASTNAME",
        Description: "COM_EMUNDUS_ONBOARD_ADDDOC_DESCRIPTION",
        MaxPerUser: "COM_EMUNDUS_ONBOARD_MAXPERUSER",
        FileType: "COM_EMUNDUS_ONBOARD_FILETYPE_ACCEPTED",
        NameRequired: "COM_EMUNDUS_ONBOARD_PROG_REQUIRED_LABEL",
        MaxRequired: "COM_EMUNDUS_ONBOARD_MAXPERUSER_REQUIRED",
        TypeRequired: "COM_EMUNDUS_ONBOARD_FILETYPE_ACCEPTED_REQUIRED",
        TranslateEnglish: "COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH",
        Required: "COM_EMUNDUS_ONBOARD_ACTIONS_REQUIRED",
        ImageWidth: "COM_EMUNDUS_ONBOARD_IMAGE_WIDTH",
        ImageHeight: "COM_EMUNDUS_ONBOARD_IMAGE_HEIGHT",
        ImageResolutionTooltips: "COM_EMUNDUS_ONBOARD_IMAGE_RESOLUTION_TOOLTIPS",
        MinResolutionPlaceholder: "COM_EMUNDUS_ONBOARD_MIN_RESOLUTION_PLACEHOLDER",
        MaxResolutionPlaceholder: "COM_EMUNDUS_ONBOARD_MAX_RESOLUTION_PLACEHOLDER",
        ErrorResolution: "COM_EMUNDUS_ONBOARD_ERROR_RESOLUTION",
        ErrorResolutionNegative: "COM_EMUNDUS_ONBOARD_ERROR_RESOLUTION_NEGATIVE",
        ErrorResolutionTooSmall: "COM_EMUNDUS_ONBOARD_ERROR_RESOLUTION_TOO_SMALL",
        ErrorResolutionNotNumber: "COM_EMUNDUS_ONBOARD_ERROR_RESOLUTION_NOT_NUMBER",
        ImageDimensionsTitle: "COM_EMUNDUS_ONBOARD_IMAGE_DIMENSION_TITLE",
      }
    };
  },
  methods: {
    beforeClose() {
      this.show = false;
      this.doc = null;
      this.currentDoc = null;
      this.can_be_deleted = false;
      this.errorWidth.error = false;
      this.errorHeight.error = false;

      this.form = {
        name: {
          fr: '',
          en: ''
        },
        description: {
          fr: '',
          en: ''
        },
        nbmax: 1,
        selectedTypes: {
          pdf: false,
          'jpeg;jpg;png;gif': false,
          'doc;docx;odt;ppt;pptx': false,
          'xls;xlsx;odf': false,

        },
        minResolution: {
          width: null,
          height: null,
        },
        // max resolution by default
        maxResolution: {
          width: null,
          height: null,
        },
      };

      this.$emit("modalClosed");
    },
    beforeOpen() {
      this.getModelsDocs();
    },
    createNewDocument() {

        this.errors = {
          name: false,
          nbmax: false,
          selectedTypes: false
        };

        if (this.form.name[this.langue] === '') {
          this.errors.name = true;

          return 0;
        }
        if (this.form.nbmax === '' || this.form.nbmax === 0) {
          this.errors.nbmax = true;
          return 0;
        }
        if (Object.values(this.form.selectedTypes).every((val) => val === false)) {
          this.errors.selectedTypes = true;
          return 0;
        }

        if (this.can_translate.name === false) {

          if (this.manyLanguages == 0 && this.langue == "en") {
            this.form.name.fr = this.form.name.en
          }
          if (this.manyLanguages == 0 && this.langue === "fr") {
            this.form.name.en = this.form.name.fr;
          }
        }

        if (this.can_translate.description === false) {

          if (this.manyLanguages == 0 && this.langue == "en") {

            this.form.description.fr = this.form.description.en;

          } else {

            this.form.description.en = this.form.description.fr;

          }
        }

        let types = [];
        Object.keys(this.form.selectedTypes).forEach(key => {
          if (this.form.selectedTypes[key] == true) {
            types.push(key);
          }
        });

        let params = {
          document: this.form,
          types: types,
          cid: this.cid,
          pid: this.pid,
          isModeleAndUpdate: false
        }

        if (this.form.name[this.langue] != this.model.value && this.currentDoc == null) {
          params.isModeleAndUpdate = true;
        }

        let y = [];
        if (this.model.allowed_types.includes('pdf')) {
          y.push('pdf');
        }
        if (this.model.allowed_types.includes('jpg') || this.model.allowed_types.includes('jpeg') || this.model.allowed_types.includes('png') || this.model.allowed_types.includes('gif')) {
          y.push('jpeg;jpg;png;gif')
        }
        if (this.model.allowed_types.includes('xls') || this.model.allowed_types.includes('xlsx') || this.model.allowed_types.includes('odf')) {
          y.push('xls;xlsx;odf')
        }

        let diffenceBetweenNewType = y.filter(x => !types.includes(x));


        if (diffenceBetweenNewType.length > 0 && this.currentDoc == null) {
          params.isModeleAndUpdate = true;
        }

        let image_error = false;
        if(types.includes('jpeg;jpg;png;gif')){
          image_error = this.isImageError()
        }

      if(!image_error) {
        let url = 'index.php?option=com_emundus&controller=campaign&task=createdocument';

        if (this.form.name[this.langue] === this.model.value && this.doc != null) {
          url = 'index.php?option=com_emundus&controller=campaign&task=updatedocument';

          params.did = this.doc;

        }
        if (this.currentDoc != null) {

          url = 'index.php?option=com_emundus&controller=campaign&task=updatedocument';
          params.did = this.doc;


        }

        axios({
          method: "post",
          url: url,
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify(params)
        }).then(() => {
          this.req = false;
          this.$emit("UpdateDocuments");
          this.$modal.hide('modalAddDocuments')
        });
      } else {
        return false;
      }
    },

    isImageError() {
      let sendError = false;

      const min_contains_value = Object.values(this.form.minResolution).some(v => v);
      const max_contains_value = Object.values(this.form.maxResolution).some(v => v);

      /// both width and height are empty
      if(!min_contains_value && !max_contains_value) {
        document.getElementById('image-min-width').style.setProperty('border-color', '#ccc', 'important');    /// set css
        document.getElementById('image-max-width').style.setProperty('border-color', '#ccc', 'important');    /// set css

        document.getElementById('image-min-height').style.setProperty('border-color', '#ccc', 'important');    /// set css
        document.getElementById('image-max-height').style.setProperty('border-color', '#ccc', 'important');    /// set css

        sendError = false;
        this.errorWidth.message = "";
        this.errorHeight.message = "";
      }

      else {
        /// check width
          if(this.form.minResolution.height && this.form.maxResolution.height) {
            if (parseInt(this.form.minResolution.width) >= 300 && parseInt(this.form.maxResolution.width) >= 300) {
              if ((parseInt(this.form.minResolution.width) > parseInt(this.form.maxResolution.width))) {
                this.errorWidth.error = true;
                this.errorWidth.message = (parseInt(this.form.minResolution.width) <= 0) ? this.translations.ErrorResolutionNegative : this.translations.ErrorResolution;
                document.getElementById('image-min-width').style.setProperty('border-color', 'red', 'important');
                sendError = true;
              } else {
                this.errorWidth.error = false;
                this.errorWidth.message = "";
                document.getElementById('image-min-width').style.setProperty('border-color', '#ccc', 'important');    /// set css
                document.getElementById('image-max-width').style.setProperty('border-color', '#ccc', 'important');    /// set css
              }
            } else {
              this.errorWidth.error = true;
              this.errorWidth.message = this.translations.ErrorResolutionTooSmall;
              sendError = true;

              if (parseInt(this.form.minResolution.width) < 300) {
                document.getElementById('image-min-width').style.setProperty('border-color', 'red', 'important');
              }

              if (parseInt(this.form.maxResolution.width) < 300) {
                document.getElementById('image-max-width').style.setProperty('border-color', 'red', 'important');
              }
            }
          } else {
            this.errorHeight.error = true;
            this.errorHeight.message = this.translations.ErrorResolutionTooSmall;
            sendError = true;

            /// if min_height || max_height not exist
            if(!this.form.minResolution.height || this.form.minResolution.height === '') {
              document.getElementById('image-min-height').style.setProperty('border-color', 'red', 'important');
            }

            if(!this.form.maxResolution.height || this.form.maxResolution.height === '') {
              document.getElementById('image-max-height').style.setProperty('border-color', 'red', 'important');
            }

        }

        //// check height
          if(this.form.minResolution.width && this.form.maxResolution.width) {
            if (parseInt(this.form.minResolution.height) >= 300 && parseInt(this.form.maxResolution.height) >= 300) {
              if ((parseInt(this.form.minResolution.height) > parseInt(this.form.maxResolution.height))) {
                this.errorHeight.error = true;
                this.errorHeight.message = (parseInt(this.form.minResolution.height) <= 0) ? this.translations.ErrorResolutionNegative : this.translations.ErrorResolution;
                document.getElementById('image-min-height').style.setProperty('border-color', 'red', 'important');
                sendError = true;
              } else {
                this.errorHeight.error = false;
                this.errorHeight.message = "";
                document.getElementById('image-min-height').style.setProperty('border-color', '#ccc', 'important');    /// set css
                document.getElementById('image-max-height').style.setProperty('border-color', '#ccc', 'important');    /// set css
              }
            } else {
              this.errorHeight.error = true;
              this.errorHeight.message = this.translations.ErrorResolutionTooSmall;
              sendError = true;

              if (parseInt(this.form.minResolution.height) < 300) {
                document.getElementById('image-min-height').style.setProperty('border-color', 'red', 'important');
              }

              if (parseInt(this.form.maxResolution.height) < 300) {
                document.getElementById('image-max-height').style.setProperty('border-color', 'red', 'important');
              }
            }
          } else {
            this.errorWidth.error = true;
            this.errorWidth.message = this.translations.ErrorResolutionTooSmall;
            sendError = true;

            /// if min_height || max_height not exist
            if(!this.form.minResolution.width || this.form.minResolution.width === '') {
              document.getElementById('image-min-width').style.setProperty('border-color', 'red', 'important');
            }

            if(!this.form.maxResolution.width || this.form.maxResolution.width === '') {
              document.getElementById('image-max-width').style.setProperty('border-color', 'red', 'important');
            }
          }
        }
      return sendError;
    },

    deleteModel(){
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_DELETE_TEMPLATE_DOC"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#12db42',
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
      }).then(result => {
        if(result.value){
          axios({
            method: "post",
            url: "index.php?option=com_emundus&controller=form&task=deletemodeldocument",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              did: this.doc,
            })
          }).then((response) => {
            if(response.data.allowed){
              Swal.fire({
                backdrop: true,
                title: this.translate("COM_EMUNDUS_ONBOARD_MODEL_DELETED"),
                text: this.translate("COM_EMUNDUS_ONBOARD_MODEL_DELETED_TEXT"),
                showConfirmButton: true,
                timer: 5000,
                customClass: {
                  title: 'em-swal-title',
                  confirmButton: 'em-swal-confirm-button',
                  actions: "em-swal-single-action",
                },
              }).then(() => {
                this.$modal.hide('modalAddDocuments')
              });
            } else {
              Swal.fire({
                backdrop: true,
                title: this.translate("COM_EMUNDUS_ONBOARD_CANNOT_DELETE"),
                text: this.translate("COM_EMUNDUS_ONBOARD_CANNOT_DELETE_TEXT"),
                showConfirmButton: true,
                timer: 5000,
                customClass: {
                  title: 'em-swal-title',
                  confirmButton: 'em-swal-confirm-button',
                  actions: "em-swal-single-action",
                },
              })
            }
          });
        }
      });
    },

    getModelsDocs() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=form&task=getundocuments",
      }).then(response => {
        this.models = response.data.data;
        if (this.currentDoc != null) {
          this.doc = this.currentDoc;
        }
      });
    },
    updateRequireMandatory() {
      if (this.req == 1) {
        this.form.mandatory = 0
      } else {
        this.form.mandatory = 1
      }
    },

    selectType(e) {
      let raw_val = e.value;
      let val = raw_val.split(';');

      if(val.includes('jpeg') || val.includes('jpg') || val.includes('png') || val.includes('gif')) {
        this.show = !this.show;
      }
    },

    ZeroOrNegative() {
      let id = event.target.id;
      let val = parseInt(event.target.value);

      let min_width = this.form.minResolution.width;
      let max_width = this.form.maxResolution.width;

      let min_height = this.form.minResolution.height;
      let max_height = this.form.maxResolution.height;

      if (!isNaN(val)) {

        if (val < 300) {
          document.getElementById(id).style.color = "red";
          document.getElementById(id).style.setProperty('border-color', 'red', 'important');

          if (id == 'image-min-width' || id == 'image-max-width') {
            this.errorWidth.error = true;

            if(parseInt(min_width) < 300 || !min_width) {
              document.getElementById('image-min-width').style.setProperty('border-color', 'red', 'important');
            }

            if(parseInt(max_width) < 300 || !max_width) {
              document.getElementById('image-max-width').style.setProperty('border-color', 'red', 'important');
            }

            this.errorWidth.message = this.translations.ErrorResolutionTooSmall;
          }

          if (id == 'image-min-height' || id == 'image-max-height') {
            this.errorHeight.error = true;

            if(parseInt(min_height) < 300 || !min_height) {
              document.getElementById('image-min-height').style.setProperty('border-color', 'red', 'important');
            }

            if(parseInt(max_height) < 300 || !max_height) {
              document.getElementById('image-max-height').style.setProperty('border-color', 'red', 'important');
            }

            this.errorHeight.message = this.translations.ErrorResolutionTooSmall;
          }
        } else {
          document.getElementById(id).style.color = "unset";
          document.getElementById(id).style.setProperty('border-color', '#ccc', 'important');

          if(parseInt(min_width) >= 300 && parseInt(max_width) >= 300) {
            if(parseInt(min_width) > parseInt(max_width)) {
              document.getElementById('image-min-width').style.setProperty('border-color', 'red', 'important');
              this.errorWidth.error = true;
              this.errorWidth.message = this.translations.ErrorResolution;
            } else {
              document.getElementById('image-min-width').style.setProperty('border-color', '#ccc', 'important');
              this.errorWidth.error = false;
              this.errorWidth.message = "";
            }
          }

          if(min_height >= 300 && max_height >= 300) {
            if(parseInt(min_height) > parseInt(max_height)) {
              document.getElementById('image-min-height').style.setProperty('border-color', 'red', 'important');
              this.errorHeight.error = true;
              this.errorHeight.message = this.translations.ErrorResolution;
            } else {
              document.getElementById('image-min-height').style.setProperty('border-color', '#ccc', 'important');
              this.errorHeight.error = false;
              this.errorHeight.message = "";
            }
          }

          if(min_width >= 300 && max_width >= 300 && min_height >= 300 && max_height >= 300 && max_width >= min_width && max_height >= min_height) {
            this.errorWidth.error = false;
            this.errorWidth.message = "";
            this.errorHeight.error = false;
            this.errorHeight.message = "";
          }
        }
      }
      else {
        document.getElementById(id).style.color = "red";
        document.getElementById(id).style.setProperty('border-color', 'red', 'important');

        if (id == 'image-min-width' || id == 'image-max-width') {
          this.errorWidth.error = true;
          this.errorWidth.message = this.translations.ErrorResolutionTooSmall;
        }

        if (id == 'image-min-height' || id == 'image-max-height') {
          this.errorHeight.error = true;
          this.errorHeight.message = this.translations.ErrorResolutionTooSmall;
        }
      }
    }
  },

  watch: {
    doc: function (val) {
      if (val != null) {
        this.model = this.models.find(model => model.id == val);

        this.form.name = this.model.name;
        this.form.description = this.model.description;
        this.form.mandatory = parseInt(this.model.mandatory);
        this.form.minResolution = {};
        this.form.maxResolution = {};

        this.req = parseInt(this.model.mandatory);

        this.form.selectedTypes.pdf = this.model.allowed_types.includes('pdf')

        if (this.model.allowed_types.includes('jpg') || this.model.allowed_types.includes('jpeg') || this.model.allowed_types.includes('png') || this.model.allowed_types.includes('gif')) {
          /// bind image resolution -- min resolution
          if(this.model.min_width !== null && this.model.min_height !== null) {
            this.form.minResolution.width = this.model.min_width;
            this.form.minResolution.height = this.model.min_height;
          }

          /// bind image resolution -- max resolution
          if(this.model.max_width !== null && this.model.max_height !== null) {
            this.form.maxResolution.width = this.model.max_width;
            this.form.maxResolution.height = this.model.max_height;
          }

          this.form.selectedTypes['jpeg;jpg;png;gif'] = true;
          this.show = true;

        } else {
          this.form.selectedTypes['jpeg;jpg;png;gif'] = false;
          this.show = false;
        }
        this.form.selectedTypes['xls;xlsx;odf'] = this.model.allowed_types.includes('xls') || this.model.allowed_types.includes('xlsx') || this.model.allowed_types.includes('odf');

        this.can_be_deleted = this.model.can_be_deleted;

        this.form.nbmax = this.model.nbmax;
      } else {
        this.form = {
          name: {
            fr: '',
            en: ''
          },
          description: {
            fr: '',
            en: ''
          },
          nbmax: 1,
          mandatory: 0,
          selectedTypes: {
            pdf: false,
            'jpeg;jpg;png;gif': false,
            'doc;docx;odt;ppt;pptx': false,
            'xls;xlsx;odf': false,
          },
        };
      }
    },

  },

};
</script>

<style scoped>
.require {
  margin-bottom: 10px !important;
}

.inputF {
  margin: 0 0 10px 0 !important;
}

.d-flex {
  display: flex;
  align-items: center;
}

.dropdown-custom {
  height: 35px;
}

.users-block {
  height: 15em;
  overflow: scroll;
  overflow-x: hidden;
}

.user-item {
  display: flex;
  padding: 10px;
  background-color: #f0f0f0;
  border-radius: 5px;
  align-items: center;
  margin-bottom: 1em;
}

.bigbox {
  margin: 0 !important;
  cursor: pointer;
}

.btnPreview {
  margin-bottom: 10px;
  position: relative;
  background: transparent;
}

.select-all {
  display: flex;
  align-items: end;
  margin-bottom: 1em;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}

.image-resolution-header {
  margin-top: unset;
  margin-bottom: 20px;
}

.image-resolution-tooltips {
  font-size: smaller;
  color: #20835F;
}

</style>
