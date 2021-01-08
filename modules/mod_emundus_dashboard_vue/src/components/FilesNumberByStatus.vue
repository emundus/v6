<template>
  <div class='col-md-2 col-sm-4 tchooz-widget'>
    <div class='section-sub-menu' style='margin-bottom: 10px'>
      <label>Nombre de dossiers <br/><span>{{label}}</span></label>
      <p class='big-number'>{{files}}</p>
    </div>
  </div>
</template>

<script>
import axios from "axios";

const qs = require("qs");

export default {
  name: "FilesNumberByStatus",

  props: {
    status: Number
  },

  components: {},

  data: () => ({
    files: 0,
    label: 'Total'
  }),

  created() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getfilescountbystatus",
        params: {
          status: this.status,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.files = response.data.data.files;
        if(this.status != null){
          this.label = response.data.data.value;
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

  .big-number{
    font-size: 2.5em;
    margin-top: 10%;
  }

  label {
    font-size: 21px;
  }

</style>
