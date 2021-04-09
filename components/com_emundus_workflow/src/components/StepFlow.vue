<template>
  <div id="stepflow">
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

    <div contenteditable="true" class="editable-workflow-name" id="editable-workflow-name-div" v-on:keyup.enter="setWorkflowLabel()" v-b-tooltip.top.hover title="Cliquer sur le nom du workflow pour le changer" v-if="!hideStep">
      {{ this.workflowName }}
    </div>

    <p class="tooltip" v-if="!hideStep"> Dernier mis a jour: {{ this.lastUpdated }}</p>

    <div class="min-h-screen flex overflow-x-scroll py-12">
      <div v-for="step in steps" :key="step.title" class="bg-gray-100 rounded-lg px-3 py-3 column-width rounded mr-4" :id="'step_' + step.id" v-on:dblclick="openStep(step.id)" v-if="!hideStep" style="margin-right: 10rem !important">
        <div contenteditable="true" class="editable-step-label" class="step-label" :id="'step_label_' + step.id" v-on:keyup.enter="setStepLabel(step.id)" style="background: rgb(134, 230, 230) none repeat scroll 0% 0%">{{ step.title }}</div>
        <modal-config-step :ID="step.id" :element="step"/>
        <table style="border:0px; margin: -5px">
          <tr>
            <th class="btn-group-step"><b-button variant="danger" @click="deleteStep(step.id)" style="margin:5px; width: max-content">Supprimer &nbsp<b-icon icon="scissors"></b-icon></b-button></th>
            <th class="btn-group-step"><b-button variant="warning" @click="configStep(step.id)" style="margin:10px; width: max-content">Configurer &nbsp<b-icon icon="gear"></b-icon></b-button></th>
          </tr>
        </table>
      </div>
        <workflow-space v-for="step in steps" v-if="currentStep == step.id" :step="step"/>
    </div>

    <b-button variant="success" @click="createStep()" v-if="!hideStep" style="position:absolute; width: max-content" size="lg">Ajouter étape &nbsp<b-icon icon="calendar-plus"></b-icon></b-button>

  </div>
</template>

<script>
import axios from 'axios';
import ModalConfigStep from "../ModalConfigStep";
import SimpleFlowchart from "./SimpleFlowchart";
import WorkflowSpace from "../WorkflowSpace";
const qs = require('qs');

export default {
  name: "stepflow",

  components: {ModalConfigStep, SimpleFlowchart, WorkflowSpace},

  props: {},

  data() {
    return {
      steps: [],
      currentStep: '',
      hideStep: false,
      workflowName: '',
      lastUpdated: '',
    };
  },

  created() {
    this.getAllSteps(); //// get all steps by workflow
    this.getWorkflow();
  },

  methods: {
    openStep: function(id) {
      this.currentStep = id;
      this.hideStep = true;
    },

    createStep: function() {
      var _data = {
        workflow_id : this.getWorkflowIdFromURL(),
      }
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=createstep',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: _data
        })
      }).then(response => {
        this.steps.push({
          id: response.data.data.step_id,
          title: 'Etape # anonyme ' + response.data.data.step_id,       // default name of step
        })
      })
    },

    deleteStep: function(id) {
      var data = {
        id: id,
        wid: this.getWorkflowIdFromURL(),
      }
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=deletestep',
        params: { data },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.steps = this.steps.filter((step) => {
          return step.id !== id;   // delete step
        })
      })
    },

    getAllSteps: function() {
      axios({
        method: 'get',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=getallsteps',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: {
          wid: this.getWorkflowIdFromURL(),
        },
        paramsSerializer: params =>{
          return qs.stringify(params);
        }
      }).then(response => {
        var _steps = response.data.data;

        _steps.forEach(step => {
          this.steps.push({
            id: step.id,
            title: 'Etape # anonyme ' + step.id,
          })
        })
      })
    },

    configStep: function(id) {
      this.$modal.show("stepModal" + id);
    },

    // get the workflow id from url --> base function
    getWorkflowIdFromURL: function () {
      return window.location.href.split('id=')[1];
    },

    setStepLabel: function(id) {
      var data = {
        step_label: document.getElementById('step_label_' + id).innerText,
        id: id,
      }

      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=updateparams',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: { data },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {

      }).catch(error => {
        console.log(error);
      })
    },

    getWorkflow: function () {
      //// change post --> get to improve the performance
      axios({
        method: 'get',
        url: 'index.php?option=com_emundus_workflow&controller=workflow&task=getworkflowbyid',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: {
          wid: this.getWorkflowIdFromURL(),
        },
        paramsSerializer: params =>{
          return qs.stringify(params);
        }
      }).then(response => {
        var data = (response.data.data)[0];
        this.workflowName = data.workflow_name;
        this.lastUpdated = data.updated_at;
      })
    },

    setWorkflowLabel: function() {
      var info = {
        workflow_name: document.getElementById('editable-workflow-name-div').innerText,
        id: this.getWorkflowIdFromURL(),
      }

      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=workflow&task=updateworkflow',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: info,
        })
      }).then(response => {
      }).catch(error => {
        console.log(error);
      })
    },
  }
};
</script>

<style>
.column-width {
  min-width: 300px;
  width: 400px;
}

.px-3 {
  padding-left: .75rem;
  padding-right: .75rem;
}

.py-3 {
  padding-top: .75rem;
  padding-bottom: .75rem;
}

.mr-4 {
  margin-right: 7.5rem !important;
}

.rounded-lg {
  border-radius: .5rem;
}

.bg-gray-100 {
  background-color: #e3e3e3;;
  background-image: radial-gradient(circle, black 1px, rgba(0, 0, 0, 0) 1px);
  background-size: 2em 2em;
}

.py-12 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

.overflow-x-scroll {
  overflow-x: scroll;
}

.min-h-screen {
  min-height: 25vh;
  margin-top: 5vh;
}

.flex {
  display: flex;
}

*, ::after, ::before {
  box-sizing: border-box;
  border-width: 0;
  border-style: solid;
  border-color: #e2e8f0;
}

[contenteditable="true"].editable-step-label {
  white-space: nowrap;
  overflow: hidden;
}
[contenteditable="true"].editable-step-label br {
  display:none;
}
[contenteditable="true"].editable-step-label * {
  display:inline;
  white-space:nowrap;
}

.editable-workflow-name {
  color: #118a3b !important;
  font-size: xx-large !important;
  font-weight: bold !important;
  width: max-content;
  /*border-bottom: 1px dotted black;*/
  text-decoration: underline #28a745;
}

[contenteditable="true"].editable-workflow-name {
  white-space: nowrap;
  width:max-content;
  overflow: hidden;
  position: absolute;
  top: 7vh;
}
[contenteditable="true"].editable-workflow-name br {
  display:none;
}
[contenteditable="true"].editable-workflow-name * {
  display:inline;
  white-space:nowrap;
}

.tooltip {
  opacity: 1 !important;
  font-size: small !important;
  color: #8a8a8a;
  position: absolute;
  top: 12vh;
}

.step-label {
  text-align: center;
  font-family: Arial, Helvetica, sans-serif;
  width:19.5vh;
}

.btn-group-step {
  background: none !important;
  padding: 0;
}
</style>
