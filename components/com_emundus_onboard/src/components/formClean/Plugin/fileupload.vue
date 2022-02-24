<template xmlns="http://www.w3.org/1999/html">

  <div id="fileF">
      <div class="row rowmodal">
        <div class="form-group">
          <label>Max size (Mb) :</label>
          <input type="number" min="1" max="10" class="form__input field-general w-input" v-model="size" v-on:keyup="convertToByte()" />
          <p v-if="errors.maxSize" class="error col-md-12 mb-2">
            <span class="error">Saisir une valeur comprise entre 1 et 10 Mb</span>
          </p>
        </div>
        <div class="form-group">
          <label for="max_size" :class="{ 'is-invalid': errors.selectedTypes}">{{FileType}}* :</label>
          <div class="users-block" :class="{ 'is-invalid': errors.selectedUsers}">
            <div v-for="(type, index) in types" :key="index" class="user-item">
              <input type="checkbox" class="form-check-input bigbox" v-model="form.selectedTypes[type.value]">
              <div class="ml-10px">
                <p>{{type.title}} ({{type.value}})</p>
              </div>
            </div>
          </div>

        </div>
      </div>

      </div>



</template>

<script>
import axios from "axios";
const qs = require("qs");
export default {
  name: "fileF",
  props: {
    value: File,
    element: Object,
    prid: String,

  },
  data() {
    return {
      msg: '',
      size: this.element.params.size/1048576,
      types: [
        {
          title: this.translate("COM_EMUNDUS_ONBOARD_PDF_DOCUMENTS"),
          value: 'pdf'
        },
        {
          title: this.translate("COM_EMUNDUS_ONBOARD_PICTURES_DOCUMENTS"),
          value: 'jpg;png;gif'
        },
        {
          title: this.translate("COM_EMUNDUS_ONBOARD_OFFICE_DOCUMENTS"),
          value: 'doc;docx;odt'
        },
        {
          title: this.translate("COM_EMUNDUS_ONBOARD_EXCEL_DOCUMENTS"),
          value: 'xls;xlsx;odf'
        }],
      labels_name:{
        text_fr: '',
        text_en: '',
        did: '',
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
          'jpg;png;gif': false,
          'doc;docx;odt': false,
          'xls;xlsx;odf': false,
        },
      },
      errors: {
        name: false,
        nbmax: false,
        selectedTypes: false,
        maxSize:false
      },
      selectedTypes: [],
      path: window.location.protocol + '//' + window.location.host + '/media/com_emundus_onboard/',
      heightext: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_HEIGHT"),
      helptext: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_HELPTEXT"),
      MaxRequired: this.translate("COM_EMUNDUS_ONBOARD_MAXPERUSER_REQUIRED"),
      TypeRequired: this.translate("COM_EMUNDUS_ONBOARD_FILETYPE_ACCEPTED_REQUIRED"),
      placeholderHelp: this.translate("COM_EMUNDUS_ONBOARD_BUILDER_PLACEHOLDER_HELP"),
      MaxPerUser: this.translate("COM_EMUNDUS_ONBOARD_MAXPERUSER"),
      FileType: this.translate("COM_EMUNDUS_ONBOARD_FILETYPE_ACCEPTED"),
    }
  },
  methods: {
    handleFileChange(e) {
      this.$emit('input', e.target.files[0])
    },
    convertToByte(){
      if (this.size>10 || this.size <1){
        this.errors.maxSize=true;
      } else {
        this.element.params.size=this.size*1048576;
        this.errors.maxSize=false;
      }
    },

    retrieveAssociateElementDoc(docid){
      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=formbuilder&task=retriveElementFormAssociatedDoc',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          gid:2,
          docid:docid
        })
      }).then((result)=>{

        this.form.description[this.actualLanguage]=result.data.description;

        if (result.data.allowed_types.includes('pdf')) {
          this.form.selectedTypes.pdf = true;
        } else {
          this.form.selectedTypes.pdf = false;
        }
        if (result.data.allowed_types.includes('jpg') || result.data.allowed_types.includes('png') || result.data.allowed_types.includes('gif')) {
          this.form.selectedTypes['jpg;png;gif'] = true;
        } else {
          this.form.selectedTypes['jpg;png;gif'] = false;
        }
        if (result.data.allowed_types.includes('xls') || result.data.allowed_types.includes('xlsx') || result.data.allowed_types.includes('odf')) {
          this.form.selectedTypes['xls;xlsx;odf'] = true;
        } else {
          this.form.selectedTypes['xls;xlsx;odf'] = false;
        }

        this.form.nbmax=result.data.nbmax;

      })
    },
    retrieveDocumentNameFalangs(docid){
      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=campaign&task=getDocumentFalang',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          gid:2,
          docid:docid
        })
      }).then((result)=>{

        this.labels_name.text_fr=result.data.data.fr.value;
        this.labels_name.text_en=result.data.data.en.value;
        this.labels_name.did=docid;

      });
    },
    updateDocumentFalengs(params){
      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=campaign&task=updateDocumentFalang',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify(params)
      }).then((rep)=>{
        //console.log(rep);

      })
    },
    updateAssociateDocElement(params){

      axios({
        method: "post",
        url: 'index.php?option=com_emundus&controller=campaign&task=updatedocument',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify(params)
      }).then((rep) => {

        this.$emit("UpdateDocuments");

      });
    },

  },

  created() {
    this.msg = '<p style="color: black">' + this.placeholderHelp + '</p>' +
        '<img src="' + this.path + 'placeholder.gif" />'

    this.retrieveAssociateElementDoc(this.element.params.attachmentId);
    this.retrieveDocumentNameFalangs(this.element.params.attachmentId);

  },
  beforeDestroy() {

    let types = [];
    Object.keys(this.form.selectedTypes).forEach(key => {
      if(this.form.selectedTypes[key] == true){
        types.push(key);
      }
    });

    let updateparams = {
      document: this.form,
      types:  types,
      cid: this.cid,
      pid: this.prid,
      did: this.element.params.attachmentId,

    };
    this.updateAssociateDocElement(updateparams);

    this.updateDocumentFalengs(this.labels_name);

  }

}
</script>

<style scoped>
  .file-select > .select-button {
      padding: 1rem;
      color: red;
      background-color: #2EA169;
      border-radius: .3rem;
      text-align: center;
      font-weight: bold;
  }

  .file-select > input[type="file"] {
      display: none;
  }
  .rowmodal {
    margin-top: 0.5em;
    margin-bottom: 0.5em;
  }
  #fileF{
    padding: 10px 0;
  }
</style>
