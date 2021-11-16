<template>
  <div id="workflowBuilder">
    <h1> Workflow Builder </h1>

    <table class="fabrik_groupheading">
      <thead>
        <tr>
          <th>Workflow</th>
          <th>Actions</th>
        </tr>
      </thead>

      <tbody>
      <tr v-for="(w,i) in this.workflows" :key="w.id" :id="w.id">
        <th> {{ w.label }} </th>
        <th>
          <button @click="openWorkflow(w.id)" class="btn-success">Open</button>
          <button @click="removeWorkflow(w.id)" class="btn-danger">Remove</button>
        </th>
      </tr>
      </tbody>

    </table>
  </div>
</template>

<script>
import stepsBuilder from "./stepsBuilder";

import axios from 'axios';
const qs = require('qs');

export default {
  name: 'workflowBuilder',

  components: {
    stepsBuilder
  },

  data: function() {
    return {
      workflows: [],
    }
  },

  methods: {
    getAllWorkflows: function() {
      axios.get("index.php?option=com_emundus_workflow&controller=workflows&task=getallworkflows").then(response=>{
        this.workflows = response.data.data;
        console.log(this.workflows);
      }).catch(error => {
        console.log(error);
      })
    },

    openWorkflow: function(id) {
      window.location.href = 'index.php?option=com_emundus_workflow&view=steps&layout=add&wid=' + id;
    },

    removeWorkflow: function(id) {

    },
  },

  created() {
    this.getAllWorkflows();
  }
}
</script>

<style>
#workflowBuilder {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
