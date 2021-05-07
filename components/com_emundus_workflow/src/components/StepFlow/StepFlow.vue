<template>
  <div id="stepflow">
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

    <div contenteditable="true" class="editable-workflow-label" id="editable-workflow-label" v-on:keyup.enter="updateWorkflowLabel()" v-if="hideStep == false">
      {{ this.workflowLabel }}
    </div>

    <b-button @click="createStep()" v-if="hideStep == false" variant="success" style="position: sticky">(+)</b-button>
    <!--    <div class="min-h-screen flex overflow-x-scroll py-12">-->
    <draggable :invertedSwapThreshold="0.4" :invertSwap="true" v-model="columns" :group="columns" class="flex" :sort="true">
      <div v-for="column in columns" :key="column.title" class="bg-gray-100 rounded-lg px-3 py-3 column-width rounded mr-4" :id="'step_' + column.id"
           v-on:dblclick="openStep(column.id)"
           v-if="hideStep == false" style=""
           @dragstart="dragStart"
           @dragend="dragEnd"
           @mousedown="handleDown">
<!--           v-bind:style="{ backgroundColor : column.style }"-->
<!--      >-->

        <div contenteditable="true" class="editable-step-label" :id="'step_label_' + column.id" v-on:keyup.enter="setStepLabel(column.id)" style="background: #a8bb4a">{{ column.title }}</div>
        <div style="color:red">{{ column.stateIn }}</div>
        <div style="color:blueviolet">{{ column.stateOut }}</div>
        <div style="color:blue"> {{ column.startDate }}</div>
        <div style="color:orange"> {{ column.endDate }}</div>
        <div style="color:forestgreen"> {{ column.order }} </div>
        <div style="color:lightseagreen"> {{ column.emailTemplate }} </div>
        <div style="color:midnightblue"> {{ column.destination }} </div>
        <div style="color:darkolivegreen"> {{ column.users }}</div>
        <modal-config-step :ID="column.id" :element="column" @updateStep="updateStep" @deleteStep="deleteStep(column.id)"/>
        <!--        <div>{{ column.stateIn }}</div>-->
        <!--        <div>{{ column.stateOut }}</div>-->
        <b-button @click="configStep(column.id)" variant="warning">Configurer</b-button>
        <b-button @click="deleteStep(column.id)" variant="danger" style="margin-left: 20px">(-)</b-button>
      </div>
      <workflow-space v-for="column in columns" v-if="currentStep == column.id && hideWorkflow == false" :step="column" @returnBack="returnToStepFlow"/>
    </draggable>
    <!--  </div>-->
  </div>
</template>

<script>
import axios from 'axios';
import ModalConfigStep from "../Modal/StepFlowModal/ModalConfigStep";
import SimpleFlowchart from "../Workflow/Workspace/elements/SimpleFlowchart";
import WorkflowSpace from "../Workflow/Workspace/WorkflowSpace";
const qs = require('qs');
import $ from 'jquery';

import { commonMixin } from '../../mixins/common-mixin';
import workflowDashboard from "../Workflow/Dashboard/WorkflowDashboard"; /// using mixin in this case

import draggable from 'vuedraggable';
import PulseLoader from 'vue-spinner/src/PulseLoader.vue'

