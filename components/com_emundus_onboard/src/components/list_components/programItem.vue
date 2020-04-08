<template class="program-item">
  <div class="main-column-block w-row">
    <div class="column-tick w-col w-col-1">
      <a
        href="#!"
        class="item-select w-inline-block"
        v-on:click="selectItem(data.id)"
        :class="{ active: isActive }"
      ></a>
    </div>
    <div class="column-block w-col w-col-11">
      <div class="block-dash" :class="isPublished ? '' : 'unpublishedBlock'">
        <div class="column-blocks w-row">
          <div class="column-inner-block w-col w-col-8">
            <h1 class="nom-campagne-block white">{{ data.label }}</h1>
            <div :class="isPublished ? 'publishedTag' : 'unpublishedTag'">
              {{ isPublished ? publishedTag : unpublishedTag }}
            </div>
            <div class="date-menu orange"></div>
            <p class="description-block white" v-html="data.notes">{{ data.notes }}</p>
          </div>
          <div class="column-inner-block-2 w-clearfix w-col w-col-4">
            <a href="#" class="button-programme">{{ data.label }}</a>
            <div class="nb-dossier">
              <div>{{ data.nb_files }}</div>
            </div>
            <div class="container-gerer-modifier-visualiser">
              <a
                :href="
                  'index.php?option=com_emundus_onboard&view=program&layout=add&pid=' + data.id
                "
                class="cta-block"
                >{{ Modify }}</a
              ><a
                :href="
                  '/files&pid=' + data.id
                "
                class="cta-block"
                >{{ Visualize }}</a
              >
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
      Visualize: Joomla.JText._("COM_EMUNDUS_ONBOARD_VISUALIZE")
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
</style>
