<template>
  <select v-model="campaignSelected">
    <option v-for="campaign in campaigns" :value="campaign.id">{{campaign.label}}</option>
  </select>
  <ul>
    <li v-for="message in messages">{{message.message}}</li>
  </ul>
</template>

<script>
import axios from "axios";

const qs = require("qs");

export default {
  name: "Messages",
  props: {},
  components: {},
  data() {
    return {
      messages: [],
      campaigns: [],
      campaignSelected: 0,
    };
  },

  methods: {
    getCampaignsByUser(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_messenger&controller=messages&task=getcampaignsbyuser",
      }).then(response => {
        this.campaigns = response.data;
        this.campaignSelected = this.campaigns[0].id;
      });
    },

    getMessagesByCampaign(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus_messenger&controller=messages&task=getmessagesbycampaign",
        params: {
          cid: this.campaignSelected,
        },
        paramsSerializer: params => {
           return qs.stringify(params);
        }
      }).then(response => {
        this.messages = response.data.data;
      });
    }
  },

  created(){
    this.getCampaignsByUser();
  },

  watch: {
    campaignSelected: function(){
      this.getMessagesByCampaign();
    }
  }
}
</script>

<style scoped>

</style>
