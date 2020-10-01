<template>
  <!-- modalC -->
  <span :id="'modalAffectCampaign'">
    <modal
      :name="'modalAffectCampaign'"
      height="auto"
      transition="nice-modal-fade"
      :min-width="200"
      :min-height="200"
      :delay="100"
      :adaptive="true"
      :clickToClose="false"
      @closed="beforeClose"
      @before-open="beforeOpen"
    >
      <div class="modalC-content">
        <div class="update-field-header">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalAffectCampaign')">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{affectCampaigns}}
          </h2>
        </div>
        <p v-if="campaigns.length === 0" class="mt-1 mb-1">{{campaignsEmpty}}</p>
        <div class="wrap">
          <div class="search">
            <input type="text" class="searchTerm" :placeholder="Search" v-model="searchTerm" @keyup="searchCampaignByTerm">
            <button type="button" class="searchButton" @click="searchCampaignByTerm">
              <em class="fas fa-search"></em>
            </button>
          </div>
        </div>
        <div v-for="(campaign, index) in campaigns" :key="index" class="user-item">
            <input type="checkbox" class="form-check-input bigbox" v-model="affectedCampaigns[campaign.id]">
            <div class="ml-10px">
                <p>{{campaign.label}}</p>
            </div>
        </div>
      </div>
      <div class="col-md-12 mb-1">
        <a
          class="bouton-sauvergarder-et-continuer-3"
          @click.prevent="affectToForm"
        >{{ Continuer }}</a>
        <a
          class="bouton-sauvergarder-et-continuer-3"
          style="margin-right: 20px"
          @click.prevent="goAddCampaign"
        >{{addCampaign}}</a>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
  name: "modalAffectCampaign",
  props: { prid: Number },
  data() {
    return {
      campaigns: [],
      affectedCampaigns: [],
      searchTerm: '',
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      affectCampaigns: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_AFFECTCAMPAIGNS"),
      campaignsEmpty: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_CAMPAIGNSEMPTY"),
      addCampaign: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN"),
    };
  },
  methods: {
    beforeClose(event) {
    },
    beforeOpen(event) {
      this.getCampaigns();
    },
    affectToForm() {
      let campaigns = [];
      if(this.affectedCampaigns.length > 0) {
        this.campaigns.forEach(campaign => {
          if (this.affectedCampaigns[campaign.id]) {
            campaigns.push(campaign.id);
          }
        });
        axios({
          method: "post",
          url: 'index.php?option=com_emundus_onboard&controller=form&task=affectcampaignstoform',
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            prid: this.prid,
            campaigns: campaigns
          })
        }).then(() => {
          this.$modal.hide('modalAffectCampaign');
          window.location.href = '/configuration-forms'
        });
      } else {
        window.location.href = '/configuration-forms'
      }
    },
    goAddCampaign() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute",
        params: {
          link: 'index.php?option=com_emundus_onboard&view=campaign&layout=add&cid=',
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        window.location.href = window.location.pathname + response.data.data;
      });
    },
    getCampaigns() {
      axios.get("index.php?option=com_emundus_onboard&controller=campaign&task=getcampaignstoaffect")
              .then(response => {
                this.campaigns = response.data.data;
              });
    },
    searchCampaignByTerm() {
      axios.get("index.php?option=com_emundus_onboard&controller=campaign&task=getcampaignstoaffectbyterm&term=" + this.searchTerm)
              .then(response => {
                this.campaigns = response.data.data;
              });
    }
  },
};
</script>

<style scoped>
.modalC-content {
  height: 100%;
  box-sizing: border-box;
  padding: 10px;
  font-size: 15px;
  overflow: auto;
}
.topright {
  font-size: 25px;
  float: right;
}
.btnCloseModal {
  background-color: inherit;
}
  .update-field-header{
    margin-bottom: 1em;
  }

  .update-title-header{
    margin-top: 0;
    display: flex;
    align-items: center;
  }

.user-item{
  display: flex;
  padding: 10px;
  background-color: #f0f0f0;
  border-radius: 5px;
  align-items: center;
  margin-bottom: 1em;
}

  .bigbox{
    height: 30px !important;
    width: 30px !important;
    cursor: pointer;
  }
</style>
