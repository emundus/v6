<template>
  <div
      id="application-modal"
      name="application-modal"
  >
    <div class="em-modal-header em-w-100 em-h-50 em-p-12-16 em-bg-main-900 em-flex-row">
      <div class="em-flex-row em-pointer em-gap-8" id="evaluation-modal-close">
        <div class="em-w-max-content em-flex-row">
          <span class="material-icons-outlined em-font-size-16" onclick="document.querySelector('body').style.overflow= 'visible';swal.close()" style="color: white">arrow_back</span>
        </div>
        <span class="em-text-neutral-500">|</span>
        <p class="em-font-size-14" style="color: white" v-if="file.applicant_name != ''">
          {{ file.applicant_name }} - {{ file.fnum }}
        </p>
        <p class="em-font-size-14" style="color: white" v-else>
          {{ file.fnum }}
        </p>
      </div>
    </div>

    <div class="modal-grid" :style="'grid-template-columns:' + this.ratioStyle" v-if="access">
      <div id="modal-applicationform">
        <div class="scrollable">
          <div class="em-flex-row em-flex-center em-gap-16 em-border-bottom-neutral-300 sticky-tab">
            <div v-for="tab in tabs" v-if="access[tab.access].r" class="em-light-tabs em-pointer" @click="selected = tab.name" :class="selected === tab.name ? 'em-light-selected-tab' : ''">
              <span class="em-font-size-14">{{ translate(tab.label) }}</span>
            </div>
          </div>

          <div v-if="selected === 'application'" v-html="applicationform"></div>
          <Attachments
              v-if="selected === 'attachments'"
              :fnum="file.fnum"
              :user="$props.user"
              :columns="['name','date','category','status']"
              :displayEdit="false"
          />
          <Comments
              v-if="selected === 'comments'"
              :fnum="file.fnum"
              :user="$props.user"
              :access="access['10']"
            />
        </div>
      </div>

      <div id="modal-evaluationgrid">
        <div class="em-flex-column" v-if="!loading" style="width: 40px;height: 40px;margin: 24px 0 12px 24px;">
          <div class="em-circle-main-100 em-flex-column" style="width: 40px">
            <div class="em-circle-main-200 em-flex-column" style="width: 24px">
              <span class="material-icons-outlined em-main-400-color" style="font-size: 14px">troubleshoot</span>
            </div>
          </div>
        </div>
        <iframe v-if="url" :src="url" class="iframe-evaluation" id="iframe-evaluation" @load="iframeLoaded($event);" title="Evaluation form" />
        <div class="em-page-loader" v-if="loading"></div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import Attachments from "@/views/Attachments";
import filesService from 'com_emundus/src/services/files';
import errors from "@/mixins/errors";
import Comments from "@/components/Files/Comments";


