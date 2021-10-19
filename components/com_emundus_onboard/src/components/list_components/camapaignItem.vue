<template class="campaign-item">
  <div class="main-column-block">
    <div class="column-block w-100">
      <div class="block-dash" :class="isPublished ? '' : isFinish ? 'passee' : 'unpublishedBlock'">
        <div class="column-blocks w-row">
          <div class="column-inner-block w-col w-col-8 pl-30px">
            <div class="list-item-header">
              <div class="block-label">
<!--                <a class="item-select w-inline-block"
                   v-on:click="selectItem(data.id)"
                   :class="{ active: isActive }">
                </a>-->
                <h2 class="nom-campagne-block">{{ data.label }}</h2>
              </div>
            </div>
            <div class="date-menu">
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
            <p class="description-block" v-html="data.short_description"></p>
            <div class="d-flex">
              <div :class="isPublished ? 'publishedTag' : isFinish ? 'passeeTag' : 'unpublishedTag'">
                {{ isPublished ? publishedTag : isFinish ? passeeTag : unpublishedTag }}
              </div>
              <div class="nb-dossier">
                <div>{{ data.nb_files }} <span v-if="data.nb_files > 1">{{ Files }}</span><span v-else>{{ File }}</span></div>
              </div>
            </div>
            <div>
              <hr class="divider-card">
            <div class="stats-block">
              <a @click="redirectJRoute('index.php?option=com_emundus_onboard&view=form&layout=addnextcampaign&cid=' + data.id + '&index=0')"
                 class="bouton-ajouter pointer add-button-div"
                 :title="AdvancedSettings">
                <em class="fas fa-pen"></em>
                <span>{{Modify}}</span>
              </a>
              <v-popover :popoverArrowClass="'custom-popover-arraow'">
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
          <!--<div class="column-inner-block-2 w-clearfix w-col w-col-4">
            <div class="stats-block mb-1">
              <label class="mb-0">{{Program}} : </label>
              <a class="button-programme pointer"
                 :title="AdvancedSettings"
                 @click="redirectJRoute('index.php?option=com_emundus_onboard&view=program&layout=advancedsettings&pid=' + data.program_id)">
                {{ data.program_label }}
              </a>
            </div>

            <div class="container-gerer-modifier-visualiser">

              <a
                 @click="redirectJRoute('index.php?option=com_emundus_onboard&view=campaign&layout=add&cid=' + data.id)"
                 class="cta-block ml-10px pointer"
                 :title="Modify">
                <em class="fas fa-edit"></em>
              </a>
            </div>
          </div>-->
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";
import { list } from "../../store";
import axios from "axios";
import actions from "./action_menu";

const qs = require("qs");

export default {
  name: "camapaignItem",
  components: {actions},
  props: {
    data: Object,
    selectItem: Function,
    actions: Object,
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
      Files: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILES"),
      File: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILE")
    };
  },

  methods: {
    updateLoading(value){
      this.$emit('updateLoading',value);
    },

    validateFilters(){
      this.$emit('validateFilters');
    },

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
  .w-row{
    margin-bottom: 0 !important;
  }
  h2 {
    color: #000;
    font-size: 24px;
    font-weight: 700;
  }


</style>
