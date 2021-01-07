<template>
  <div class='col-md-2 col-sm-4 tchooz-widget'>
    <div class='section-sub-menu' style='margin-bottom: 10px'>
      <h3>Une question ?</h3>
      <p>N'hésitez plus, consultez notre Foire aux questions.</p>
      <a href='https://www.emundus.fr/ressources/centre-aide' target='_blank' rel="noopener noreferrer"><button class='bouton-faq'>F.A.Q</button></a>
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
  }),

  created() {

  },

  methods: {},
}
</script>

<style scoped lang="scss">

  h1 {
    color: #000;
  }
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
    bottom: 15px;
    padding: 5px 20px;
    height: 36px;
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
    font-weight: 300;
  }
  .bouton-faq:hover{
    cursor: pointer;
    background-color: transparent;
    color: #16afe1;
  }

  h3{
    margin-bottom: 15px;
  }

</style>
