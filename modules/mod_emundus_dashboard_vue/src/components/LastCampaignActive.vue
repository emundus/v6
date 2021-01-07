<template>
  <div class="col-md-5 col-sm-6 tchooz-widget">
    <div class='section-sub-menu' style='margin-bottom: 10px'>
      <template v-if="campaigns.length > 0">
        <template v-if="campaigns[cindex]">
          <div class='d-flex'>
            <h1>{{campaigns[cindex].label}}</h1>
            <div class='publishedTag'>
              Publiée
            </div>
          </div>
          <div class='date-menu'>
            du {{campaigns[cindex].start_date | formatDate}} au {{campaigns[cindex].end_date | formatDate}}
          </div>
          <p class='description-block'>{{campaigns[cindex].short_description}}</p>
          <div class='stats-block'>
            <div class='nb-dossier'>
              <div>200 Dossiers</div>
            </div>
          </div>
        </template>
        <template v-else>
          <h1>Pas de campagnes en cours</h1>
        </template>
      </template>
      <template v-else>
        <h1>Pas de campagnes en cours</h1>
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
    translations:{
      Published: Joomla.JText._("COM_EMUNDUS_ONBOARD_FILTER_PUBLISH")
    },
  }),

  created() {},

  methods: {},
}
</script>

<style scoped lang="scss">

  h1 {
    color: #00000;
  }
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
      h1{
        margin-top: 0;
        margin-bottom: 0 !important;
        color: #1f1f1f;
        font-size: 28px;
        line-height: 35px;
        font-weight: 400;
        width: 75%;
      }
  }
  .publishedTag{
    color: #78dc6e;
    border-radius: 25px;
    width: min-content;
    padding: 5px 20px;
    text-align: center;
    border: 2px solid #78dc6e;
    font-size: 12px;
    background: #fff;
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
    line-height: 19px;
    display: flex;
    justify-content: center;
    align-items: center;
    white-space: nowrap;
    padding: 5px 20px;
  }
</style>