export default {
  name: "stepflow",
  mixins: [commonMixin],

  components: {ModalConfigStep, SimpleFlowchart, WorkflowSpace, draggable, PulseLoader},

  props: {},

  data() {
    return {
      columns: [],
      currentStep: '',
      hideStep: false,
      stateIn: '',
      stateOut: '',
      workflowLabel: '',
      hideWorkflow: false,
      loading: false,
    };
  },

  created() {
    this.getWorkflowFromURL();    //// get workflow name from url
    this.getAllSteps(); //// get all steps by workflow
  },

  methods: {
    handleDown(e) {
      const target = e.target || e.srcElement;
      if(target.className === 'vm--modal') {
        e.preventDefault();     /// prevent page loading
      }
    },

    dragStart: function(event) {
      const e = event.target.id;      // get the <div id> from selected step
      let id = e.split('step_')[1];   // get id
      event.dataTransfer.setData('step', id);
      this.$emit('dragStart', id);
    },

    dragEnd: function(event) {
      let newIndex = [];
      event.preventDefault();
      this.columns.forEach((elt) => {
        // newIndex[elt.id] = this.columns.indexOf(elt);
        newIndex[this.columns.indexOf(elt)] = elt.id;
      });

      var counter;
      for(counter = 0; counter < newIndex.length; counter++) {
        const _id = (element) => element.id == newIndex[counter];
        var _index = this.columns.findIndex(_id);
        this.columns[_index]['order'] = counter;
        this.$forceUpdate();
      }

      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=updatestepordering',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: newIndex,
          wid: this.$data.id,
        })
      }).then(response => {
        /// I think I should put this code snippet before axios -->
      }).catch(error => {
        console.log(error);
      })
    },

    returnToStepFlow(result) {
      if (result === true) {
        this.hideStep = false;
        this.hideWorkflow = true;
      }
    },

    updateStep(result) {
      console.log(result);
      const _id = (element) => element.id == result['id'];
      var _index = this.columns.findIndex(_id);
      this.columns[_index]['stateIn'] = result['input'];
      this.columns[_index]['stateOut'] = result['output'];
      this.columns[_index]['title'] = result['label'];
      this.columns[_index]['startDate'] = result['startDate'];
      this.columns[_index]['endDate'] = result['endDate'];
      this.columns[_index]['emailTemplate'] = result['email'];
      this.columns[_index]['destination'] = result['destination'];
      this.columns[_index]['style'] = result['color'];
      this.columns[_index]['users'] = result['users'];
      this.$forceUpdate();

      //// forceupdate --> call api to update status in database --> checkin if status (after) and status (before) are the same --> do nothing /// otherwise, call to axios
    },

    openStep: function (id) {
      this.currentStep = id;
      this.hideStep = true;
      this.hideWorkflow = false;
    },

    createStep: function () {
      let _data = {
        workflow_id: this.$data.id,
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
          id: response.data.data.step_id,
          order: response.data.data.ordering,
        })

        setTimeout(() => {
          this.$modal.show('stepModal' + response.data.data.step_id)
        }, 500);
      })
    },

    deleteStep: function (id) {
      var data = {
        id: id,
        wid: this.$data.id,
      }
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=deletestep',
        params: {data},
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.columns = this.columns.filter((step) => {
          return step.id !== id;   // delete step
        })

        let newIndex = [];
        this.columns.forEach((elt) => {
          // newIndex[elt.id] = this.columns.indexOf(elt);
          newIndex[this.columns.indexOf(elt)] = elt.id;
        });

        var counter;
        for(counter = 0; counter < newIndex.length; counter++) {
          const _id = (element) => element.id == newIndex[counter];
          var _index = this.columns.findIndex(_id);
          this.columns[_index]['order'] = counter;
          this.$forceUpdate();
        }
      })
    },

    getAllSteps: function () {
      axios({
        method: 'get',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=getallsteps',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: {
          wid: this.$data.id,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.columns = response.data.data;
        var steps = response.data.data; // get all steps
        steps.forEach(elt => {
          axios({
            method: 'get',
            url: 'index.php?option=com_emundus_workflow&controller=step&task=getcurrentparams',
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            params: {sid : elt.id},
            paramsSerializer: params => {
              return qs.stringify(params);
            }
          }).then(answer => {
            const _id = (element) => element.id == elt.id;
            var _index = this.columns.findIndex(_id);
            var _temp = answer.data.data.inputStatusNames;
            var _stateIn = [];
            _temp.forEach(elt => _stateIn.push(elt.value));
            this.columns[_index]['stateIn'] = _stateIn.toString();
            this.columns[_index]['stateOut'] = (answer.data.data.outputStatusNames)[0].value;
            this.columns[_index]['title'] = answer.data.data.stepLabel;
            this.columns[_index]['startDate'] = answer.data.data.startDate;
            this.columns[_index]['endDate'] = answer.data.data.endDate;
            this.columns[_index]['order'] = answer.data.data.ordering;
            this.columns[_index]['style'] = answer.data.data.color;

            if(answer.data.data.message === undefined) {
              this.columns[_index]['emailTemplate'] = "";
              this.columns[_index]['destination'] = "";
            } else {
              this.columns[_index]['emailTemplate'] = answer.data.data.message.emailLabel;
              this.columns[_index]['destination'] = answer.data.data.message.destinationLabel;
            }
            this.$forceUpdate();
          })
        })
      })
    },

    configStep: function (id) {
      this.$modal.show("stepModal" + id);
    },

    getWorkflowFromURL: function () {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=workflow&task=getworkflowbyid',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          wid: this.$data.id,
        })
      }).then(response => {
        this.workflowLabel = ((response.data.data)[0]).workflow_name;
      }).catch(error => {
        console.log(error);
      })
    },

    updateWorkflowLabel: function () {
      let newLabel = {
        workflow_name: $("#editable-workflow-label").text(),
        id: this.$data.id,
      }

      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=workflow&task=updateworkflowlabel',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: newLabel,
        })
      }).then(response => {
        console.log(response);
      }).catch(error => {
        console.log(error);
      })
    },

    setStepLabel: function (id) {
      var data = {
        step_label: $("#step_label_" + id).text(),
        id: id,
        workflow_id: this.$data.id,
      }

      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=updateparams',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        params: {data},
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        var _index = this.columns.findIndex((element) => element.id == id);
        this.columns[_index]['title'] = data.step_label;
      }).catch(error => {
        console.log(error);
      })
    },
  }
};
</script>

