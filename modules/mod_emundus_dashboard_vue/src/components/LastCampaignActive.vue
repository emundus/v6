<template>
  <div class="col-md-5 col-sm-6 tchooz-widget">
    <div class='section-sub-menu' style='margin-bottom: 10px'>
      <template v-if="campaigns.length > 0">
        <template v-if="campaigns[cindex]">
          <div class='d-flex'>
            <h2>{{campaigns[cindex].label}}</h2>
            <div class='publishedTag'>
              {{translations.Published}}
            </div>
          </div>
          <div class='date-menu'>
            {{translations.From}} {{campaigns[cindex].start_date | formatDate}} {{translations.To}} {{campaigns[cindex].end_date | formatDate}}
          </div>
          <p class='description-block'>{{campaigns[cindex].short_description}}</p>
          <div class='stats-block'>
            <div class='nb-dossier'>
              <div v-if="files > 1">{{files}} {{translations.Files}}</div>
              <div v-else>{{files}} {{translations.FileNumber}}</div>
            </div>
          </div>
        </template>
        <template v-else>
          <h1>{{translations.NoCampaign}}</h1>
        </template>
      </template>
      <template v-else>
        <h1>{{translations.NoCampaign}}</h1>
      </template>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import moment from "moment";
import Vue from "vue";

const qs = require("qs");
Vue.filter('formatDate', function(value) {
  if (value) {
    return moment(String(value)).format('DD/MM/YYYY')
  }
})

export default {
  name: "LastCampaignActive",

  props: {
    campaigns: Array,
    cindex: Number
  },

  components: {},

  data: () => ({
    files: 0,
    translations:{
      Published: Joomla.JText._("COM_EMUNDUS_DASHBOARD_CAMPAIGN_PUBLISHED"),
      NoCampaign: Joomla.JText._("COM_EMUNDUS_DASHBOARD_NO_CAMPAIGN"),
      From: Joomla.JText._("COM_EMUNDUS_DASHBOARD_CAMPAIGN_FROM"),
      To: Joomla.JText._("COM_EMUNDUS_DASHBOARD_CAMPAIGN_TO"),
      Files: Joomla.JText._("COM_EMUNDUS_DASHBOARD_FILES"),
      FileNumber: Joomla.JText._("COM_EMUNDUS_DASHBOARD_FILE_NUMBER")
    },
  }),

  methods: {
    getFilesByCampaign(){
      if(typeof this.campaigns[this.cindex] !== 'undefined'){
        axios({
          method: "get",
          url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getfilesbycampaign",
          params: {
            cid: this.campaigns[this.cindex].id,
          },
          paramsSerializer: params => {
            return qs.stringify(params);
          }
        }).then(response => {
          this.files = response.data.data;
        });
      } else {
        setTimeout(() => {
          this.getFilesByCampaign();
        },500);
      }

    }
  },

  created() {
    this.getFilesByCampaign();
  },
}
</script>

<style scoped lang="scss">

  .section-sub-menu {
    display: block;
    width: 100%;
    height: 100%;
    justify-content: center;
    border-radius: 4px;
    background-color: #fff;
    color: #1f1f1f;
    box-shadow: 0 1px 2px 0 hsla(0, 0%, 41.2%, .19);
    padding: 30px;
  }

  .d-flex{
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    justify-content: space-between;
      h2{
        margin-top: 0;
        margin-bottom: 0 !important;
        color: #000;
        font-size: 24px;
        line-height: 35px;
        font-weight: 700;
        width: 75%;
      }
  }

.section-sub-menu {
  h2, h1 {
    color: #000;
    font-size: 24px;
    font-weight: 700;
  }
}
  .publishedTag{
    color: #78dc6e;
    border-radius: 25px;
    width: min-content;
    padding: 5px 30px;
    text-align: center;
    border: 2px solid #78dc6e;
    font-size: 12px;
    background: #fff;
    font-weight: 500;
  }
  .date-menu{
    color: #b2b2c1;
    font-size: 12px;
  }
  .description-block{
    width: 70%;
    margin-top: 10px;
    margin-bottom: 0;
    font-size: 13px;
    line-height: 20px;
    height: 40px;
    -ms-overflow-style: none;
    scrollbar-width: none;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
  }
  .stats-block{
    display: flex;
    align-items: center;
    width: 90%;
  }
  .nb-dossier{
    width: min-content;
    border-radius: 25px;
    background-color: #5a5a72;
    color: #fff;
    font-size: 14px;
    height: 29px;
    line-height: 19px;
    display: flex;
    justify-content: center;
    align-items: center;
    white-space: nowrap;
    padding: 5px 30px;
    font-weight: 500;
  }
</style>
