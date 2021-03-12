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
          <th v-for="(theader,index) in this.$data.table_header" :key="index">
            {{ theader }}
          </th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="(workflow,index) in this.$props.workflows" :key="workflow.id">
          <th>{{ index }}</th>
          <th>{{ workflow.id }}</th>
          <th>{{ workflow.workflow_name }}</th>
          <th> {{ workflow.label }} </th>
          <th>{{ workflow.name }}</th>
          <th>{{ workflow.created_at }}</th>
          <th>{{ workflow.updated_at }}</th>
          <th>
            <button @click="changeToWorkflowSpace(workflow.id)" class="edit-button">OUVRIR</button>
            <button @click="alertDeleteDisplay(workflow.id)" class="delete-button">SUPPRIMER</button>
            <button @click="alertDuplicateDisplay(workflow.id)" class="duplicate-button">DUPLIQUER</button>
          </th>
        </tr>
      </tbody>
    </table>
  </div>

</template>

<script>
import axios from 'axios';
import { DateTime } from 'vue-datetime';
import { DateTime as LuxonDateTime, Settings } from 'luxon';
import swal from 'sweetalert';

import Swal from 'sweetalert2'

// CommonJS

let now = new Date();

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
      table_header: ['No.ligne', 'Workflow ID', 'Nom du workflow', 'Campagne Associeé', 'Dernier Mis-a-jour par', 'Créé à', 'Mis-a-jour ', 'Action'],
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
          }).catch(error => {
            console.log(error);
      })
    },

    getAllWorkflow: function() {
      axios.get("index.php?option=com_emundus_workflow&controller=workflow&task=getallworkflows")
          .then(response=>{
            this.workflows = response.data.data;
          }).catch(error => {
            console.log(error);
      })
    },

    // create workflow with campaign
    createworkflow: function() {
      axios({
        method: "post",
        url: "index.php?option=com_emundus_workflow&controller=workflow&task=getcampaignbyid",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data: this.$data.selectedCampaign,
        }),
      }).then(answer => {
        // console.log((answer.data.data)[0]);
        var workflow = {
          campaign_id :this.$data.selectedCampaign,
          workflow_name: this.$data.name || "Workflow de " + ((answer.data.data)[0]).label,
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
            //redirect to workflow space
            this.changeToWorkflowSpace(response.data.data);
          }).catch(error => {
            console.log(error);
          })
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
        this.getAllWorkflow();
      }).catch(error => {
        console.log(error);
      })
    },

    //duplicate workflow from id --> duplicate all existing items of the last workflow
    ///api 1 --> get workflow_name / campaign_id of last workflow
    ///api 2 --> get all items of last workflow
    ///api 3 --> create new workflow with api 1 + api 2


    duplicateWorkflow: async function(id) {
      //api 1 -- get workflow_name, campaign_id of last workflow
      let _response = await axios.get('index.php?option=com_emundus_workflow&controller=workflow&task=getworkflowbyid', { params: {wid:id} });
      var oldworkflow = ((_response.data.data)[0]);
      let now = new Date();

      var newworkflow = {
        campaign_id :oldworkflow.campaign_id,
        workflow_name: oldworkflow.workflow_name + "copy",
      }

      //get all elements (items + links) of the last workflow
      let rawItems = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getallitemsbyworkflow', {params: {data: id}}); //get all items
      let rawLinks = await axios.get('index.php?option=com_emundus_workflow&controller=item&task=getalllinks', {params: {data: id}}); //get all links

      var items = rawItems.data.data;    //items : Array
      var links = rawLinks.data.data;    //links: Array

      //api 2 -- create cloned workflow :: empty items
      axios({
        method: 'post',
        url: 'index.php?option=com_emundus_workflow&controller=workflow&task=createworkflow&suboption=clone&oldworkflow=' + oldworkflow.id,
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          data:newworkflow
        })
      }).then(response => {
        //create new item
        items.forEach(its => {
          var _newitems = {
            item_id: its.item_id,
            item_label: its.item_label,
            item_name: its.item_name,
            style: its.style,
            workflow_id: response.data.data,
            axisX: its.axisX,
            axisY: its.axisY,
          }
          axios({
            method: 'post',
            url: "index.php?option=com_emundus_workflow&controller=item&task=createitem&workflowid=" + response.data.data + "&itemid=" + _newitems.item_id,
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            data: qs.stringify({
              data: _newitems
            })
          }).then(answer => {})
          this.getAllWorkflow();
        })
      }).catch(error => {console.log(error);})
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


    alertDuplicateDisplay: function(id) {
      Swal.fire({
        icon: 'success',
        title: 'Congrat',
        html: 'Le workflow est dupliqué <h2 style="color:red">SAUF LES LIAISONS!',
        footer: '<a href>EMundus SAS</a>',
        timer: 2000,
        showConfirmButton:false,
      })
      this.duplicateWorkflow(id);
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
        window.location.href =  window.location.pathname + response.data.data;
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
    background-color: #06ba00;
    border-color: #06ba00;
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
    background-color: red;
    border-color: red;
    color: #fff;
    display: inline-block;
    text-align: center;
    vertical-align: center;
    user-select: none;
    border-radius: .25rem !important;
    margin: 5px;
    padding: 4px 20px;
  }

  .duplicate-button {
    top: auto;
    background-color: orange;
    border-color: orange;
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

  .swal2-styled.swal2-confirm {
    border-radius: 5px !important;
  }

  .swal2-styled.swal2-confirm:hover {
    box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
  }

  .swal2-styled.swal2-cancel {
    border-radius: 5px !important;
  }

  .swal2-styled.swal2-cancel:hover {
    box-shadow: 0 12px 16px 0 rgba(0,0,0,0.24), 0 17px 50px 0 rgba(0,0,0,0.19);
  }

</style>