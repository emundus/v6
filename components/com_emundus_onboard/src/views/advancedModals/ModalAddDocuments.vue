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
      <div class="fixed-header-modal">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalAddDocuments')">
              <em class="fas fa-times"></em>
            </button>
          </div>
                <div class="update-field-header">
            <h2 class="update-title-header" v-if="currentDoc ==null">
               {{ translations.createDocument }}
            </h2>
            <h2 class="update-title-header" v-if="currentDoc != null">
               {{ translations.editDocument }}
            </h2>
                </div>
        </div>

      <div class="modalC-content">
        <div class="mb-1">

          <a class="d-flex tool-icon">
            <div class="toggle">
              <input type="checkbox" class="check" v-model="req" @click="updateRequireMandatory()"/>
              <strong class="b switch"></strong>
              <strong class="b track"></strong>
            </div>
            <span class="ml-10px">{{ translations.Required }}</span>
          </a>
        </div>
        <div class="form-group" v-if="can_be_deleted">
          <button type="button" class="bouton-sauvergarder-et-continuer w-delete" @click="deleteModel">{{translations.DeleteDocTemplate}}</button>
        </div>
        <div class="form-group" v-if="currentDoc ==null">
          <label for="modelName">{{ translations.DocTemplate }} :</label>
          <select v-model="doc" class="dropdown-toggle" id="modelName" :disabled="Object.keys(models).length <= 0">
            <option :value="null"></option>
            <option v-for="(modelT, index) in models" :value="modelT.id">{{ modelT.name[langue] }}  ({{ modelT.allowed_types }})</option>
          </select>
        </div>
        <div class="form-group">
          <label for="name">{{ translations.Name }}* :</label>
          <div class="input-can-translate">
            <input type="text" maxlength="100" class="form__input field-general w-input mb-0"
                   v-model="form.name[langue]" id="name" :class="{ 'is-invalid': errors.name}"/>
            <button class="translate-icon" :class="{'translate-icon-selected': translate.name}"
                    v-if="manyLanguages !== '0'" type="button" @click="translate.name = !translate.name"></button>
          </div>
          <translation :label="form.name" :actualLanguage="langue" v-if="translate.name"></translation>
          <p v-if="errors.name" class="error col-md-12 mb-2">
            <span class="error">{{ translations.NameRequired }}</span>
          </p>
        </div>
        <div class="form-group">
          <label for="description">{{ translations.Description }} :</label>
          <div class="input-can-translate">
            <textarea type="text" class="form__input field-general w-input mb-0" v-model="form.description[langue]"
                      id="description"/>
            <button class="translate-icon" :class="{'translate-icon-selected': translate.description}"
                    v-if="manyLanguages !== '0'" type="button"
                    @click="translate.description = !translate.description"></button>
          </div>
          <translation :label="form.description" :actualLanguage="langue" v-if="translate.description"></translation>
        </div>
        <div class="form-group">
          <label for="nbmax">{{ translations.MaxPerUser }}* :</label>
          <input type="number" min="1" class="form__input field-general w-input" v-model="form.nbmax" id="nbmax"
                 :class="{ 'is-invalid': errors.nbmax}"/>
          <p v-if="errors.nbmax" class="error col-md-12 mb-2">
            <span class="error">{{ translations.MaxRequired }}</span>
          </p>
        </div>
        <div class="form-group">
          <label for="nbmax" :class="{ 'is-invalid': errors.selectedTypes}">{{ translations.FileType }}* :</label>
          <div class="users-block" :class="{ 'is-invalid': errors.selectedUsers}">
            <div v-for="(type, index) in types" :key="index" class="user-item">
              <input type="checkbox" class="form-check-input bigbox" v-model="form.selectedTypes[type.value]" @change="selectType(type)">
              <div class="ml-10px">
                  <p>{{ type.title }} ({{ type.value }})</p>
              </div>
            </div>
          </div>
          <p v-if="errors.selectedTypes" class="error col-md-12 mb-2">
            <span class="error">{{ translations.TypeRequired }}</span>
          </p>
        </div>
      </div>
      <!-- image resolution -->
      <div id="imageResolutionZone" v-if="show == true">
        <hr style="margin-top: unset"/>
        <div class="toogle" style="font-size: smaller; color: #16AFE1;">
          <span>{{ translations.MinResolutionTooltips }}</span>
        </div>
        <br/>
        <div class="form-group">
          <label for="image-min-width">{{ translations.ImageWidth }}</label>
          <div class="input-can-translate d-flex justify-content-between">
            <input type="number" maxlength="100" class="form__input field-general w-input mb-0" id="image-min-width" min="0" v-model="form.minResolution.width" style="max-width: 48%" @keyup="ZeroOrNegative()" :placeholder="translations.MinResolutionPlaceholder"/>
            <input type="number" maxlength="100" class="form__input field-general w-input mb-0" id="image-max-width" min="0"  v-model="form.maxResolution.width" style="max-width: 48%" @keyup="ZeroOrNegative()" :placeholder="translations.MaxResolutionPlaceholder"/>
          </div>
          <transition name="fade">
              <span style="font-size: smaller; color:red" v-if=" errorWidth.error "> {{ errorWidth.message}} </span>
          </transition>
        </div>

        <div class="form-group">
          <label for="image-min-height">{{ translations.ImageHeight }}</label>
          <div class="input-can-translate d-flex justify-content-between">
            <input type="number" maxlength="100" class="form__input field-general w-input mb-0" id="image-min-height" min="0" v-model="form.minResolution.height" style="max-width: 48%" @keyup="ZeroOrNegative()" :placeholder="translations.MinResolutionPlaceholder"/>
            <input type="number" maxlength="100" class="form__input field-general w-input mb-0" id="image-max-height" min="0" v-model="form.maxResolution.height" style="max-width: 48%" @keyup="ZeroOrNegative()" :placeholder="translations.MaxResolutionPlaceholder"/>
          </div>
          <transition name="fade">
              <span style="font-size: smaller; color:red" v-if=" errorHeight.error"> {{ errorHeight.message}} </span>
          </transition>
        </div>

      </div>
      <div class="d-flex justify-content-between mb-1">
        <button
            type="button"
            class="bouton-sauvergarder-et-continuer w-retour"
            @click.prevent="$modal.hide('modalAddDocuments')">
          {{ translations.Retour }}
        </button>
        <button type="button"
                class="bouton-sauvergarder-et-continuer"
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
import Translation from "@/components/translation"
import Swal from "sweetalert2";

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
    Translation
  },
  data() {
    return {
      show: false,
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
          'jpeg;jpg;png;gif': false,
          'doc;docx;odt': false,
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
      translate: {
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
          title: Joomla.JText._("COM_EMUNDUS_ONBOARD_PDF_DOCUMENTS"),
          value: 'pdf'
        },
        {
          title: Joomla.JText._("COM_EMUNDUS_ONBOARD_PICTURES_DOCUMENTS"),
          value: 'jpeg;jpg;png;gif'
        },
        {
          title: Joomla.JText._("COM_EMUNDUS_ONBOARD_OFFICE_DOCUMENTS"),
          value: 'doc;docx;odt'
        },
        {
          title: Joomla.JText._("COM_EMUNDUS_ONBOARD_EXCEL_DOCUMENTS"),
          value: 'xls;xlsx;odf'
        },

      ],

      selectedTypes: [],
      models: [],
      can_be_deleted: false,
      translations: {
        DeleteDocTemplate: Joomla.JText._("COM_EMUNDUS_ONBOARD_DELETE_TEMPLATE_DOC"),
        createDocument: Joomla.JText._("COM_EMUNDUS_ONBOARD_CREATE_DOCUMENT"),
        editDocument: Joomla.JText._("COM_EMUNDUS_ONBOARD_EDIT_DOCUMENT"),
        Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
        Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
        DocTemplate: Joomla.JText._("COM_EMUNDUS_ONBOARD_TEMPLATE_DOC"),
        Name: Joomla.JText._("COM_EMUNDUS_ONBOARD_LASTNAME"),
        Description: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION"),
        MaxPerUser: Joomla.JText._("COM_EMUNDUS_ONBOARD_MAXPERUSER"),
        FileType: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILETYPE_ACCEPTED"),
        NameRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROG_REQUIRED_LABEL"),
        MaxRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_MAXPERUSER_REQUIRED"),
        TypeRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILETYPE_ACCEPTED_REQUIRED"),
        TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
        Required: Joomla.JText._("COM_EMUNDUS_ONBOARD_ACTIONS_REQUIRED"),
        ImageWidth: Joomla.JText._("COM_EMUNDUS_ONBOARD_IMAGE_WIDTH"),
        ImageHeight: Joomla.JText._("COM_EMUNDUS_ONBOARD_IMAGE_HEIGHT"),
        MinResolutionTooltips: Joomla.JText._("COM_EMUNDUS_ONBOARD_MIN_RESOLUTION_TOOLTIPS"),
        MaxResolutionTooltips: Joomla.JText._("COM_EMUNDUS_ONBOARD_MAX_RESOLUTION_TOOLTIPS"),
        MinResolutionPlaceholder: Joomla.JText._("COM_EMUNDUS_ONBOARD_MIN_RESOLUTION_PLACEHOLDER"),
        MaxResolutionPlaceholder: Joomla.JText._("COM_EMUNDUS_ONBOARD_MAX_RESOLUTION_PLACEHOLDER"),
        ErrorResolution: Joomla.JText._("COM_EMUNDUS_ONBOARD_ERROR_RESOLUTION"),
        ErrorResolutionNegative: Joomla.JText._("COM_EMUNDUS_ONBOARD_ERROR_RESOLUTION_NEGATIVE")
      }
    };
  },
  methods: {
    beforeClose(event) {
      this.show = false;
      this.doc = null;
      this.currentDoc = null;
      this.can_be_deleted = false;
      this.errorWidth = false;
      this.errorHeight = false;

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
          'doc;docx;odt': false,
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
    beforeOpen(event) {
      this.getModelsDocs();
    },
    createNewDocument() {
      if(!this.isImageError()) {
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
        if (Object.values(this.form.selectedTypes).every((val, i) => val === false)) {
          this.errors.selectedTypes = true;
          return 0;
        }

        if (this.translate.name === false) {

          if (this.manyLanguages == 0 && this.langue == "en") {
            this.form.name.fr = this.form.name.en
          }
          if (this.manyLanguages == 0 && this.langue === "fr") {
            this.form.name.en = this.form.name.fr;
          }
        }

        if (this.translate.description === false) {

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


        let url = 'index.php?option=com_emundus_onboard&controller=campaign&task=createdocument';

        if (this.form.name[this.langue] === this.model.value && this.doc != null) {
          url = 'index.php?option=com_emundus_onboard&controller=campaign&task=updatedocument';

          params.did = this.doc;

        }
        if (this.currentDoc != null) {

          url = 'index.php?option=com_emundus_onboard&controller=campaign&task=updatedocument';
          params.did = this.doc;


        }

        axios({
          method: "post",
          url: url,
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify(params)
        }).then((rep) => {

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

        /// check width
        if ((parseInt(this.form.minResolution.width) > parseInt(this.form.maxResolution.width)) || parseInt(this.form.minResolution.width) <= 0) {
          this.errorWidth.error = true;
          this.errorWidth.message = (parseInt(this.form.minResolution.width) <= 0) ? this.translations.ErrorResolutionNegative : this.translations.ErrorResolution
          sendError = true;
        } else {
          this.errorWidth.error = false;
          this.errorWidth.message = "";
        }

        /// check height
        if ((parseInt(this.form.minResolution.height) > parseInt(this.form.maxResolution.height)) || parseInt(this.form.minResolution.height) <= 0) {
          this.errorHeight.error = true;
          this.errorHeight.message = (parseInt(this.form.minResolution.height) <= 0) ? this.translations.ErrorResolutionNegative : this.translations.ErrorResolution
          sendError = true;
        } else {
          this.errorHeight.error = false;
          this.errorHeight.message = "";
        }

      return sendError;
    },

    deleteModel(){
      Swal.fire({
        title: Joomla.JText._("COM_EMUNDUS_ONBOARD_DELETE_TEMPLATE_DOC"),
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: '#12db42',
        confirmButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true
      }).then(result => {
        if(result.value){
          axios({
            method: "post",
            url: "index.php?option=com_emundus_onboard&controller=form&task=deletemodeldocument",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              did: this.doc,
            })
          }).then((response) => {
            console.log(response);
            if(response.data.allowed){
              Swal.fire({
                backdrop: true,
                title: Joomla.JText._("COM_EMUNDUS_ONBOARD_MODEL_DELETED"),
                text: Joomla.JText._("COM_EMUNDUS_ONBOARD_MODEL_DELETED_TEXT"),
                showConfirmButton: true,
                timer: 5000
              }).then(() => {
                this.$modal.hide('modalAddDocuments')
              });
            } else {
              Swal.fire({
                backdrop: true,
                title: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANNOT_DELETE"),
                text: Joomla.JText._("COM_EMUNDUS_ONBOARD_CANNOT_DELETE_TEXT"),
                showConfirmButton: true,
                timer: 5000
              })
            }
          });
        }
      });
    },

    getModelsDocs() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=form&task=getundocuments",
      }).then(response => {
        this.models = response.data.data;
        if (this.currentDoc != null) {
          this.doc = this.currentDoc;
        }
      });
    },
    updateRequireMandatory() {

      if (this.req == true) {
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
      if(val == 0 || val < 0) {
        document.getElementById(id).style.color = "red";
      } else {
        document.getElementById(id).style.color = "unset";
      }
    }
  },

  watch: {
    doc: function (val) {
      if (val != null) {
        this.model = this.models.find(model => model.id == val);

        this.form.name = this.model.name;
        this.form.description = this.model.description;
        this.form.mandatory = this.model.mandatory
        this.form.minResolution = {};
        this.form.maxResolution = {};

        this.req = this.model.mandatory;

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
          selectedTypes: {
            pdf: false,
            'jpeg;jpg;png;gif': false,
            'doc;docx;odt': false,
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

</style>
