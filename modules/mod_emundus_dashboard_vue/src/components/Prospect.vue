<template>
  <div class='col-md-2 col-sm-4 tchooz-widget'>
    <div class='section-sub-menu' style='margin-bottom: 10px'>
      <h3>{{translations.Demo}}</h3>
      <p class="faq-intro">{{translations.currentlyDemo}}</p>
      <a href='https://www.emundus.fr/ressources/centre-aide' target='_blank' rel="noopener noreferrer" class="prospect-link"><button class='bouton-faq'><span>{{translations.enableDemo}}</span></button></a>
    </div>
  </div>
</template>

<script>
const qs = require("qs");

export default {
  name: "Faq",

  props: {},

  components: {},

  data: () => ({
    translations: {
      Demo: Joomla.JText._("COM_EMUNDUS_DASHBOARD_PROSPECT_DEMO"),
      currentlyDemo: Joomla.JText._("COM_EMUNDUS_DASHBOARD_PROSPECT_DEMO_TEXT"),
      enableDemo: Joomla.JText._("COM_EMUNDUS_DASHBOARD_PROSPECT_DEMO_ENABLE"),
    }
  }),

  created() {

  },

  methods: {},
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

  @media (max-width: 1440px) {
    .faq-intro{
      display: none;
    }
  }

</style>
