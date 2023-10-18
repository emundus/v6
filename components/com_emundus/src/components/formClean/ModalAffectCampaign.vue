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
      <div class="em-flex-row em-flex-space-between em-mb-16">
        <h4>
          {{translations.affectCampaigns}}
        </h4>
        <button class="em-pointer em-transparent-button" @click.prevent="$modal.hide('modalAffectCampaign')">
          <span class="material-icons-outlined">close</span>
        </button>
      </div>

      <div>
        <p v-if="campaigns.length === 0" class="em-mb-16">{{translations.campaignsEmpty}}</p>
        <div class="em-mb-16">
          <div v-for="(campaign, index) in campaigns" :key="index" class="user-item">
              <input type="checkbox" class="form-check-input bigbox" v-model="affectedCampaigns[campaign.id]">
              <div class="ml-10px">
                  <p>{{campaign.label}}</p>
              </div>
          </div>
        </div>
      </div>

      <div class="em-flex-row em-flex-space-between em-mb-8">
        <button
            type="button"
            class="em-secondary-button em-w-auto"
            @click="redirect('index.php?option=com_emundus&view=form')">
          {{ translations.BackWithoutAssociation }}
        </button>
        <button type="button"
                class="em-primary-button em-w-auto"
                @click.prevent="affectToForm">
          {{ translations.Continuer }}
        </button>
      </div>

      <div class="em-float-right">
        <button v-if="!testing"
                type="button"
                class="em-tertiary-button em-w-auto"
                @click.prevent="goAddCampaign">
          {{translations.addCampaign}}
        </button>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
  name: "modalAffectCampaign",
  props: { prid: String, testing: Boolean },
  data() {
    return {
      campaigns: [],
      affectedCampaigns: [],
      searchTerm: '',
      translations:{
        Continuer: this.translate("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
        affectCampaigns: this.translate("COM_EMUNDUS_ONBOARD_FORM_AFFECTCAMPAIGNS"),
        campaignsEmpty: this.translate("COM_EMUNDUS_ONBOARD_FORM_CAMPAIGNSEMPTY"),
        addCampaign: this.translate("COM_EMUNDUS_ONBOARD_ADD_CAMPAIGN"),
        BackWithoutAssociation: this.translate("COM_EMUNDUS_ONBOARD_BACK_WITHOUT_ASSOCIATION"),
      }
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
          url: 'index.php?option=com_emundus&controller=form&task=affectcampaignstoform',
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            prid: this.prid,
            campaigns: campaigns
          })
        }).then(() => {
          if(!this.testing) {
            window.location.href = 'index.php?option=com_emundus&view=form';
          } else {
            if(campaigns.length > 0){
              this.$emit("testForm");
              this.$modal.hide('modalAffectCampaign');
            }
          }
        });
      } else {
        if(!this.testing) {
          window.location.href = 'index.php?option=com_emundus&view=form';
        } else {
          this.$modal.hide('modalAffectCampaign');
        }
      }
    },
    goAddCampaign() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=settings&task=redirectjroute",
        params: {
          link: 'index.php?option=com_emundus&view=campaigns&layout=add&cid=',
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        window.location.href = window.location.pathname + response.data.data;
      });
    },
    getCampaigns() {
      axios.get("index.php?option=com_emundus&controller=campaign&task=getcampaignstoaffect")
          .then(response => {
            this.campaigns = response.data.data;
          });
    },
    searchCampaignByTerm() {
      axios.get("index.php?option=com_emundus&controller=campaign&task=getcampaignstoaffectbyterm&term=" + this.searchTerm)
          .then(response => {
            this.campaigns = response.data.data;
          });
    },
    redirect(link) {
      window.location.href = link;
    },
  },
};
</script>

<style scoped>
.modalC-content {
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

.campaigns-list{
  max-height: 50vh;
  margin-top: 15%;
}

.wrap{
  position: fixed;
  width: 22%;
}
.searchButton{
  height: 50px;
  background: transparent;
  margin-left: 12px;
}
</style>
