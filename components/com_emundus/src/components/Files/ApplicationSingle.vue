<template>
  <div
      id="application-modal"
      name="application-modal"
      v-if="selectedFile !== null && selectedFile !== undefined"
      :class="{ 'context-files': context === 'files', 'hidden': hidden }"
  >
    <div class="em-modal-header em-w-100 em-h-50 em-p-12-16 em-bg-main-900 em-flex-row">
      <div class="em-flex-row em-pointer em-flex-space-between em-w-100" id="evaluation-modal-close">
        <div class="em-flex-row em-gap-8">
          <div class="em-w-max-content em-flex-row">
          <span class="material-icons-outlined em-font-size-16"
                @click="onClose" style="color: white">arrow_back</span>
          </div>
          <span class="em-text-neutral-500">|</span>
          <p class="em-font-size-14" style="color: white" v-if="selectedFile.applicant_name != ''">
            {{ selectedFile.applicant_name }} - {{ selectedFile.fnum }}
          </p>
          <p class="em-font-size-14" style="color: white" v-else>
            {{ selectedFile.fnum }}
          </p>
        </div>
        <div v-if="fnums.length > 1" class="em-flex-row">
          <span class="material-icons-outlined em-font-size-16" style="color:white;" @click="openPreviousFnum">navigate_before</span>
          <span class="material-icons-outlined em-font-size-16" style="color:white;" @click="openNextFnum">navigate_next</span>
        </div>
      </div>
    </div>

    <div class="modal-grid" :style="'grid-template-columns:' + this.ratioStyle" v-if="access">
      <div id="modal-applicationform">
        <div class="scrollable">
          <application-tabs
              :user="user"
              :file="file"
              :access="access"
          ></application-tabs>
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
        <iframe v-if="url" :src="url" class="iframe-evaluation" id="iframe-evaluation" @load="iframeLoaded($event);"
                title="Evaluation form"/>
        <div class="em-page-loader" v-if="loading"></div>
      </div>
    </div>
  </div>
</template>

<script>
import filesService from 'com_emundus/src/services/files';
import errors from "@/mixins/errors";
import ApplicationTabs from "./ApplicationTabs.vue";

