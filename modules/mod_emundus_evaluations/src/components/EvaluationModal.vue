<template>
  <modal
      id="evaluation-modal"
      name="evaluation-modal"
      height="100vh"
      width="100vw"
      styles="display:flex;flex-direction:column;justify-content:center;align-items:center;"
  >
    <div class="em-modal-header em-w-100">
      <div class="em-flex-row em-pointer" id="evaluation-modal-close" @click="$emit('reload-list');$modal.hide('evaluation-modal')">
        <div class="em-w-max-content em-flex-row">
          <span class="material-icons-outlined">arrow_back</span>
          <span class="em-ml-8">{{ translate('MOD_EMUNDUS_EVALUATIONS_BACK') }}</span>
        </div>
      </div>
    </div>

    <div class="modal-grid">
      <DragCol
          width="100vw"
          height="auto"
      >
        <template #left>
          <div id="modal-applicationform">
            <div class="em-p-16-0">
              <h3 class="em-w-100 em-flex-row em-flex-center">{{ translate('MOD_EMUNDUS_EVALUATIONS_APPLICATION_FORM')}}</h3>
              <button class="btn btn-primary em-ml-16" @click="exportFile">{{ translate('MOD_EMUNDUS_EVALUATIONS_APPLICATION_DOWNLOAD')}}</button>
            </div>
            <div class="scrollable">
              <div v-html="applicationform"></div>
              <Attachments v-if="preview" :user="current_user" :fnum="file.fnum"></Attachments>
              <div v-else :class="{ 'loading': loading }" class="em-p-16">
                <h3 class="em-mb-8">{{ translate('MOD_EMUNDUS_EVALUATIONS_ATTACHMENTS') }}</h3>
                <table aria-describedby="Table of files attachments" class="em-mt-16">
                  <thead>
                  <tr>
                    <th v-for="column in activeAttachmentColumns" :key="column.name" :id="column.name">
                      {{ translate(column.label) }}
                    </th>
                  </tr>
                  </thead>
                  <tbody>
                  <tr v-for="attachment in attachments" class="file-row em-pointer" :key="attachment.id" @click="downloadAttachment(attachment)">
                    <td class="td-file" v-for="column in activeAttachmentColumns" :key="column.name" :id="'value_'+column.name">
                      <span v-if="column.name === 'timedate'">{{ formattedDate(attachment[column.name]) }}</span>
                      <a v-else-if="column.name === 'value'" style="text-decoration: underline;">
                        {{ attachment[column.name] }}
                      </a>
                      <span v-else>{{ attachment[column.name] }}</span>
                    </td>
                  </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </template>

        <template #right>
          <div id="modal-evaluationgrid">
            <div class="em-p-16-0">
              <h3 class="em-w-100 em-flex-row em-flex-center">{{ translate('MOD_EMUNDUS_EVALUATIONS_EVALUATION_GRID') }}</h3>
            </div>
            <iframe :src="url" class="iframe-evaluation" @load="loading = false;" id="iframe-evaluation" title="Evaluation form" />
            <div class="em-page-loader" v-if="loading"></div>
          </div>
        </template>
      </DragCol>
    </div>
  </modal>
</template>

<script>
/* IMPORT YOUR COMPONENTS */
import axios from "axios";
import Attachments from "../../../../components/com_emundus/src/views/Attachments";
import {
  DragCol,
  DragRow,
  ResizeCol,
  ResizeRow,
  Resize,
} from "vue-resizer";


/* IMPORT YOUR SERVICES */
import attachmentService from "../../../../components/com_emundus/src/services/attachment.js";
import mixin from "../../../../components/com_emundus/src/mixins/mixin.js";

