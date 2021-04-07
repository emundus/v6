<template>
  <div id="stepflow">
    <button @click="createStep()">Creer nouvelle etape</button>
    <div class="min-h-screen flex overflow-x-scroll py-12">
      <div v-for="column in columns" :key="column.title" class="bg-gray-100 rounded-lg px-3 py-3 column-width rounded mr-4" :id="'step_' + column.id">
        <p>{{ column.title }}</p>
        <button @click="deleteStep(column.id)">Annuler etape</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
const qs = require('qs');

export default {
  name: "stepflow",

  components: {},

  props: {
    ID: Number,             //id of step flow
  },

  data() {
    return {
      columns: []
    };
  },

  created() {
    this.getAllSteps(); //// get all steps by workflow
  },

  methods: {
    createStep: function() {
      var _data = {
        workflow_id : this.getWorkflowIdFromURL(),
        step_label: "Candidature cycle 2",
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
          console.log(response);
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
        console.log(response);
      })
    },

    // get the workflow id from url
    getWorkflowIdFromURL: function () {
      return window.location.href.split('id=')[1];
    },
  }
};
</script>

<style>
.column-width {
  min-width: 450px;
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
  background-color: #c6e1dc;
  background-image: radial-gradient(circle, black 1px, rgba(0, 0, 0, 0) 1px);
  background-size: 3em 3em;
}

.py-12 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

.overflow-x-scroll {
  overflow-x: scroll;
}

.min-h-screen {
  min-height: 100vh;
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
</style>
