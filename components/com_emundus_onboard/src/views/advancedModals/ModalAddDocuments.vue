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
            <h2 class="update-title-header" v-if="doc == null">
               {{createDocument}}
            </h2>
            <h2 class="update-title-header" v-if="doc != null">
               {{editDocument}}
            </h2>
                </div>
        </div>

      <div class="modalC-content">
        <div class="form-group">
          <label for="name">{{Name}}* :</label>
          <div class="input-can-translate">
            <input type="text" maxlength="100" class="form__input field-general w-input mb-0" v-model="form.name[langue]" id="name" :class="{ 'is-invalid': errors.name}" />
            <button class="translate-icon" :class="{'translate-icon-selected': translate.name}" v-if="manyLanguages !== '0'" type="button" @click="translate.name = !translate.name"></button>
          </div>
          <translation :label="form.name" :actualLanguage="langue" v-if="translate.name"></translation>
          <p v-if="errors.name" class="error col-md-12 mb-2">
            <span class="error">{{NameRequired}}</span>
          </p>
        </div>
        <div class="form-group">
          <label for="description">{{Description}} :</label>
          <div class="input-can-translate">
            <textarea type="text" class="form__input field-general w-input mb-0" v-model="form.description[langue]" id="description" />
            <button class="translate-icon" :class="{'translate-icon-selected': translate.description}" v-if="manyLanguages !== '0'" type="button" @click="translate.description = !translate.description"></button>
          </div>
          <translation :label="form.description" :actualLanguage="langue" v-if="translate.description"></translation>
        </div>
        <div class="form-group">
          <label for="nbmax">{{MaxPerUser}}* :</label>
          <input type="number" min="1" class="form__input field-general w-input" v-model="form.nbmax" id="nbmax" :class="{ 'is-invalid': errors.nbmax}" />
          <p v-if="errors.nbmax" class="error col-md-12 mb-2">
            <span class="error">{{MaxRequired}}</span>
          </p>
        </div>
        <div class="form-group">
          <label for="nbmax" :class="{ 'is-invalid': errors.selectedTypes}">{{FileType}}* :</label>
          <div class="users-block" :class="{ 'is-invalid': errors.selectedUsers}">
            <div v-for="(type, index) in types" :key="index" class="user-item">
              <input type="checkbox" class="form-check-input bigbox" v-model="form.selectedTypes[type.value]">
              <div class="ml-10px">
                  <p>{{type.title}} ({{type.value}})</p>
              </div>
            </div>
          </div>
          <p v-if="errors.selectedTypes" class="error col-md-12 mb-2">
            <span class="error">{{TypeRequired}}</span>
          </p>
        </div>
      </div>
      <div class="d-flex justify-content-between mb-1">
        <button
            type="button"
            class="bouton-sauvergarder-et-continuer w-retour"
                @click.prevent="$modal.hide('modalAddDocuments')">
          {{Retour}}
        </button>
        <button type="button"
            class="bouton-sauvergarder-et-continuer"
                @click.prevent="createNewDocument()">
          {{ Continuer }}
        </button>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");
import Translation from "@/components/translation"