export default {
  name: "EvaluationModal",
  components: {
    Attachments,
    DragCol
  },
  props: {
    file: {
      type: Object,
      required: true
    },
    evaluation_form: {
      type: Number,
      required: true
    },
    preview: {
      type: Boolean,
      default: false
    },
    readonly: {
      type: Boolean,
      default: false
    }
  },
  mixins: [mixin],
  data: () => ({
    applicationform: '',
    attachments: '',
    attachments_column:[
      {
        label: 'MOD_EMUNDUS_EVALUATIONS_ATTACHMENT_NAME',
        name: 'value',
        active: true
      },
      {
        label: 'MOD_EMUNDUS_EVALUATIONS_ATTACHMENT_SENT_ON',
        name: 'timedate',
        active: false
      },
      {
        label: 'MOD_EMUNDUS_EVALUATIONS_ATTACHMENT_DESCRIPTION',
        name: 'upload_description',
        active: false
      },
    ],

    url: '',
    rowid: '',
    student_id: '',
    current_user: 0,
    loading: false,
  }),
  mounted() {
    this.loading = true;
    this.getApplicationForm();
    this.getAttachments();
    this.getEvaluationForm();
    this.current_user = this.$store.state.user.currentUser;
  },
  methods: {
    getApplicationForm(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&view=application&format=raw&layout=form&fnum="+this.file.fnum,
      }).then(response => {
        this.applicationform = response.data;
      });
    },

    async getAttachments(){
      const response = await attachmentService.getAttachmentsByFnum(this.$props.file.fnum);

      if (response.status) {
        this.attachments = response.attachments;
      }
    },

    downloadAttachment(attachment){
      let url = '/images/emundus/files/'+this.student_id+'/'+attachment.filename;
      window.open(url, '_blank');
    },

    exportFile(){
      const formData = new FormData();
      formData.append('student_id', this.student_id);
      formData.append('fnum', this.$props.file.fnum);

      axios.post('index.php?option=com_emundus&controller=application&task=exportpdf', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then((response) => {
        window.open(response.data.link, '_blank');
      })
    },

    getEvaluationForm(){
      if (this.$props.file.id != null) {
        this.rowid = this.$props.file.id;
      }
      if (this.$props.file.student_id != null) {
        this.student_id = this.$props.file.student_id;
      }
      let view = this.$props.readonly ? 'details' : 'form';

      this.url = 'index.php?option=com_fabrik&c=form&view=' + view + '&formid='+this.$props.evaluation_form+'&rowid='+this.rowid+'&jos_emundus_evaluations___student_id[value]='+this.student_id+'&jos_emundus_evaluations___campaign_id[value]='+this.$props.file.campaign_id+'&jos_emundus_evaluations___fnum[value]='+this.$props.file.fnum+'&student_id='+this.student_id+'&tmpl=component&iframe=1'
    },
  },

  computed: {
    activeAttachmentColumns() {
      return this.attachments_column.filter((col) => {
        return col.active;
      });
    }
  }
}
</script>

<style lang="scss">
.modal-grid{
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  grid-gap: 16px;
  width: 100%;
  height: 100vh;
  #modal-applicationform {
    grid-column: 1;

    .scrollable {
      height: calc(100vh - 184px);
      overflow-y: scroll;
      overflow-x: hidden;
    }

    table tr {
      td:first-child {
        vertical-align: baseline;
      }

      &:hover {
        background-color: #f2f2f3 !important;
      }
    }
  }
  #modal-evaluationgrid{
    grid-column: 2;
    padding-right: 16px;
    height: 90vh;
    margin-left: 48px;
  }
}
.em-modal-header{
  padding: 24px;
}
.holds-iframe{
  display: none;
}
#iframe{
  height: 100vh;
  overflow-y: scroll;
  overflow-x: hidden;
}
.em-container-form-heading{
  display: none;
}
.iframe-evaluation{
  width: 100%;
  height: 100%;
  border: unset;
}
.w--current{
  background: #E3E3E3;
}
.nav-tabs > li > a:hover{
  color: black;
}

#em-attachments  {
  .head, .category-select {
    display: none !important;
  }

  .table-wrapper {
    .date, .desc, .category ,.status, .user, .modified_by, .modified, .permissions, td:nth-child(7), td:nth-child(8), td:nth-child(1) {
      display: none;
    }

    #user, #modified_by, #check-th {
      display: none;
    }
  }

  #filters {
    display: none;
  }
}

#em-switch-profiles {
  display: none;
}

.drager_col{
  display: flex;
}

.drager_col > .slider_col {
  transition: background 0.2s;
  position: relative;
  z-index: 1;
  cursor: col-resize;
  background: #f0f0f0;
  margin-left: 0px !important;
  margin-right: 0px !important;
  height: 90vh;
  border-radius: 8px;
}
.drager_col > .slider_col:before {
  transition: background-color 0.2s;
  position: absolute;
  top: 50%;
  left: 31%;
  transform: translateY(-50%);
  content: "";
  display: block;
  width: 1px;
  height: 24%;
  min-height: 30px;
  max-height: 70px;
  background-color: #6f808d;
}
.drager_col > .slider_col:after {
  transition: background-color 0.2s;
  position: absolute;
  top: 50%;
  right: 31%;
  transform: translateY(-50%);
  content: "";
  display: block;
  width: 1px;
  height: 24%;
  min-height: 30px;
  max-height: 70px;
  background-color: #6f808d;
}
.drager_col > .slider_col:hover:before,
.drager_col > .slider_col:hover:after,
.drager_col > .slider_col:active:before,
.drager_col > .slider_col:active:after {
  background-color: #6f808d;
}
.drager_col > .slider_col:hover,
.drager_col > .slider_col:active {
  background: #e3e3e3;
}
</style>
