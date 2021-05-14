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
      <div v-for="column in columns" :key="column.title" class="bg-gray-100 rounded-lg px-3 py-3 column-width rounded mr-4" :id="'step_' + column.id" v-if="hideStep == false" style=""
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
        <modal-config-step :ID="column.id" :element="column" @updateStep="updateStep" @deleteStep="deleteStep(column.id)" ref="stepModal"/>
        <b-button @click="configStep(column.id)" variant="warning">Configurer</b-button>
        <b-button @click="deleteStep(column.id)" variant="danger" style="margin-left: 20px">(-)</b-button>
        <b-button @click="openStep(column.id)" variant="primary" style="margin-left: 20px">Ouvrir </b-button>
        <hr/>
        <div style="position: sticky"> <b-button variant="info" @click="createMessageDiv(column.id)">(+)</b-button> </div>
        <div class="message-block" v-for="message in messages" :id="'message_zone' + message.id" v-if="column.id == message.parent_id">
          <div>
            {{ message.title }}

            <b-button @click="openDiv(message.id)" variant="primary" :id="'button_' + message.id">Trigger</b-button>
            <b-button :id="'button_' + message.id" variant="danger" @click="deleteMessageDiv(message.id)">x</b-button>

            <div style="color:forestgreen"> {{ message.messageTemplate }} </div>
            <div style="color:lightseagreen"> {{ message.messageDestination }} </div>
            <div style="color:midnightblue"> {{ message.messageDestinationList }} </div>
            <div style="color:darkgoldenrod"> {{ message.trigger }} </div>

            <message-modal v-for="params in stepParams" v-if="showDiv==true && currentDiv == message.id && params.id == column.id"
                           :messageParams="message"
                           :stepParams="params"
                           @updateMessageBlock="updateMessageBlock"
            />

<!--            <div v-if="showDiv===true && currentDiv===message.id" @mouseleave="showDiv=false" @mouseout="showDiv=false"/>-->
<!--            <div v-if="showDiv===true && currentDiv!==message.id || showDiv===false"> hide </div>-->

          </div>
        </div>

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

import messageModal from "../Modal/WorkflowModal/element/messageModal";

export default {
  name: "stepflow",
  mixins: [commonMixin],

  components: {ModalConfigStep, SimpleFlowchart, WorkflowSpace, draggable, PulseLoader, messageModal},

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
      campaignID: '',

      messages: [],
      stepList: [],
      show: false,

      showDiv: false,
      hideDiv: true,
      currentDiv: '',

      form: {},
      stepParams: Object,
    };
  },

  created() {
    this.getWorkflowFromURL();    //// get workflow name from url
    this.getAllSteps();           //// get all steps by workflow
    this.getMessagesDiv();        //// get all message divs
  },

  methods: {
    updateMessageBlock(trigger) {
      /// find index of message block --> based on trigger['messageDivId']
      let _index = this.messages.findIndex(message=>message.id === trigger['messageDivId']);
      this.messages[_index]['messageTemplate'] = trigger['messageTemplate'];
      this.messages[_index]['messageDestination'] = trigger['messageDestination'];
      this.messages[_index]['messageDestinationList'] = trigger['messageDestinationList'];
      this.messages[_index]['trigger'] = trigger['trigger'];
      this.showDiv=false;
      this.$forceUpdate();
    },

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
    },

    openStep: function (id) {
      this.currentStep = id;
      this.hideStep = true;
      this.hideWorkflow = false;
    },

    openDiv: function(id) {
      this.currentDiv = id;
      this.showDiv = !this.showDiv;
      this.hideDiv = !this.hideDiv;
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
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=step&task=getallsteps',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          wid: this.$data.id,
        }),
      }).then(response => {
        this.columns = response.data.data;
        this.stepParams = response.data.data;
        var steps = response.data.data; // get all steps
        this.stepList = steps;

        /// the reason is here
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
            var _userName = [];
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
            this.columns[_index]['outputStatus'] = answer.data.data.outputStatus;
            this.columns[_index]['campaignId'] = this.campaignID;
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
        this.campaignID = ((response.data.data)[0]).campaign_id;
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

    createMessageDiv: function(parent_id) {
      let data = {
        title: Math.random().toString(36).substring(2,10),       /// random title --> fix it later with hot-updating
        parent_type: 'step',
        parent_id: parent_id,
        element_type: 'message',
        workflow_id: this.$data.id,   /// using mixin to get workflow id
      };

      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=common&task=createmessagebloc',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: data,
          campaignId: this.campaignID,
        })
      }).then(response => {
        let parent_id = response.data.data.parent_id;
        this.messages.push({
          parent_id: parent_id,
          id: response.data.data.id,
          triggerId: response.data.data.trigger,
          title: data.title,            /// random title --> fix it later with hot-updating
        });

      }).catch(error => {
        console.log(error);
      })
    },

    deleteMessageDiv: function(id) {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=common&task=deletemessagebloc',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          id: id
        })
      }).then(response => {
        this.messages = this.messages.filter((message) => {
          return message.id !== id; // remove step
        })
      }).catch(error => {
        console.log(error);
      })
    },

    getMessagesDiv: function() {
        let data = {
          parent_type: 'step',
          element_type: 'message',
          workflow_id: this.$data.id,
        }

        axios({
          method: 'post',
          url: 'index.php?option=com_emundus_workflow&controller=common&task=getmessageblocbyparenttype',
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            data: data
          })
        }).then(response => {
          this.messages = response.data.data;
          let mess = response.data.data;

          mess.forEach(element => {
            axios({
              method: 'post',
              url: 'index.php?option=com_emundus_workflow&controller=common&task=getmessageblocbyid',
              headers: {
                "Content-Type": "application/x-www-form-urlencoded"
              },
              data: qs.stringify({
                data : {id: element.id, mode: 'email',}
              })
            }).then(answer => {
              let _users = [];

              let _index = this.messages.findIndex((elt) => elt.id === element.id);
              this.messages[_index]['messageTemplate'] = answer.data.data.parsedParams.emailSelectedName;
              this.messages[_index]['messageDestination'] = answer.data.data.parsedParams.destinationSelectedName;

              let userList = answer.data.data.parsedParams.userSelectedName;

              if(userList !== "") {
                userList.forEach(user => {
                  _users.push(user.name);
                })
                this.messages[_index]['messageDestinationList'] = _users.toString();
              }
              else {
                this.messages[_index]['messageDestinationList'] = "";
              }

              this.messages[_index]['trigger'] = answer.data.data.parsedParams.triggerSelected;

              this.$forceUpdate();
            }).catch(logs => {
              console.log(logs);
            })
          })
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
  overflow-y: scroll;
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

.message-block {
  box-shadow: 0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06);
  padding-bottom: 1.25rem;
  padding-top: .75rem;
  padding-left: .75rem;
  padding-right: .75rem;
  margin-top: .75rem;
  border-width: 1px;
  border-radius: .25rem;
  --border-opacity: 1;
  border-color: rgba(255,255,255,var(--border-opacity));
  overflow-y: scroll;
  border-color: red;
}

.remove-message {
  font-size: xx-small;
  right: 85vh !important;
  top: 45vh !important;
  position: fixed !important;
}
</style>
