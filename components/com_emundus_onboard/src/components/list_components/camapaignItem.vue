<template class="campaign-item">
  <div class="main-column-block w-row max900">
    <div class="column-block w-col w-col-11">
      <div class="block-dash" :class="isPublished ? '' : isFinish ? 'passee' : 'unpublishedBlock'">
        <div class="column-blocks w-row">
          <div class="column-inner-block w-col w-col-8 pl-30px">
            <div class="list-item-header">
              <div class="block-label">
                <a class="item-select w-inline-block"
                   v-on:click="selectItem(data.id)"
                   :class="{ active: isActive }">
                </a>
                <h1 class="nom-campagne-block white">{{ data.label }}</h1>
              </div>
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
            <div :class="isPublished ? 'publishedTag' : isFinish ? 'passeeTag' : 'unpublishedTag'">
              {{ isPublished ? publishedTag : isFinish ? passeeTag : unpublishedTag }}
            </div>
            <div class="stats-block mb-1">
              <label class="mb-0">{{Program}} : </label>
              <a class="button-programme pointer"
                 :title="AdvancedSettings"
                 @click="redirectJRoute('index.php?option=com_emundus_onboard&view=program&layout=advancedsettings&pid=' + data.program_id)">
                {{ data.program_label }}
              </a>
            </div>
            <div class="stats-block">
              <label class="mb-0">{{FilesCount}} : </label>
              <div class="nb-dossier">
                <div>{{ data.nb_files }}</div>
              </div>
            </div>
            <div class="container-gerer-modifier-visualiser">
              <a
                 @click="redirectJRoute('index.php?option=com_emundus_onboard&view=form&layout=addnextcampaign&cid=' + data.id + '&index=0')"
                 class="cta-block pointer"
                 :title="AdvancedSettings">
                <em class="fas fa-cog"></em>
              </a>
              <a
                 @click="redirectJRoute('index.php?option=com_emundus_onboard&view=campaign&layout=add&cid=' + data.id)"
                 class="cta-block ml-10px pointer"
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
import moment from "moment";
import { list } from "../../store";
import axios from "axios";

const qs = require("qs");

export default {
  name: "camapaignItem",
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
      From: Joomla.JText._("COM_EMUNDUS_ONBOARD_FROM"),
      To: Joomla.JText._("COM_EMUNDUS_ONBOARD_TO"),
      Since: Joomla.JText._("COM_EMUNDUS_ONBOARD_SINCE"),
      AdvancedSettings: Joomla.JText._("COM_EMUNDUS_ONBOARD_PROGRAM_ADVANCED_SETTINGS"),
      Program: Joomla.JText._("COM_EMUNDUS_ONBOARD_DOSSIERS_PROGRAM"),
      FilesCount: Joomla.JText._("COM_EMUNDUS_ONBOARD_DOSSIERS_COUNT")
    };
  },

  methods: {
    moment(date) {
      return moment(date);
    },

    redirectJRoute(link) {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=settings&task=redirectjroute",
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
    },
  }
};
</script>
<style scoped>
a.button-programme:hover {
  color: white;
  background: #de6339;
  cursor: pointer;
}

div.nb-dossier div:hover {
  cursor: default;
}

.nom-campagne-block {
  width: 75%;
}
  .w-row{
    margin-bottom: 0 !important;
  }

  .description-block{
    max-height: 160px;
    overflow: hidden;
  }
</style>
