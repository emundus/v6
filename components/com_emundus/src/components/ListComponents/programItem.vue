<template class="program-item">
  <div class="main-column-block">
    <div class="column-block w-col w-col-11">
      <div class="block-dash" :class="isPublished ? '' : 'unpublishedBlock'">
        <div class="column-blocks w-row">
          <div class="column-inner-block w-col w-col-8 pl-30px">
            <div class="list-item-header">
              <div class="block-label">
                <a
                        class="item-select w-inline-block"
                        v-on:click="selectItem(data.id)"
                        :class="{ active: isActive }"
                ></a>
                <h1 class="nom-campagne-block white">{{ data.label }}</h1>
              </div>
            </div>
            <div class="date-menu orange"></div>
            <div class="description-block white" v-html="data.notes">
            </div>
          </div>
          <div class="column-inner-block-2 w-clearfix w-col w-col-4">
            <div :class="isPublished ? 'publishedTag' : 'unpublishedTag'">
              {{ isPublished ? publishedTag : unpublishedTag }}
            </div>
            <div class="stats-block mb-1">
              <label class="mb-0">{{CampaignNumbers}} : </label>
              <div class="nb-dossier">
                <div>{{ data.nb_campaigns }}</div>
              </div>
            </div>
            <div class="container-gerer-modifier-visualiser">
              <a class="cta-block pointer"
                 :title="AdvancedSettings"
                 @click="redirectJRoute('index.php?option=com_emundus&view=program&layout=advancedsettings&pid=' + data.id)">
                <em class="fas fa-cog"></em>
              </a >
              <a class="cta-block ml-10px pointer"
                 @click="redirectJRoute('index.php?option=com_emundus&view=program&layout=add&pid=' + data.id)"
                 :title="Modify">
                <em class="fas fa-edit"></em>
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
  name: "programItem",
  props: {
    data: Object,
    selectItem: Function
  },

  data() {
    return {
      selectedData: [],
      publishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
      unpublishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
      passeeTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_CLOSE"),
      Modify: this.translate("COM_EMUNDUS_ONBOARD_MODIFY"),
      Visualize: this.translate("COM_EMUNDUS_ONBOARD_VISUALIZE"),
      AdvancedSettings: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_ADVANCED_SETTINGS"),
      CampaignNumbers: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_CAMPAIGN_NUMBERS"),
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
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        window.location.href = window.location.pathname + response.data.data;
      });
    }
  },

  computed: {
    isPublished() {
      return this.data.published == 1;
    },

    isActive() {
      return this.$store.getters['lists/isSelected'](this.data.id);
    }
  }
};
</script>
<style scoped>
a.button-programme:hover {
  color: white;
  cursor: default;
}
  .w-row{
    margin-bottom: 0;
  }

.description-block{
  max-height: 160px;
  overflow: hidden;
}
</style>
