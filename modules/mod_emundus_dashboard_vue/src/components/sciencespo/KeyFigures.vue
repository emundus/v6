<template>
  <div class='col-md-2 col-sm-4 tchooz-widget'>
    <div class='section-sub-menu' style='margin-bottom: 10px'>
      <h3>{{translations.KeyFigures}}</h3>
      <p><span class="important-key-figures">{{files[0].value}}</span> {{translations.IncompleteFiles}}</p>
      <p><span class="important-key-figures">{{files[4].value}}</span> {{translations.RegisteredFiles}}</p>
    </div>
  </div>
</template>

<script>
import axios from "axios";

const qs = require("qs");

export default {
  name: "KeyFigures",

  props: {
    files: {},
    status: [],
    program: Number
  },

  components: {},

  data: () => ({
    translations: {
      KeyFigures: Joomla.JText._("COM_EMUNDUS_DASHBOARD_KEY_FIGURES_TITLE"),
      IncompleteFiles: Joomla.JText._("COM_EMUNDUS_DASHBOARD_INCOMPLETE_FILES"),
      RegisteredFiles: Joomla.JText._("COM_EMUNDUS_DASHBOARD_REGISTERED_FILES"),
    }
  }),

  created() {
    this.renderFilesByStatus();
  },

  methods: {
    renderFilesByStatus() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=dashboard&task=getfilescountbystatus",
        params: {
          program: this.program,
        },
        paramsSerializer: params => {
          return qs.stringify(params);
        }
      }).then(response => {
        this.files = response.data.files;
        this.status = response.data.status;
      });
    }
  },

  watch:{
    program: function () {
      this.renderFilesByStatus();
    }
  }
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
  text-align: center;
}

.section-sub-menu p{
  justify-content: center;
  align-items: center;
  display: flex;
}

h3{
  margin-bottom: 15px;
  color: #000;
  font-size: 24px;
}

.important-key-figures{
  font-weight: bold;
  font-size: 30px;
  margin-right: 10px;
}

@media (max-width: 1440px) {
  .faq-intro{
    display: none;
  }
}

</style>
