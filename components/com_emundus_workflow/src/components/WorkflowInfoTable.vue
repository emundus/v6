<template>
  <div id="workflow-info-table">
    <table class="styled-table" id="infotable">
      <thead>
      <tr>
        <th v-for="(theader,index) in this.$data.table_header" :key="index">
          {{ theader }}
        </th>
      </tr>
      </thead>

      <tbody>
      <tr v-for="(workflow,index) in this.workflows" :key="workflow.id" :id="'row_'+ workflow.id">
          <th>{{ workflow.id }}</th>
          <th>{{ workflow.workflow_name }}</th>
          <th> {{ workflow.label }} </th>
          <th>{{ workflow.name }}</th>
          <th>{{ workflow.created_at }}</th>
          <th>{{ workflow.updated_at }}</th>
          <th>
            <button @click="openWorkflowSpace(workflow.id)" class="edit-button">OUVRIR</button>
            <button @click="alertDeleteDisplay(workflow.id)" class="delete-button">SUPPRIMER</button>
  <!--          <button @click="alertDuplicateDisplay(workflow.id)" class="duplicate-button">DUPLIQUER</button>-->
          </th>
      </tr>
      </tbody>
    </table>
    <div class="alert-count">
      <p> {{ this.workflowMessage }} </p>
    </div>

  </div>
</template>

<script>

import axios from 'axios';
import Swal from "sweetalert2";
const qs = require('qs');
import $ from 'jquery';

export default {
  name: "WorkflowInfoTable",

  props: {
    receiver: Number,
  },

  data: function() {
    return {
      workflows: [],      /// all workflows ==> 1 array
      workflowMessage: '',
      table_header: ['Workflow ID', 'Nom du workflow', 'Campagne Associeé', 'Dernier Mis-a-jour par', 'Créé à', 'Mis-a-jour ', 'Action'],     // use JText later
    }
  },

  created() {
    this.getAllWorkflow();
  },

  methods: {
    getAllWorkflow: function() {
      axios.get("index.php?option=com_emundus_workflow&controller=workflow&task=getallworkflows")
          .then(response=>{
            this.workflows = response.data.data;
            if(parseInt(response.data.count) == 0) {
              this.workflowMessage = "Aucun workflow trouvé";
            }
            else {
              this.workflowMessage = "Il y a " + response.data.count + " workflow(s)";
            }
          }).catch(error => {
        console.log(error);
      })
    },

    //delete workflow
    deleteWorkflow: function(wid) {
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=workflow&task=deleteworkflow',
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({wid})
      }).then(response => {
        this.workflows = this.workflows.filter((elt) => {
          return elt.id !== wid;
        })

        let _rows = $('#infotable tbody tr').length - 1;
        if(_rows == 0) {
          this.workflowMessage = "Aucun workflow trouvé";
        } else {
          this.workflowMessage = "Il y a " + _rows + " workflow(s)";
        }

        /// call to getAvailableCampaign
        let isUpdate = true;
        this.$emit('updateCampaign', isUpdate);
      }).catch(error => {
        console.log(error);
      })
    },

    alertDeleteDisplay: function(wid) {
      Swal.fire({
        title: 'Supprime le workflow',
        text: "Action irréversible",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        confirmButtonText: '<i class="far fa-thumbs-up"></i> Oui, c\'est sûr',
        cancelButtonText: 'Non, garder ce workflow',
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire('Merci', 'Le workflow est supprimé', 'success');
          this.deleteWorkflow(wid);
        } else if (result.isDismissed) {
          Swal.fire('Merci', 'Le workflow est gardé', 'success');
        }
      })
    },

    openWorkflowSpace: function(id) {
      this.$emit('gotoStepFlow', id);
    }
  }
}
</script>

<style scoped>

</style>