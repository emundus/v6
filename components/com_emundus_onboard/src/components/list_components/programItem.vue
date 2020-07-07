<template class="program-item">
  <div class="main-column-block w-row">
    <div class="column-block w-col w-col-11">
      <div class="block-dash" :class="isPublished ? '' : 'unpublishedBlock'">
        <div class="column-blocks w-row">
          <div class="column-inner-block w-col w-col-8 pl-30px">
            <div class="list-item-header">
              <div :class="isPublished ? 'publishedTag' : 'unpublishedTag'">
                {{ isPublished ? publishedTag : unpublishedTag }}
              </div>
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
            <div class="stats-block mb-1">
              <label class="mb-0">{{CampaignNumbers}} : </label>
              <div class="nb-dossier ml-10px">
                <div>{{ data.nb_campaigns }}</div>
              </div>
            </div>
            <div class="container-gerer-modifier-visualiser">
              <a :href="'programs/index.php?option=com_emundus_onboard&view=program&layout=advancedsettings&pid=' + data.id"
                 class="cta-block"
                 :title="AdvancedSettings">
                <em class="fas fa-cog"></em>
              </a>
              <a :href="'programs/index.php?option=com_emundus_onboard&view=program&layout=add&pid=' + data.id"
                class="cta-block ml-10px"
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
import { list } from "../../store";

export default {
  name: "programItem",
  props: {
    data: Object,
    selectItem: Function
  },

  data() {
    return {
      selectedData: [],
      publishedTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
      unpublishedTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
      passeeTag: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_CLOSE"),
      Modify: Joomla.JText._("COM_EMUNDUS_ONBOARD_MODIFY"),
      Visualize: Joomla.JText._("COM_EMUNDUS_ONBOARD_VISUALIZE"),
      AdvancedSettings: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADVANCED_SETTINGS"),
      CampaignNumbers: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_CAMPAIGN_NUMBERS"),
    };
  },

  computed: {
    isPublished() {
      return this.data.published == 1;
    },

    isActive() {
      return list.getters.isSelected(this.data.id);
    }
  }
};
</script>
<style scoped>
.publishedTag,
.unpublishedTag {
  position: absolute;
  top: 5%;
  right: 2%;
  color: #fff;
  font-weight: 700;
  border-radius: 10px;
  width: 18%;
  padding: 5px;
  text-align: center;
}

.unpublishedTag {
  background: #c3c3c3;
}

.publishedTag {
  background: #44d421;
}

.unpublishedBlock {
  background: #4b4b4b;
}

a.button-programme:hover {
  color: white;
  cursor: default;
}
  .w-row{
    margin-bottom: 0;
  }
</style>
