<template>
    <div id="workflow-creator-menu">

      <div style="text-align: center">
        <b-alert show variant="success" style="background-color: #fff3cd; border-color: #ffeeba; padding: 3.75rem; align-items: center" show dismissible>
          <h1>EMundus Workflow Demo</h1>
        </b-alert>
      </div>

      <b-form-group label-cols="4" label-cols-lg="2" label-size="lg" :label="WorkflowCreatorMenu_Title.label" label-for="input-lg">
        <b-form-input v-model="name" :placeholder="WorkflowCreatorMenu_PlaceHolder.label" size="sm"/>
      </b-form-group>

      <b-form-group label-cols="4" label-cols-lg="2" label-size="lg" :label="WorkflowCreatorMenu_Title.campaigns" label-for="input-lg">
        <b-form-select v-model="selectedCampaign" class="form-control-select" id="campaign">
          <b-form-select-option disabled selected aria-placeholder="test">{{ WorkflowCreatorMenu_PlaceHolder.campaigns }}</b-form-select-option>
          <option v-for="campaign in this.availableCampaign" :value="campaign.id"> {{ campaign.label }} </option>
        </b-form-select>
      </b-form-group>

      <b-button type="submit" variant="success" @click="createWorkflow">{{ WorkflowCreatorMenu_Button.add_button }}</b-button>
    </div>
</template>

<script>
import axios from 'axios';
import $ from 'jquery';

const qs = require('qs');

export default {
  name: "WorkflowCreatorMenu",

  props: {
    workflowLabel: String,
    associatedCampaign: Number,
  },

  data: function() {
    return {
      WorkflowCreatorMenu_PlaceHolder: {
        label: Joomla.JText._("COM_EMUNDUS_WORKFLOW_WORKFLOW_CREATOR_MENU_WORKFLOW_NAME_PLACEHOLDER"),
        campaigns: Joomla.JText._("COM_EMUNDUS_WORKFLOW_WORKFLOW_CREATOR_MENU_AVAILABLE_CAMPAIGNS_PLACEHOLDER"),
      },

      WorkflowCreatorMenu_Title: {
        label: Joomla.JText._("COM_EMUNDUS_WORKFLOW_WORKFLOW_CREATOR_MENU_TITLE_WORKFLOW_NAME"),
        campaigns: Joomla.JText._("COM_EMUNDUS_WORKFLOW_WORKFLOW_CREATOR_MENU_TITLE_ASSOCIATED_CAMPAIGN"),
      },

      WorkflowCreatorMenu_Button: {
        add_button: Joomla.JText._("COM_EMUNDUS_WORKFLOW_COMMON_ADD_BUTTON_TITLE"),
      },

      availableCampaign: [],
      workflowMessage: '',
      form: {
        workflowName: '',
      },
      selectedCampaign: 0,
      name: '',
    }
  },

  created() {
    this.getAllAvailableCampaigns();  // get all available campaigns
  },

  methods: {
    getAllAvailableCampaigns: function() {
      axios.get("index.php?option=com_emundus_workflow&controller=workflow&task=getallavailablecampaigns")
          .then(response=>{this.availableCampaign = response.data.data;})
          .catch(error => {console.log(error);})
    },

    createWorkflow: function() {
      let workflow = {
        campaign_id :this.selectedCampaign,
        workflow_name: this.name || "Workflow de" + $( "#campaign option:selected" ).text(),     // using jquery to get the campaign name
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
        //redirect to step flow area

        this.availableCampaign = this.availableCampaign.filter((elt) => {
          return elt.id !== workflow.campaign_id;
        });

        this.$emit('gotoStepFlow', response.data.data);
        let signal = 1;
        this.$emit('updateTable', signal);

      }).catch(error => {
        console.log(error);
      })
    }
  }
}
</script>

<style scoped>

</style>