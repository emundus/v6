<template class="form-item">
  <div class="main-column-block">
    <div class="column-block w-100">
      <div class="block-dash" :class="isPublished ? '' : 'unpublishedBlock'">
        <div class="column-blocks w-row">
          <div class="column-inner-block w-col w-col-8 pl-30px">
            <div class="list-item-header">
              <div class="block-label">
<!--                <a class="item-select w-inline-block"
                   v-on:click="selectItem(data.id)"
                   :class="{ active: isActive }">
                </a>-->
                <h2 class="nom-campagne-block">{{ data.form_label }}</h2>
              </div>
            </div>
            <div>
              <p class="associated-campaigns" v-if="campaigns.length == 1">{{translations.campaignAssociated}} :</p>
              <p class="associated-campaigns" v-if="campaigns.length > 1">{{translations.campaignsAssociated}} :</p>
              <ul style="margin-top: 10px;margin-left: 0">
                <li v-for="(campaign, index) in campaigns" :key="index" class="campaigns-item">{{campaign.label}}</li>
              </ul>
            </div>
            <div class="em-flex-row">
              <div :class="isPublished ? 'publishedTag' : 'unpublishedTag'">
                {{ isPublished ? translations.publishedTag : translations.unpublishedTag }}
              </div>
            </div>
            <div>
              <hr class="divider-card">
              <div class="stats-block">
                <a class="bouton-ajouter pointer add-button-div"
                   @click="redirectJRoute('index.php?option=com_emundus&view=form&layout=formbuilder&prid=' + data.id + '&index=0&cid=')"
                   :title="translations.Modify">
                  <em class="fas fa-pen"></em> {{translations.Modify}}
                </a>
                <v-popover :popoverArrowClass="'custom-popover-arrow'">
                  <button class="tooltip-target b3 card-button"></button>

                  <template slot="popover">
                    <actions
                        :data="actions"
                        :selected="this.data.id"
                        :published="isPublished"
                        @validateFilters="validateFilters()"
                        @updateLoading="updateLoading"
                    ></actions>
                  </template>
                </v-popover>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
;
import axios from "axios";
import actions from "./action_menu";

const qs = require("qs");

export default {
  name: "formItem",
  components: {actions},
  props: {
    data: Object,
    selectItem: Function,
    actions: Object,
    programFilter: {
      type: String,
      required: false,
    },
  },
  data() {
    return {
      selectedData: [],
      updateAccess: false,
      campaigns: [],
      translations:{
        publishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH_FORM"),
        unpublishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH_FORM"),
        Modify: this.translate("COM_EMUNDUS_ONBOARD_MODIFY"),
        campaignAssociated: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGN_ASSOCIATED"),
        campaignsAssociated: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED")
      },
    };
  },

  methods: {
    updateLoading(value){
      this.$emit('updateLoading',value);
    },

    validateFilters(){
      this.$emit('validateFilters');
    },

    redirectJRoute(link) {
      window.location.href = link;
    },

    getAssociatedCampaigns(){
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=form&task=getassociatedcampaign",
        params: {
          pid: this.data.id,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.campaigns = response.data.data;
      });
    }
  },

  created() {
    if (this.$store.getters['lists/formsAccess'].length > 0) {
      this.$store.getters['lists/formsAccess'][0].forEach(element => {
        if (element === this.data.id){
          this.updateAccess = true;
        }
      });
    }

    this.getAssociatedCampaigns();
  },

  computed: {
    isPublished() {
      return this.data.status == 1;
    },

    isActive() {
      return this.$store.getters['lists/isSelected'](this.data.id);
    }
  }
};
</script>
<style scoped>
  .w-row{
    margin-bottom: 0;
  }
  .associated-campaigns{
    font-style: italic;
    font-size: 12px;
  }
</style>
