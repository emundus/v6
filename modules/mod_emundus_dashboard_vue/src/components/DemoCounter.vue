<template>
  <div class='col-md-2 col-sm-4 tchooz-widget'>
    <div class='section-sub-menu' style='margin-bottom: 10px'>
      <h3>Essai Tchooz</h3>
      <p class="faq-intro"><span class="big-number">{{counter}}</span> jours</p>
    </div>
  </div>
</template>

<script>
import axios from "axios";

const qs = require("qs");

export default {
  name: "DemoCounter",

  props: {},

  components: {},

  data: () => ({
    today: new Date(),
    end: null,
    counter: 0
  }),

  created() {
    axios({
      method: "get",
      url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getfirstcoordinatorconnection",
    }).then(response => {
      const register = response.data.data;
      if(register != '0000-00-00 00:00:00') {
        this.end = new Date(register);
        this.end.setDate(this.end.getDate() + 90);
        const diffTime = Math.abs(this.end - this.today);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        this.counter = diffDays;
      }
    });
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
    position: absolute;
    padding: 5px 30px;
    height: 29px;
    border-radius: 25px;
    border: 2px solid #16afe1;
    background-color: #16afe1;
    transition: color .2s ease,background-color .2s cubic-bezier(.55,.085,.68,.53);
    color: #fff;
    text-decoration: none;
    width: auto;
    display: flex;
    align-items: center;
    font-size: 14px;
    font-weight: 500;
    bottom: 30px;
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

  .big-number{
    font-size: 5em;
    color: #16afe1;
  }

  @media (max-width: 1440px) {
    .faq-intro{
      display: none;
    }
  }

</style>