export default {
  name: "ApplicationSingle",
  components: {ApplicationTabs},
  props: {
    file: Object | String,
    type: String,
    user: {
      type: String,
      required: true,
    },
    ratio: {
      type: String,
      default: '66/33'
    },
    context: {
      type: String,
      default: ''
    }
  },
  mixins: [errors],
  data: () => ({
    fnums: [],
    selectedFile: null,
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
    hidden: false,
    loading: false
  }),

  created() {
    document.querySelector('body').style.overflow = 'hidden';
    var r = document.querySelector(':root');
    let ratio_array = this.$props.ratio.split('/');
    r.style.setProperty('--attachment-width', ratio_array[0] + '%');

    this.selectedFile = this.file;

    // if props file is not null, then render
    if (typeof this.selectedFile !== 'undefined' && this.selectedFile !== null) {
      this.render();
    } else {
      // hide modal if no file is selected
      this.$modal.hide('application-modal');
    }

    this.addEventListeners();
  },
  onBeforeDestroy() {
    window.removeEventListener('openSingleApplicationWithFnum');
  },

  methods: {
    addEventListeners() {
      window.addEventListener('openSingleApplicationWithFnum', (e) => {
        this.selectedFile = e.detail.fnum;

        if (e.detail.fnums) {
          this.fnums = e.detail.fnums;
        }

        if (typeof this.selectedFile !== 'undefined' && this.selectedFile !== null) {
          this.render();
        }
      });
    },
    render() {
      this.loading = true;
      let fnum = '';

      if (typeof this.selectedFile == 'string') {
        fnum = this.selectedFile;
      } else {
        fnum = this.selectedFile.fnum;
      }

      if (typeof this.selectedFile == 'string') {
        filesService.getFile(fnum, this.$props.type).then((result) => {
          if (result.status == 1) {
            this.selectedFile = result.data;
            this.access = result.rights;
            this.updateURL(this.selectedFile.fnum)
            this.getApplicationForm();
            if (this.$props.type === 'evaluation') {
              this.getEvaluationForm();
            }

            this.$modal.show('application-modal');
            this.hidden = false;
          } else {
            this.displayError('COM_EMUNDUS_FILES_CANNOT_ACCESS', 'COM_EMUNDUS_FILES_CANNOT_ACCESS_DESC'
            ).then((confirm) => {
              if (confirm === true) {
                this.$modal.hide('application-modal');
                this.hidden = true;
              }
            });
          }
        });
      } else {
        filesService.checkAccess(fnum).then((result) => {
          if (result.status == true) {
            this.access = result.data;
            this.updateURL(this.selectedFile.fnum)
            if (this.access['1'].r) {
              this.getApplicationForm();
            } else {
              if (this.access['4'].r) {
                this.selected = 'attachments';
              } else if (this.access['10'].r) {
                this.selected = 'comments';
              }
            }
            if (this.$props.type === 'evaluation') {
              this.getEvaluationForm();
            }
            this.$modal.show('application-modal');
            this.hidden = false;
          } else {
            this.displayError('COM_EMUNDUS_FILES_CANNOT_ACCESS', 'COM_EMUNDUS_FILES_CANNOT_ACCESS_DESC').then((confirm) => {
              if (confirm === true) {
                this.$modal.hide('application-modal');
                this.hidden = true;
              }
            });
          }
        }).catch((error) => {
          this.displayError('COM_EMUNDUS_FILES_CANNOT_ACCESS', 'COM_EMUNDUS_FILES_CANNOT_ACCESS_DESC');
          this.loading = false;
        });
      }
    },

  methods:{
    getApplicationForm() {
      const fnum = this.file.fnum ? this.file.fnum : this.file;

      filesService.getApplicationForm(fnum).then((response) => {
        this.applicationform = response.data;
        if (this.type !== 'evaluation') {
          this.loading = false;
        }
      });
    },
    getEvaluationForm() {
      if (this.selectedFile.id != null) {
        this.rowid = this.selectedFile.id;
      }
      if (typeof this.selectedFile.applicant_id != 'undefined') {
        this.student_id = this.selectedFile.applicant_id;
      } else {
        this.student_id = this.selectedFile.student_id;
      }
      let view = 'form';

      filesService.getEvaluationFormByFnum(this.selectedFile.fnum, this.$props.type).then((response) => {
        if (response.data !== 0) {
          if (typeof this.selectedFile.id === 'undefined') {
            filesService.getMyEvaluation(this.selectedFile.fnum).then((data) => {
              this.rowid = data.data;
              if (this.rowid == null) {
                this.rowid = "";
              }

              this.url = 'index.php?option=com_fabrik&c=form&view=' + view + '&formid=' + response.data + '&rowid=' + this.rowid + '&jos_emundus_evaluations___student_id[value]=' + this.student_id + '&jos_emundus_evaluations___campaign_id[value]=' + this.selectedFile.campaign + '&jos_emundus_evaluations___fnum[value]=' + this.selectedFile.fnum + '&student_id=' + this.student_id + '&tmpl=component&iframe=1'
            });
          } else {
            this.url = 'index.php?option=com_fabrik&c=form&view=' + view + '&formid=' + response.data + '&rowid=' + this.rowid + '&jos_emundus_evaluations___student_id[value]=' + this.student_id + '&jos_emundus_evaluations___campaign_id[value]=' + this.selectedFile.campaign + '&jos_emundus_evaluations___fnum[value]=' + this.selectedFile.fnum + '&student_id=' + this.student_id + '&tmpl=component&iframe=1'
          }
        }
      });
    },
    iframeLoaded() {
      this.loading = false;
    },
    updateURL(fnum = '') {
      let url = window.location.href;
      url = url.split('#');

      if (fnum === '') {
        window.history.pushState('', '', url[0]);
      } else {
        window.history.pushState('', '', url[0] + '#' + fnum);
      }
    },
    onClose(e) {
      e.preventDefault();
      this.hidden = true;
      this.$modal.hide('application-modal');
      document.querySelector('body').style.overflow= 'visible';
      swal.close();
    },
    openNextFnum() {
      let index = typeof this.selectedFile === 'string' ? this.fnums.indexOf(this.selectedFile) : this.fnums.indexOf(this.selectedFile.fnum);
      if (index !== -1 && index < this.fnums.length - 1) {
        const newIndex = index + 1;
        if (newIndex > this.fnums.length) {
          this.selectedFile = this.fnums[0];
        } else {
          this.selectedFile = this.fnums[newIndex];
        }

        this.render();
      }
    },
    openPreviousFnum() {
      let index = typeof this.selectedFile === 'string' ? this.fnums.indexOf(this.selectedFile) : this.fnums.indexOf(this.selectedFile.fnum);

      if (index !==-1 && index > 0) {
        const newIndex = index - 1;
        if (newIndex < 0) {
          // open last fnum
          this.selectedFile = this.fnums[this.fnums.length - 1];
        } else {
          this.selectedFile = this.fnums[newIndex];
        }
        this.render();
      }
    },
  },
  computed: {
    ratioStyle() {
      let ratio_array = this.$props.ratio.split('/');
      return ratio_array[0] + '% ' + ratio_array[1] + '%';
    },
    tabsICanAccessTo() {
      return this.tabs.filter(tab => this.access[tab.access].r);
    }
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

.em-container-form-heading {
  display: none;
}

#iframe {
  height: 100vh;
  overflow-y: scroll;
  overflow-x: hidden;
}

.iframe-evaluation {
  width: 100%;
  height: 90%;
  border: unset;
}

#modal-evaluationgrid {
  border-left: 1px solid #EBECF0;
  box-shadow: 0px 4px 16px rgba(32, 35, 44, 0.1);
}

.sticky-tab {
  position: sticky;
  top: 0;
  background: white;
}

#modal-applicationform #em-attachments .v--modal-overlay {
  height: 100% !important;
  width: var(--attachment-width) !important;
  margin-top: 50px;
}

#modal-applicationform #em-attachments .v--modal-box.v--modal {
  width: 100% !important;
  height: calc(100vh - 50px) !important;
  box-shadow: unset;
}

#modal-applicationform #em-attachments .modal-body {
  width: 100%;
}

#modal-applicationform #em-attachments #em-attachment-preview {
  width: 100%;
}

.context-files:not(.hidden) {
  position: fixed;
  top: 0;
  left: 0;
  background-color: white;
  z-index: 9999;
  width: 100vw;
  height: 100vh;
}

.hidden {
  display: none;
  z-index: -1;
  margin: 0;
  padding: 0;
  width: 0;
  height: 0;
}
</style>