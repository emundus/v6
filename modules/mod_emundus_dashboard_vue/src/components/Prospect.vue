<template>
  <div class='col-md-3 col-sm-5 tchooz-widget'>
    <div class='section-sub-menu' style='margin-bottom: 10px'>
      <h3>{{translations.Demo}}</h3>
      <p class="faq-intro">{{translations.currentlyDemo}}<span style="font-weight: bold">{{this.counter}}</span>{{translations.currentlyDemo2}}</p>
      <button class='bouton-faq' @click="enableFreePeriod"><span>{{translations.enableDemo}}</span></button>
    </div>
  </div>
</template>

<script>
import axios from "axios";

const qs = require("qs");

export default {
  name: "Faq",

  props: {},

  components: {},

  data: () => ({
    translations: {
      Demo: Joomla.JText._("COM_EMUNDUS_DASHBOARD_PROSPECT_DEMO"),
      currentlyDemo: Joomla.JText._("COM_EMUNDUS_DASHBOARD_PROSPECT_DEMO_TEXT"),
      currentlyDemo2: Joomla.JText._("COM_EMUNDUS_DASHBOARD_PROSPECT_DEMO_TEXT_2"),
      enableDemo: Joomla.JText._("COM_EMUNDUS_DASHBOARD_PROSPECT_DEMO_ENABLE"),
    },
    counter: 30,
  }),

  created() {
    this.getcounter();
  },

  methods: {
    enableFreePeriod(){
      axios({
        method: "post",
        url:
            "index.php?option=com_emundus_onboard&controller=dashboard&task=enablefreeperiod",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
      }).then(response => {
        console.log(response.msg);
      });
    },

    getcounter(){
      axios({
        method: "get",
        url:
            "index.php?option=com_emundus_onboard&controller=dashboard&task=democounter",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
      }).then(response => {
        this.counter = response.data.data;
      });
    }
  },
}
</script>

<style scoped lang="scss">

  .section-sub-menu{
    display: block;
    width: 100%;
    height: 100%;
    justify-content: center;
    border-radius: 4px;
    background-color: #fff;
    color: #1f1f1f;
    box-shadow: 0 1px 2px 0 hsla(0,0%,41.2%,.19);
    padding: 30px;
  }
  .bouton-faq{
    margin-top: 20px;
    height: 30px;
    border-radius: 25px;
    border: 2px solid #16afe1;
    background-color: #16afe1;
    transition: color .2s ease,background-color .2s cubic-bezier(.55,.085,.68,.53);
    color: #fff;
    text-decoration: none;
    width: 100%;
    font-size: 14px;
    font-weight: 500;
  }
  .bouton-faq:hover{
    cursor: pointer;
    background-color: transparent;
    color: #16afe1;
  }

  h3{
    margin-bottom: 15px;
    color: #000;
    font-size: 24px;
  }

  .prospect-link:hover{
    text-decoration: unset;
  }

</style>
