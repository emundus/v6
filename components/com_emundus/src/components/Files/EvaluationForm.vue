<template>
  <div id="modal-evaluationgrid">
    <iframe v-if="url" :src="url" class="iframe-evaluation" id="iframe-evaluation" @load="loading = false"
            title="Evaluation form"/>
    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import fileService from "@/services/file";
import filesService from "@/services/files";

export default {
  name: 'EvaluationForm',
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
      campaign_id: 0,
      applicant_id: 0,
      rowid: '',
      url: '',
      loading: false,
      top: 0,
    }
  },
  mounted() {
    this.getEvaluationForm();

    // get #modal-evaluationgrid top position
    let modal = document.getElementById('modal-evaluationgrid');
    this.top = modal.getBoundingClientRect().top;
    modal.style.height = 'calc(100vh - ' + this.top + 'px)';
  },
  methods: {
    getEvaluationForm() {
      let view = 'form';

      fileService.getFnumInfos(this.fnum).then((response) => {
        this.applicant_id = response.fnumInfos.applicant_id;
        this.campaign_id = response.fnumInfos.campaign_id;

        filesService.getEvaluationFormByFnum(this.fnum).then((response) => {
          if (response.data !== 0) {
            filesService.getMyEvaluation(this.fnum).then((data) => {
              this.rowid = data.data;
              if (this.rowid == null) {
                this.rowid = '';
              }

              this.url = 'index.php?option=com_fabrik&c=form&view=' + view + '&formid=' + response.data + '&rowid=' + this.rowid + '&jos_emundus_evaluations___student_id[value]=' + this.applicant_id + '&jos_emundus_evaluations___campaign_id[value]=' + this.campaign_id + '&jos_emundus_evaluations___fnum[value]=' + this.fnum + '&student_id=' + this.applicant_id + '&tmpl=component&iframe=1'
            });
          }
        });
      });
    },
  }
}
</script>

<style scoped>

</style>