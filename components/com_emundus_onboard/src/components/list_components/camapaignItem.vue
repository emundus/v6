<template class="campaign-item">
  <div class="main-column-block w-row max900">
    <div class="column-tick w-col w-col-1">
      <a
        class="item-select w-inline-block"
        v-on:click="selectItem(data.id)"
        :class="{ active: isActive }"
      ></a>
    </div>
    <div class="column-block w-col w-col-11">
      <div class="block-dash" :class="isPublished ? '' : isFinish ? 'passee' : 'unpublishedBlock'">
        <div class="column-blocks w-row">
          <div class="column-inner-block w-col w-col-8">
            <h1 class="nom-campagne-block white">{{ data.label }}</h1>
            <div :class="isPublished ? 'publishedTag' : isFinish ? 'passeeTag' : 'unpublishedTag'">
              {{ isPublished ? publishedTag : isFinish ? passeeTag : unpublishedTag }}
            </div>
            <div class="date-menu orange">
              {{
                data.end_date != null && data.end_date != "0000-00-00 00:00:00" ? From : Since + " "
              }}
              {{ moment(data.start_date).format("DD/MM/YYYY") }}
              {{
                data.end_date != null && data.end_date != "0000-00-00 00:00:00"
                  ? To + " " + moment(data.end_date).format("DD/MM/YYYY")
                  : ""
              }}
            </div>
            <p class="description-block white">{{ data.short_description }}</p>
          </div>
          <div class="column-inner-block-2 w-clearfix w-col w-col-4">
            <a class="button-programme">{{ data.program_label }}</a>
            <div class="nb-dossier">
              <div>{{ data.nb_files }}</div>
            </div>
            <div class="container-gerer-modifier-visualiser">
              <a
                :href="
                  'index.php?option=com_emundus_onboard&view=campaign&layout=add&cid=' + data.id
                "
                class="cta-block"
                >{{ Modify }}</a
              >
              <a
                :href="
                  'index.php?option=com_emundus_onboard&view=campaign&layout=add&cid=' + data.id
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
import moment from "moment";
import { list } from "../../store";

export default {
  name: "camapaignItem",
  props: {
    data: Object,
    selectItem: Function
  },
  data() {
    return {
      selectedData: [],
      publishedTag: Joomla.JText._("COM_EMUNDUSONBOARD_FILTER_PUBLISH"),
      unpublishedTag: Joomla.JText._("COM_EMUNDUSONBOARD_FILTER_UNPUBLISH"),
      passeeTag: Joomla.JText._("COM_EMUNDUSONBOARD_FILTER_CLOSE"),
      Modify: Joomla.JText._("COM_EMUNDUSONBOARD_MODIFY"),
      Visualize: Joomla.JText._("COM_EMUNDUSONBOARD_VISUALIZE"),
      From: Joomla.JText._("COM_EMUNDUSONBOARD_FROM"),
      To: Joomla.JText._("COM_EMUNDUSONBOARD_TO"),
      Since: Joomla.JText._("COM_EMUNDUSONBOARD_SINCE")
    };
  },

  methods: {
    moment(date) {
      return moment(date);
    }
  },

  computed: {
    isPublished() {
      return (
        this.data.published == 1 &&
        moment(this.data.start_date) <= moment() &&
        (moment(this.data.end_date) >= moment() ||
          this.data.end_date == null ||
          this.data.end_date == "0000-00-00 00:00:00")
      );
    },

    isFinish() {
      return moment(this.data.end_date) <= moment();
    },

    isActive() {
      return list.getters.isSelected(this.data.id);
    }
  }
};
</script>
<style scoped>
.publishedTag,
.unpublishedTag,
.passeeTag {
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

.passeeTag {
  background: #4b4b4b;
}

a.button-programme:hover {
  color: white;
  cursor: default;
}

div.nb-dossier div:hover {
  cursor: default;
}

.nom-campagne-block {
  width: 75%;
}
</style>