export default {
  name: "modalAddDocuments",
  props: {
    cid: Number,
    pid: Number,
    doc: Object,
    manyLanguages: Number,
    langue: String,
  },
  components: {
    Translation
  },
  data() {
    return {
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
          'jpg;png;gif': false,
          'doc;docx;odt': false,
          'xls;xlsx;odf': false,
        },
      },
      translate: {
        name: false,
        description: false
      },
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
          value: 'jpg;png;gif'
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
      createDocument: Joomla.JText._("COM_EMUNDUS_ONBOARD_CREATE_DOCUMENT"),
      editDocument: Joomla.JText._("COM_EMUNDUS_ONBOARD_EDIT_DOCUMENT"),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_OK"),
      Name: Joomla.JText._("COM_EMUNDUS_ONBOARD_LASTNAME"),
      Description: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADDCAMP_DESCRIPTION"),
      MaxPerUser: Joomla.JText._("COM_EMUNDUS_ONBOARD_MAXPERUSER"),
      FileType: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILETYPE_ACCEPTED"),
      NameRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROG_REQUIRED_LABEL"),
      MaxRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_MAXPERUSER_REQUIRED"),
      TypeRequired: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILETYPE_ACCEPTED_REQUIRED"),
      TranslateEnglish: Joomla.JText._("COM_EMUNDUS_ONBOARD_TRANSLATE_ENGLISH"),
    };
  },
  methods: {
    beforeClose(event) {
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
          'jpg;png;gif': false,
          'doc;docx;odt': false,
          'xls;xlsx;odf': false,
        },
      };

      this.doc = null;
    },
    beforeOpen(event) {
      if(this.doc != null) {
        this.form.name.fr = this.doc.value_fr;
        this.form.name.en = this.doc.value_en;
        this.form.description.fr = this.doc.description_fr;
        this.form.description.en = this.doc.description_en;
        if(this.doc.allowed_types.includes('pdf')) {
          this.form.selectedTypes.pdf = true;
        }
        if(this.doc.allowed_types.includes('jpg') || this.doc.allowed_types.includes('png') || this.doc.allowed_types.includes('gif')) {
          this.form.selectedTypes['jpg;png;gif'] = true;
        }
        if(this.doc.allowed_types.includes('xls') || this.doc.allowed_types.includes('xlsx') || this.doc.allowed_types.includes('odf')) {
          this.form.selectedTypes['xls;xlsx;odf'] = true;
        }
        this.form.nbmax = this.doc.nbmax;
      }
    },
    createNewDocument() {
      this.errors = {
        name: false,
        nbmax: false,
        selectedTypes: false
      };
      if(this.form.name[this.langue] === ''){
        this.errors.name = true;
        return 0;
      }
      if(this.form.nbmax === '' || this.form.nbmax === 0){
        this.errors.nbmax = true;
        return 0;
      }
      if(Object.values(this.form.selectedTypes).every((val, i) => val === false )){
        this.errors.selectedTypes = true;
        return 0;
      }

      if(this.translate.name === false){
        this.form.name.en = this.form.name.fr;
      }

      if(this.translate.description === false){
        this.form.description.en = this.form.description.fr;
      }

      let types = [];
      Object.keys(this.form.selectedTypes).forEach(key => {
        if(this.form.selectedTypes[key] == true){
          types.push(key);
        }
      });

      let params = {
        document: this.form,
        types: types,
        cid: this.cid,
        pid: this.pid,
      }

      let url = 'index.php?option=com_emundus_onboard&controller=campaign&task=createdocument';
      if(this.doc != null) {
        url = 'index.php?option=com_emundus_onboard&controller=campaign&task=updatedocument';
        params.did = this.doc.id;
      }

      axios({
        method: "post",
        url: url,
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify(params)
      }).then((rep) => {
        this.$emit("UpdateDocuments");
        this.$modal.hide('modalAddDocuments')
      });
    },
  },
};
</script>

<style scoped>
.require{
  margin-bottom: 10px !important;
}

.inputF{
  margin: 0 0 10px 0 !important;
}

.d-flex{
  display: flex;
  align-items: center;
}

.dropdown-custom{
  height: 35px;
}

.users-block{
  height: 15em;
  overflow: scroll;
}

.user-item{
  display: flex;
  padding: 10px;
  background-color: #f0f0f0;
  border-radius: 5px;
  align-items: center;
  margin-bottom: 1em;
}

.bigbox{
  height: 30px !important;
  width: 30px !important;
  cursor: pointer;
}

.btnPreview{
  margin-bottom: 10px;
  position: relative;
  background: transparent;
}

.select-all{
  display: flex;
  align-items: end;
  margin-bottom: 1em;
}
</style>
