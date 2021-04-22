<template>
  <div id="stepflow">
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

    <b-button @click="createStep()" v-if="!hideStep" variant="success">Creer nouvelle etape</b-button>
    <div class="min-h-screen flex overflow-x-scroll py-12">
      <div v-for="column in columns" :key="column.title" class="bg-gray-100 rounded-lg px-3 py-3 column-width rounded mr-4" :id="'step_' + column.id" v-on:dblclick="openStep(column.id)" v-if="!hideStep">
        <div contenteditable="true" class="editable-step-label" :id="'step_label_' + column.id" v-on:keyup.enter="setStepLabel(column.id)" style="background: #a8bb4a">{{ column.title }}</div>
        <div style="color:red">{{ column.stateIn }}</div>
        <div style="color:blueviolet">{{ column.stateOut }}</div>
        <modal-config-step :ID="column.id" :element="column" @updateState="updateStatus" @deleteStep="deleteStep(column.id)"/>
        <!--        <div>{{ column.stateIn }}</div>-->
        <!--        <div>{{ column.stateOut }}</div>-->

        <b-button @click="deleteStep(column.id)" variant="danger">Annuler etape</b-button>
        <b-button @click="configStep(column.id)" variant="warning" style="margin-left: 20px">Configurer</b-button>
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

import { commonMixin } from '../common-mixin'; /// using mixin in this case

export default {
  name: "stepflow",
  mixins: [commonMixin],

  components: {ModalConfigStep, SimpleFlowchart, WorkflowSpace},

  props: {},

  data() {
    return {
      columns: [],
      currentStep: '',
      hideStep: false,
      stateIn: '',
      stateOut: '',
    };
  },

  created() {
    console.log(this.$data.id);
    this.getAllSteps(); //// get all steps by workflow
  },

  methods: {
    updateStatus(result) {
      const _id = (element) => element.id == result['id'];
      var _index = this.columns.findIndex(_id);
      this.columns[_index]['stateIn'] = result['input'];
      this.columns[_index]['stateOut'] = result['output'];

      this.$forceUpdate();

      //// forceupdate --> call api to update status in database --> checkin if status (after) and status (before) are the same --> do nothing /// otherwise, call to axios

      if(this.form.inputStatus !== null && this.form.outputStatus !== null) {
        axios({
          method: 'post',
          url: 'index.php?option=com_emundus_workflow&controller=step&task=updateparams',
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            data: {
              id: this.element.id,
              wid: this.getWorkflowIdFromURL(),
              params: this.form,
            }
          })
        }).then(response => {
          Swal.fire({
            icon: 'success',
            title: 'Congrat',
            text: 'Les parametres sont sauvegardés',
            footer: '<a href>EMundus SAS</a>',
            confirmButtonColor: '#28a745',
          }).then(result => {
            if(result.isConfirmed) {
              this.exitModal();
            }
            // this.inStatusSelected = $( "#instatus-selected option:selected" ).text();
          })
        }).catch(error => {
          console.log(error);
        })
      }
      else {
        Swal.fire({
          icon: 'error',
          title: 'Erreur',
          html: 'Le statut d\'entré et le statut de sortie doivent être configuré',
          timer: 1500,
          showConfirmButton:false,
        })
      }
    },

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
        this.columns.push({
          id: response.data.data.step_id,
          title: 'Etape # anonyme ' + response.data.data.step_id,       // default name of step
        })

        setTimeout(() => { this.$modal.show('stepModal' + response.data.data.step_id) }, 500);
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
        this.columns = this.columns.filter((step) => {
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
          //// call to axios to get status of this step
          var sid = step.id;
          axios({
            method: 'get',
            url: 'index.php?option=com_emundus_workflow&controller=step&task=getcurrentparams',
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            params: { sid },
            paramsSerializer: params => {
              return qs.stringify(params);
            }
          }).then(answer => {
            const _id = (element) => element.id == sid;
            var _index = this.columns.findIndex(_id);


            // this.columns[_index]['stateIn'] = answer.data.data.inputStatusNames;

            var _temp = answer.data.data.inputStatusNames;
            var _stateIn = [];

            _temp.forEach(elt => _stateIn.push(elt.value));
            this.columns[_index]['stateIn'] = _stateIn.toString();

            this.columns[_index]['stateOut'] = (answer.data.data.outputStatusNames)[0].value;
            this.$forceUpdate();
          });

          this.columns.push({
            id: step.id,
            title: 'Etape # anonyme ' + step.id,
          })
          // this.stateIn = answer.data.data.inputStatus;
          // this.stateOut = answer.data.data.outputStatus;
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
