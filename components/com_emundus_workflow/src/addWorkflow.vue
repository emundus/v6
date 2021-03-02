<template>
  <div>
    <h1>Emundus Workflow</h1>
      <div class="workflow-info">

        <b-form @submit="createworkflow">

          <label> Workflow name</label>
            <input v-model="name" placeholder="workflow name">

          <label> Associated campaign </label>
          <p>
            <select v-model="selectedCampaign">
              <option v-for="campaign in this.$props.campaigns" :value="campaign.id"> {{ campaign.label }} </option>
            </select>
          </p>
        </b-form>
        <b-button type="submit" variant="success" @click="createworkflow">Create new workflow</b-button>
      </div>

    <table class="styled-table">
      <thead>
        <tr>
          <th>Index</th>
          <th>Workflow ID</th>
          <th>Workflow name</th>
          <th>Action</th>
          <th>Associated campaign</th>
          <th>Last accessed by</th>
          <th>Created at</th>
          <th>Updated at</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(workflow,index) in this.$props.workflows" :key="workflow.id">
          <th>{{ index }}</th>
          <th>{{ workflow.id }}</th>
          <th>{{ workflow.workflow_name }}</th>
          <th>
            <button @click="changeToWorkflowSpace(workflow.id)" class="edit-button">EDIT</button>
            <button class="delete-button">DELETE</button>
          </th>
          <th>{{ workflow.campaign_id }}</th>
          <th>{{ workflow.user_id }}</th>
          <th>{{ workflow.created_at }}</th>
          <th>{{ workflow.updated_at }}</th>
        </tr>
      </tbody>
    </table>
  </div>

</template>

<script>
import axios from 'axios';
import { DateTime } from 'vue-datetime';
import { DateTime as LuxonDateTime } from 'luxon';

const qs = require('qs');

export default {
  name: "addWorkflow",

  data: function() {
    return {
      form: {
        workflow_name: '',   //name of workflow
      },
      name: '',
      selectedCampaign: '',
      workflow_id: 0,
    }
  },

  // using props to share data between components
  props: {
    workflow: Object,
    campaigns: Array,
    workflow_id: Number,
    workflows: Array,
  },

  created() {
    this.getAllCampaigns();
    this.getAllWorkflow();
  },

  methods: {
    getAllCampaigns: function() {
      axios.get("index.php?option=com_emundus_workflow&controller=workflow&task=getassociatedcampaigns")
          .then(response=>{
            this.campaigns = response.data.data;
          })
    },

    getAllWorkflow: function() {
      axios.get("index.php?option=com_emundus_workflow&controller=workflow&task=getallworkflows")
          .then(response=>{
            this.workflows = response.data.data;
          })
    },

    // create workflow with campaign
    createworkflow: function() {
      let now = new Date();
      var workflow = {
        campaign_id :this.$data.selectedCampaign,
        workflow_name: this.$data.name,
        user_id: 95,
        created_at: Date.now(),
        updated_at: Date.now(),
      }
      axios({
        method: "post",
        url: "index.php?option=com_emundus_workflow&controller=workflow&task=createworkflow",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: workflow
        }),
      }).then(response => {
        this.workflow = response.data.data;
        this.getAllWorkflow();
        //this.changeToWorkflowSpace(this.workflow);   //redirect to workflow space
      }).catch(error => {
        console.log(error);
      })
    },

    redirectJRoute(link) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_workflow&controller=settings&task=redirectjroute",
        params: {
          link: link,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        window.location.href = window.location.pathname + response.data.data;

      });
    },

    changeToWorkflowSpace(id) {
      this.redirectJRoute('index.php?option=com_emundus_workflow&view=item&layout=add&id=' + id);
    }
  },
}
</script>

<style>
  .styled-table {
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 0.9em;
    font-family: sans-serif;
    min-width: 400px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
  }

  .styled-table thead tr {
    background-color: #009879;
    color: #ffffff;
    text-align: left;
  }

  .styled-table th {
    background: none;
  }

  .styled-table td {
    padding: 12px 15px;
  }

  .styled-table tbody tr {
    border-bottom: 1px solid #dddddd;
  }

  .styled-table tbody tr:nth-of-type(even) {
    background-color: #f3f3f3;
  }

  .edit-button {
    top: auto;
    background-color: #008cba;
    border-color: #008cba;
    color: #fff;
    display: inline-block;
    text-align: center;
    vertical-align: center;
    user-select: none;
    border-radius: .25rem;
    margin: 5px;
    padding: 4px 20px;
  }

  .edit-button:hover {
    box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
  }

  .delete-button {
    top: auto;
    background-color: #dc3545;
    border-color: #dc3545;
    color: #fff;
    display: inline-block;
    text-align: center;
    vertical-align: center;
    user-select: none;
    border-radius: .25rem;
    margin: 5px;
    padding: 4px 20px;
  }

  .delete-button:hover {
    box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
  }
</style>