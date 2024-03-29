<template>
  <div :id="'modal-admissiongrid-' + uniqueID">
    <iframe v-if="url" :src="url" class="iframe-evaluation" id="iframe-evaluation" @load="loading = false"
            title="Admission form"/>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import admissionService from "@/services/admission";

export default {
  name: 'AdmissionForm',
  props: {
    user: {
      type: Number,
      required: true,
    },
    fnum: {
      type: String,
      required: true,
    },
    access: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      uniqueID: Math.random().toString(36).substring(2, 9),
      url: '',
      loading: false,
      top: 0,
    }
  },
  mounted() {
    this.getAdmissionForm();

    // get #modal-evaluationgrid top position
    let modal = document.getElementById('modal-admissiongrid-' + this.uniqueID);
    this.top = modal.getBoundingClientRect().top;
    modal.style.height = 'calc(100vh - ' + this.top + 'px)';
  },
  methods: {
    getAdmissionForm() {
      admissionService.getAdmissionFormUrl(this.fnum).then(response => {
        this.url = response.url;
      }).catch(error => {
        console.error(error);
      });
    },
  }
}
</script>

<style scoped>
#iframe-evaluation {
  height: 100%;
}
</style>