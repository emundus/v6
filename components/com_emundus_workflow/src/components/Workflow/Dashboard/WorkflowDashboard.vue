<template>
  <div id="workflow-dashboard">
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap/dist/css/bootstrap.min.css" />
    <link type="text/css" rel="stylesheet" href="//unpkg.com/bootstrap-vue@latest/dist/bootstrap-vue.min.css" />

    <workflow-creator-menu @updateTable="updateTable" @gotoStepFlow="redirectStepFlow" ref="creatormenu"/>
    <workflow-info-table ref="infotable" @gotoStepFlow="redirectStepFlow" @updateCampaign="updateCampaign"/>
<!--    <table class="styled-table">-->
<!--      <thead>-->
<!--      <tr>-->
<!--        <th v-for="(theader,index) in this.$data.table_header" :key="index">-->
<!--          {{ theader }}-->
<!--        </th>-->
<!--      </tr>-->
<!--      </thead>-->

<!--      <tbody>-->
<!--      <tr v-for="(workflow,index) in this.$props.workflows" :key="workflow.id">-->
<!--        <th>{{ workflow.id }}</th>-->
<!--        <th>{{ workflow.workflow_name }}</th>-->
<!--        <th> {{ workflow.label }} </th>-->
<!--        <th>{{ workflow.name }}</th>-->
<!--        <th>{{ workflow.created_at }}</th>-->
<!--        <th>{{ workflow.updated_at }}</th>-->
<!--        <th>-->
<!--          <button @click="changeToWorkflowSpace(workflow.id)" class="edit-button">OUVRIR</button>-->
<!--          <button @click="alertDeleteDisplay(workflow.id)" class="delete-button">SUPPRIMER</button>-->
<!--          <button @click="alertDuplicateDisplay(workflow.id)" class="duplicate-button">DUPLIQUER</button>-->
<!--        </th>-->
<!--      </tr>-->
<!--      </tbody>-->
<!--    </table>-->
<!--    <div class="alert-count">-->
<!--      <p> {{ this.workflow_message }} </p>-->
<!--    </div>-->
  </div>

</template>

<script>
import axios from 'axios';
import { DateTime } from 'vue-datetime';

import Swal from 'sweetalert2';

import WorkflowCreatorMenu from "./elements/WorkflowCreatorMenu";       // import menu creator menu --> 1 textbox + 1 dropdown menu
import WorkflowInfoTable from "./elements/WorkflowInfoTable";           // import workflow info table --> to view all informations about workflow

let now = new Date();

const qs = require('qs');

export default {
  name: "workflowDashboard",

  components: {
    WorkflowCreatorMenu,
    WorkflowInfoTable,
  },

  data: function() {
    return { }
  },

  // using props to share data between components
  props: { },

  created() {},

  methods: {
    updateTable: function(signal) {
      if(signal === 1) {
        this.$refs.infotable.getAllWorkflow();
      }
    },

    updateCampaign: function(isUpdate) {
      if(isUpdate == true) {
        this.$refs.creatormenu.getAllAvailableCampaigns();
      }
    },

    redirectJRoute(link) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_workflow&controller=settings&task=redirectjroute",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
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

    redirectStepFlow(id) {
      this.redirectJRoute('index.php?option=com_emundus_workflow&view=item&layout=add&id=' + id);
    },

    //duplicate workflow from id --> duplicate all existing items of the last workflow
    ///api 1 --> get workflow_name / campaign_id of last workflow
    ///api 2 --> get all items of last workflow
    ///api 3 --> create new workflow with api 1 + api 2

    duplicateWorkflow: async function(id) {
      //api 1 -- get workflow_name, campaign_id of last workflow
      let _response = await axios.get('index.php?option=com_emundus_workflow&controller=workflow&task=getworkflowbyid', { params: {wid:id} });
      var oldworkflow = ((_response.data.data)[0]);

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

.alert-count {
  border-radius: 4px;
  border: 1px solid #c80101;
  text-align: center;
  width: 100%;
  font-size: large;
  color: #0A246A;
}
</style>