export default {
  name: "ApplicationSingle",
  components: {Comments, Attachments},
  props: {
    file: Object|String,
    type: String,
    user: {
      type: String,
      required: true,
    },
    ratio: {
      type: String,
      default: '66/33'
    },
  },
  mixins: [errors],
  data: () => ({
    applicationform: '',
    selected: 'application',
    tabs: [
      {
        label: 'COM_EMUNDUS_FILES_APPLICANT_FILE',
        name: 'application',
        access: '1'
      },
      {
        label: 'COM_EMUNDUS_FILES_ATTACHMENTS',
        name: 'attachments',
        access: '4'
      },
      {
        label: 'COM_EMUNDUS_FILES_COMMENTS',
        name: 'comments',
        access: '10'
      },
    ],
    evaluation_form: 0,
    url: null,
    access: null,
    student_id: null,

    loading: false
  }),

  created(){
    document.querySelector('body').style.overflow= 'hidden';
    var r = document.querySelector(':root');
    let ratio_array = this.$props.ratio.split('/');
    r.style.setProperty('--attachment-width', ratio_array[0]+'%');

    this.loading = true;
    let fnum = '';

    if(typeof this.$props.file == 'string'){
      fnum = this.$props.file;
    } else {
      fnum = this.$props.file.fnum;
    }

    if(typeof this.$props.file == 'string'){
      filesService.getFile(fnum,this.$props.type).then((result) => {
        if(result.status == 1){
          this.$props.file = result.data;
          this.access = result.rights;
          this.updateURL(this.$props.file.fnum)
          this.getApplicationForm();
          if(this.$props.type === 'evaluation'){
            this.getEvaluationForm();
          }
        } else {
          this.displayError(
              'COM_EMUNDUS_FILES_CANNOT_ACCESS',
              'COM_EMUNDUS_FILES_CANNOT_ACCESS_DESC'
          ).then((confirm) => {
            if(confirm === true){
              this.$modal.hide('application-modal');
            }
          });
        }
      });
    } else {
      filesService.checkAccess(fnum).then((result) => {
        if(result.status == true){
          this.access = result.data;
          this.updateURL(this.$props.file.fnum)
          if(this.access['1'].r) {
            this.getApplicationForm();
          } else {
            if(this.access['4'].r) {
              this.selected = 'attachments';
            } else if(this.access['10'].r){
              this.selected = 'comments';
            }
          }
          if(this.$props.type === 'evaluation'){
            this.getEvaluationForm();
          }
        } else {
          this.displayError(
              'COM_EMUNDUS_FILES_CANNOT_ACCESS',
              'COM_EMUNDUS_FILES_CANNOT_ACCESS_DESC'
          ).then((confirm) => {
            if(confirm === true){
              this.$modal.hide('application-modal');
            }
          });
        }
      });
    }
  },

  methods:{
    getApplicationForm(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&view=application&format=raw&layout=form&fnum="+this.file.fnum,
      }).then(response => {
        this.applicationform = response.data;
        if(this.$props.type !== 'evaluation'){
          this.loading = false;
        }
      });
    },
    getEvaluationForm(){
      if (this.$props.file.id != null) {
        this.rowid = this.$props.file.id;
      }
      if(typeof this.$props.file.applicant_id != 'undefined'){
        this.student_id = this.$props.file.applicant_id;
      } else {
        this.student_id = this.$props.file.student_id;
      }
      let view = 'form';

      filesService.getEvaluationFormByFnum(this.$props.file.fnum,this.$props.type).then((response) => {
        if(response.data !== 0) {
          if(typeof this.$props.file.id === 'undefined'){
            filesService.getMyEvaluation(this.$props.file.fnum).then((data) => {
              this.rowid = data.data;
              if(this.rowid == null){
                this.rowid = "";
              }

              this.url = 'index.php?option=com_fabrik&c=form&view=' + view + '&formid=' + response.data + '&rowid=' + this.rowid + '&jos_emundus_evaluations___student_id[value]=' + this.student_id + '&jos_emundus_evaluations___campaign_id[value]=' + this.$props.file.campaign + '&jos_emundus_evaluations___fnum[value]=' + this.$props.file.fnum + '&student_id=' + this.student_id + '&tmpl=component&iframe=1'
            });
          } else {
            this.url = 'index.php?option=com_fabrik&c=form&view=' + view + '&formid=' + response.data + '&rowid=' + this.rowid + '&jos_emundus_evaluations___student_id[value]=' + this.student_id + '&jos_emundus_evaluations___campaign_id[value]=' + this.$props.file.campaign + '&jos_emundus_evaluations___fnum[value]=' + this.$props.file.fnum + '&student_id=' + this.student_id + '&tmpl=component&iframe=1'
          }
        }
      });
    },
    iframeLoaded(event){
      this.loading = false;
    },
    updateURL(fnum = ''){
      let url = window.location.href;
      url = url.split('#');

      if(fnum === '') {
        window.history.pushState('', '', url[0]);
      } else {
        window.history.pushState('', '', url[0] + '#' + fnum);
      }
    }
  },
  computed:{
    ratioStyle(){
      let ratio_array = this.$props.ratio.split('/');
      return ratio_array[0]+'% '+ratio_array[1]+'%';
    },
  }
}
</script>

<style>
.modal-grid {
  display: grid;
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
  height: 90%;
  border: unset;
}
#modal-evaluationgrid{
  border-left: 1px solid #EBECF0;
  box-shadow: 0px 4px 16px rgba(32, 35, 44, 0.1);
}
.sticky-tab{
  position: sticky;
  top: 0;
  background: white;
}
#modal-applicationform #em-attachments .v--modal-overlay{
  height: 100% !important;
  width: var(--attachment-width) !important;
  margin-top: 50px;
}
#modal-applicationform #em-attachments .v--modal-box.v--modal{
  width: 100% !important;
  height: calc(100vh - 50px) !important;
  box-shadow: unset;
}
#modal-applicationform #em-attachments .modal-body{
  width: 100%;
}
#modal-applicationform #em-attachments #em-attachment-preview{
  width: 100%;
}
</style>