<template>
  <div>
    <b-jumbotron header="EMundus Process Workflow" lead="EMundus Process Workflow">

      <div class="workflow-info">

        <b-form @submit="createworkflow">

          <label> Workflow name</label>
            <input v-model="name" placeholder="workflow name">

          <label> Associated campaign </label>
            <select v-model="selectedCampaign">
              <option v-for="campaign in this.$props.campaigns" :value="campaign.id"> {{ campaign.label }} </option>
            </select>

        </b-form>
        <b-button type="submit" variant="success" @click="createworkflow">Create new workflow</b-button>
      </div>

    </b-jumbotron>
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
  },

  created() {
    this.getAllCampaigns();
  },

  methods: {
    getAllCampaigns: function() {
      //get all items
      axios.get("index.php?option=com_emundus_workflow&controller=workflow&task=getassociatedcampaigns")
          .then(response=>{
            this.campaigns = response.data.data;
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
        console.log(this.workflow);
        this.changeToWorkflowSpace(this.workflow);   //redirect to workflow space
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

<style scoped>

</style>