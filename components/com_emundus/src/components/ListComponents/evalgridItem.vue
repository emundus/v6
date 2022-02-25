<template class="form-item">
  <div class="main-column-block">
    <div class="column-block w-100">
      <div class="block-dash">
        <div class="column-blocks w-row">
          <div class="column-inner-block w-col w-col-8 pl-30px">
            <div class="list-item-header">
              <div class="block-label">
                <a
                  class="item-select w-inline-block"
                  v-on:click="selectItem(data.id)"
                  :class="{ active: isActive }"
                >
                </a>
                <h2 class="nom-campagne-block">
                  {{ data.label[actualLanguage] }}
                </h2>
              </div>
              <!--<div :class="isPublished ? 'publishedTag' : 'unpublishedTag'">
                {{ isPublished ? translations.publishedTag : translations.unpublishedTag }}
              </div>-->
            </div>
            <div>
              <ul style="margin-top: 10px">
                <li class="campaigns-item">{{ campaigns.label }}</li>
              </ul>
            </div>
            <div class="stats-block" style="justify-content: flex-end">
              <a
                class="bouton-ajouter pointer add-button-div"
                @click="evaluationBuilder"
                :title="translations.Modify"
              >
                <em class="fas fa-pen"></em> Éditer
              </a>
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

const qs = require("qs");

export default {
  name: "grilleEvalItem",
  props: {
    data: Object,
    selectItem: Function,
    actualLanguage: String,
  },
  data() {
    return {
      selectedData: [],
      updateAccess: false,
      campaigns: {
        label: "",
      },
      translations: {
        publishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
        unpublishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
        Modify: this.translate("COM_EMUNDUS_ONBOARD_MODIFY"),
        campaignAssociated: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGN_ASSOCIATED"),
        campaignsAssociated: this.translate("COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED"),
      },
    };
  },

  methods: {
    redirectJRoute(link) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=settings&task=redirectjroute",
        params: {
          link: link,
        },
        paramsSerializer: (params) => {
          return qs.stringify(params);
        },
      }).then((response) => {
        window.location.href = window.location.pathname + response.data.data;
      });
    },

    getAssociatedCampaigns() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=form&task=getassociatedprogram",
        params: {
          fid: this.data.id,
        },
        paramsSerializer: (params) => {
          return qs.stringify(params);
        },
      }).then((response) => {
        if (response.data.data !== null) {
          this.campaigns = response.data.data;
        }
      });
    },
    evaluationBuilder() {
      this.redirectJRoute(
        "index.php?option=com_emundus&view=form&layout=formbuilder&prid=&index=0&cid=" +
          "" +
          "&evaluation=" +
          this.data.id
      );
    },
  },

  created() {
    if (this.$store.getters['lists/formsAccess'].length > 0) {
      this.$store.getters['lists/formsAccess'][0].forEach((element) => {
        if (element === this.data.id) {
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
    },
  },
};
</script>
<style scoped>
.w-row {
  margin-bottom: 0;
}
.associated-campaigns {
  font-style: italic;
  font-size: 12px;
}
</style>
