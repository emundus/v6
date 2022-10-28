<template>
  <div class="container-evaluation">
    <p class="heading">{{chooseCampaign}} :</p>
    <div class="heading-block mb-1">
      <select class="dropdown-toggle" style="width: 90%" v-model="cid">
        <option :value="null"></option>
        <option v-for="(campaign, index) in campaigns" :key="index" :value="campaign.id">
          {{campaign.label}}
        </option>
      </select>
      <a v-if="cid != null"
         :href="'index.php?option=com_emundus&view=campaign&layout=add&cid=' + cid"
         class="modifier-la-campagne">
        <button class="w-inline-block edit-icon">
          <em class="fas fa-edit"></em>
        </button>
      </a>
    </div>
    <div v-if="cid == null" style="display: flex;" class="required mt-1">
      <em class="fas fa-exclamation-circle icon-warning-margin"></em>
      <p>{{chooseCampaignWarning}}</p>
    </div>
    <div v-if="cid != null && profile == null" style="display: flex;" class="required mt-1">
      <em class="fas fa-exclamation-circle icon-warning-margin"></em>
      <p>{{NoFormAffectedToThisCampaign}}</p>
    </div>
    <div v-if="cid != null && profile !== null">
      <div>
        <add-formulaire
                :profileId="profile"
                :key="formReload"
                :visibility="cid"
        ></add-formulaire>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import AddFormulaire from "./addFormulaire";

export default {
  name: "addEvalVisi",
  components: {AddFormulaire},
  props: {
    funnelCategorie: Object,
    prog: Number,
  },

  data() {
    return {
      formReload: 0,
      cid: null,
      profile: null,
      profiles: [],
      campaigns: [],
      EnableVisibility: this.translate("COM_EMUNDUS_ONBOARD_VISIBILITY_ENABLE"),
      ChooseForm: this.translate("COM_EMUNDUS_ONBOARD_CHOOSE_FORM"),
      chooseCampaignWarning: this.translate("COM_EMUNDUS_ONBOARD_CHOOSE_CAMPAIGN_WARNING"),
      chooseCampaign: this.translate("COM_EMUNDUS_ONBOARD_CHOOSE_CAMPAIGN"),
      NoFormAffectedToThisCampaign: this.translate("COM_EMUNDUS_ONBOARD_NO_AFFECTED_FORM"),
    };
  },

  methods: {
    getFormByCampaign() {
      axios.get(
              'index.php?option=com_emundus&controller=campaign&task=getcampaignbyid&id=' + this.cid
      ).then(response => {
        this.profile = response.data.data.campaign.profile_id;
        if(this.profile != null) {
          this.formReload += 1;
        }
      });
    },

    getCampaignsByProgram(){
      axios.get("index.php?option=com_emundus&controller=campaign&task=getcampaignsbyprogram&pid=" + this.prog)
              .then(response => {
                this.campaigns = response.data.data;
              });
    },
  },

  watch: {
    cid: function (value) {
      if(value){
        this.getFormByCampaign();
      }
    },
  },

  created() {
    this.getCampaignsByProgram();
  }
};
</script>
<style>
  .label-toggle{
    margin: 0 0 0 1em;
  }
  .icon-warning-margin{
    margin-top: 2px;
    margin-right: 5px;
  }
</style>
