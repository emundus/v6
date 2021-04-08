<template>
  <div id="stepflow">
    <button @click="createStep()" v-if="!hideStep">Creer nouvelle etape</button>
    <div class="min-h-screen flex overflow-x-scroll py-12">
      <div v-for="column in columns" :key="column.title" class="bg-gray-100 rounded-lg px-3 py-3 column-width rounded mr-4" :id="'step_' + column.id" @click="openStep(column.id)" v-if="!hideStep">
        <div contenteditable="true" class="editable-step-label" :id="'step_label_' + column.id" v-on:keyup.enter="setStepLabel(column.id)" style="background: #a8bb4a">{{ column.title }}</div>
        <modal-config-step :ID="column.id" :element="column"/>
        <button @click="deleteStep(column.id)">Annuler etape</button>
        <button @click="configStep(column.id)">Configurer</button>
      </div>
    <workflow-space v-for="column in columns" v-if="currentStep == column.id" :step="column"/>
    </div>
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

  props: {
    ID: Number,             //id of step flow
    element: Object,
  },

  data() {
    return {
      columns: [],
      currentStep: '',
      hideStep: false,
    };
  },

  created() {
    this.getAllSteps(); //// get all steps by workflow
  },

  methods: {
    openStep: function(id) {
      this.currentStep = id;
      this.hideStep = true;
    },

    createStep: function() {
      var _data = {
        workflow_id : this.getWorkflowIdFromURL(),
        step_label: 'Etape # anonyme',
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
          this.columns.push({
            id: response.data.data,
            title: _data.step_label,
          })
      })
    },

    deleteStep: function(id) {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=deletestep',
        params: { id },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.columns = this.columns.filter((step) => {
          return step.id !== id;   // delete
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
          data: this.getWorkflowIdFromURL(),
        },
        paramsSerializer: params =>{
          return qs.stringify(params);
        }
      }).then(response => {
        var _steps = response.data.data;

        _steps.forEach(step => {
          this.columns.push({
            id: step.id,
            title: step.step_label,
          })
        })
      })
    },

    configStep: function(id) {
      this.$modal.show("stepModal" + id);
    },

    // get the workflow id from url
    getWorkflowIdFromURL: function () {
      return window.location.href.split('id=')[1];
    },

    setStepLabel: function(id) {
      var data = {
        step_label: document.getElementById('step_label_' + id).innerText,
      }

      console.log(data.step_label);
    }
  }
};
</script>

<style>
.column-width {
  min-width: 240px;
  width: 450px;
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
  margin-right: 1rem;
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

</style>
