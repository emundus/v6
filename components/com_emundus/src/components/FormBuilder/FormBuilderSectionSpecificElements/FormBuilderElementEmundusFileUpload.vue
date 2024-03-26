<template>
  <div id="form-builder-emundus-file-upload">
    <div v-if="loading" class="em-loader"></div>
    <div v-else class="w-full relative flex items-center ">
      <div id="div_jos_emundus_1001_00___e_805_8014" class="fabrik_element___emundus_file_upload_parent">
    <span v-if="allowedTypes!=null" class="fabrik_element___file_upload_formats">
        {{ translate("PLG_ELEMENT_FILEUPLOAD_ALLOWED_TYPES") }} : {{this.allowedTypes}}
    </span>
        <div class="btn-upload em-pointer">
          <p class="em-flex-row">{{ translate('COM_EMUNDUS_ONBOARD_PARAMS_PREVIEW_DROPZONE')}}<span class="material-icons-outlined em-ml-12">cloud_upload</span></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import fileGet from '../../../services/form';

export default {
  props: {
    element: {
      type: Object,
      required: true
    },
    type: {
      type: String,
      required: true
    }
  },
  components: {
  },
  data() {
    return {
      loading: false,
      editable: false,
      allowedTypes: null,
      attachId: this.element.params.attachmentId,
    };
  },
  created () {
    console.log('test de fin');
    this.getAllowedFile(this.attachId);
  },
  methods: {
    getAllowedFile(aid){
      fileGet.getDocumentModels(aid).then((response) => {
         this.allowedTypes =  response.data.allowed_types;
      }).catch((error) => {
        this.loading = false;
        console.error(error);
      });
    }
  },
  watch: {},
  computed: {
  }
}
</script>

<style lang="scss">

</style>
