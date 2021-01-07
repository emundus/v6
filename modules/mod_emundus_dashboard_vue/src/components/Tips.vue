<template>
  <div class='col-md-4 col-sm-6 col-md-offset-2 tchooz-widget'>
    <div class='section-sub-menu' style='margin-bottom: 10px'>
      <h3>Astuces</h3>
    </div>
  </div>
</template>

<script>
const qs = require("qs");

export default {
  name: "Tips",

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
</style>
