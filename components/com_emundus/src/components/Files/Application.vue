<template>
  <modal
      id="application-modal"
      name="application-modal"
      height="100vh"
      width="100vw"
      styles="display:flex;flex-direction:column;justify-content:center;align-items:center;"
  >
    <div class="em-modal-header em-w-100 em-h-50 em-p-12-16 em-border-bottom-neutral-300">
      <div class="em-flex-row em-pointer em-gap-8" id="evaluation-modal-close">
        <div class="em-w-max-content em-flex-row" @click="$modal.hide('application-modal')">
          <span class="material-icons-outlined em-font-size-16">arrow_back</span>
        </div>
        <p class="em-text-neutral-500">|</p>
        <p class="em-font-size-14">
          {{ file.applicant_name }} - {{ file.fnum }}
        </p>
      </div>
    </div>

    <div class="modal-grid">
      <div id="modal-applicationform">
        <div class="scrollable">
          <div class="em-flex-row em-flex-center em-gap-16 em-border-bottom-neutral-300">
            <div v-for="tab in tabs" class="em-light-tabs em-pointer" @click="selected = tab.name" :class="selected === tab.name ? 'em-light-selected-tab' : ''">
              <p class="em-font-size-14">{{ translate(tab.label) }}</p>
            </div>
          </div>

          <div v-if="selected === 'application'" v-html="applicationform"></div>
          <Attachments v-if="selected === 'attachments'" :fnum="file.fnum" :user="file.student_id" />
        </div>
      </div>

      <div id="modal-evaluationgrid">
        <iframe :src="url" class="iframe-evaluation" @load="loading = false;" id="iframe-evaluation" title="Evaluation form" />
        <div class="em-page-loader" v-if="loading"></div>
      </div>
    </div>
  </modal>
</template>

<script>
import axios from "axios";
import Attachments from "@/views/Attachments";
import filesService from 'com_emundus/src/services/files';


export default {
  name: "Application",
  components: {Attachments},
  props: {
    file: Object,
    type: String
  },
  data: () => ({
    applicationform: '',
    selected: 'application',
    tabs: [
      {
        label: 'COM_EMUNDUS_FILES_APPLICANT_FILE',
        name: 'application'
      },
      {
        label: 'COM_EMUNDUS_FILES_ATTACHMENTS',
        name: 'attachments'
      },
      {
        label: 'COM_EMUNDUS_FILES_COMMENTS',
        name: 'comments'
      },
    ],
    evaluation_form: 0,
    url: '',

    loading: false
  }),
  created(){
    this.loading = true;
    this.getApplicationForm();
    if(this.$props.type === 'evaluation'){
      this.getEvaluationForm();
    }
  },
  methods:{
    getApplicationForm(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&view=application&format=raw&layout=form&fnum="+this.file.fnum,
      }).then(response => {
        this.applicationform = response.data;
        this.loading = false;
      });
    },
    getEvaluationForm(){
      if (this.$props.file.id != null) {
        this.rowid = this.$props.file.id;
      }
      if (this.$props.file.student_id != null) {
        this.student_id = this.$props.file.student_id;
      }
      let view = 'form';

      filesService.getEvaluationFormByFnum(this.$props.file.fnum).then((response) => {
        if(response.data !== 0) {
          this.url = 'index.php?option=com_fabrik&c=form&view=' + view + '&formid=' + response.data + '&rowid=' + this.rowid + '&jos_emundus_evaluations___student_id[value]=' + this.student_id + '&jos_emundus_evaluations___campaign_id[value]=' + this.$props.file.campaign_id + '&jos_emundus_evaluations___fnum[value]=' + this.$props.file.fnum + '&student_id=' + this.student_id + '&tmpl=component&iframe=1'
        }
      });
    },
  }
}
</script>

<style>
.modal-grid {
  display: grid;
  grid-template-columns: 66% 33%;
  grid-gap: 16px;
  width: 100%;
  height: 100vh;
}
.scrollable {
  height: calc(100vh - 100px);
  overflow-y: scroll;
  overflow-x: hidden;
}
.em-container-form-heading{
  display: none;
}
#iframe{
  height: 100vh;
  overflow-y: scroll;
  overflow-x: hidden;
}
.iframe-evaluation{
  width: 100%;
  height: 100%;
  border: unset;
}
#modal-evaluationgrid{
  border-left: 1px solid #EBECF0;
  box-shadow: 0px 4px 16px rgba(32, 35, 44, 0.1);
}
</style>