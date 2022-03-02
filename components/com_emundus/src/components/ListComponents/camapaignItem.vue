<template class="campaign-item">
  <div class="main-column-block">
    <div class="column-block w-100">
      <div class="block-dash" :class="isPublished ? '' : isFinish ? 'passee' : 'unpublishedBlock'">
        <div class="column-blocks w-row">
          <div class="column-inner-block w-col w-col-8 pl-30px">
            <div class="list-item-header">
              <div class="block-label">
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
            <p class="description-block" v-if="programFilter =='all'">{{Programme}}: {{data.program_label}}</p>

            <div class="em-flex-row">
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
              <a @click="redirectJRoute('index.php?option=com_emundus&view=campaign&layout=addnextcampaign&cid=' + data.id + '&index=0')"
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
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment";
;
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
    programFilter: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      selectedData: [],
      publishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH"),
      unpublishedTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH"),
      passeeTag: this.translate("COM_EMUNDUS_ONBOARD_FILTER_CLOSE"),
      Modify: this.translate("COM_EMUNDUS_ONBOARD_MODIFY"),
      Visualize: this.translate("COM_EMUNDUS_ONBOARD_VISUALIZE"),
      Programme: this.translate("COM_EMUNDUS_ONBOARD_ADDCAMP_PROGRAM"),
      From: this.translate("COM_EMUNDUS_ONBOARD_FROM"),
      To: this.translate("COM_EMUNDUS_ONBOARD_TO"),
      Since: this.translate("COM_EMUNDUS_ONBOARD_SINCE"),
      AdvancedSettings: this.translate("COM_EMUNDUS_ONBOARD_PROGRAM_ADVANCED_SETTINGS"),
      Program: this.translate("COM_EMUNDUS_ONBOARD_DOSSIERS_PROGRAM"),
      Files: this.translate("COM_EMUNDUS_ONBOARD_FILES"),
      File: this.translate("COM_EMUNDUS_ONBOARD_FILE")
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
      window.location.href = link;
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
      return this.$store.getters['lists/isSelected'](this.data.id);
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