<style>
.column-width {
  min-width: 240px;
  width: 450px;
}
/*.column-width:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.px-3 {
  padding-left: .75rem;
  padding-right: .75rem;
}
/*.px-3:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.py-3 {
  padding-top: .75rem;
  padding-bottom: .75rem;
}
/*.py-3:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.mr-4 {
  margin-right: 1rem;
}
/*.mr-4:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.rounded-lg {
  border-radius: .5rem;
}
/*.rounded-lg:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.bg-gray-100 {
  background-color: #fff;;
  /*background-image: radial-gradient(circle, black 1px, rgba(0, 0, 0, 0) 1px);*/
  background-size: 2em 2em;
}
/*.bg-gray-100:active {*/
/*  animation-name: shake; animation-duration: 0.07s; animation-iteration-count: infinite; animation-direction: alternate;*/
/*}*/
.py-12 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

.overflow-x-scroll {
  overflow-x: scroll;
}

.min-h-screen {
  min-height: 25vh;
  display: grid !important;
  grid-template-columns: repeat(3, 1fr);
  grid-gap: 60px;
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

.editable-workflow-label {
  color: #118a3b !important;
  font-size: xx-large !important;
  /*font-weight: bold !important;*/
  width: max-content;
  /*border-bottom: 1px dotted black;*/
  text-decoration: underline #28a745;
}

[contenteditable="true"].editable-workflow-label {
  white-space: nowrap;
  overflow: hidden;
}
[contenteditable="true"].editable-workflow-label br {
  display:none;
}
[contenteditable="true"].editable-workflow-label * {
  display:inline;
  white-space:nowrap;
}

/* editable step label */
.editable-step-label {
  color: #118a3b !important;
  font-size: xx-large !important;
  /*font-weight: bold !important;*/
  width: max-content;
  /*border-bottom: 1px dotted black;*/
  text-decoration: underline #28a745;
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

.flex {
  display: flex;
  flex-direction: row;
  justify-content: center;
  flex-wrap: wrap;
  gap: 60px;
  min-height: 60vh;
  margin-top: 5vh;
}
@keyframes shake {
  from {
    transform: rotate(-4deg);
  }
  to {
    transform: rotate(4deg);
  }
}
</style>
