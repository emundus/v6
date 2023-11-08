<template>
  <div class="container-evaluation" style="width: auto">
    <button type="button" @click="rewind()" class="bouton-sauvergarder-et-continuer" style="float: left">
      {{ REWINDTUTORIAL }}
    </button>
  </div>
</template>

<script>
import axios from "axios";
import Swal from "sweetalert2";

const qs = require("qs");

export default {
  name: "helpSettings",
  props: {
    actualLanguage: String,
    manyLanguages: Number,
  },

  data: () => ({
    REWINDTUTORIAL: this.translate("COM_EMUNDUS_ONBOARD_TUTORIAL_REWIND"),
  }),

  methods: {
    rewind() {
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=settings&task=rewindtutorial",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
      }).then((result) => {
        Swal.fire({
          backdrop: true,
          title: this.translate("COM_EMUNDUS_ONBOARD_TUTORIAL_REWIND"),
          text: this.translate("COM_EMUNDUS_ONBOARD_TUTORIAL_REWIND_SUCCESS"),
          showConfirmButton: true,
          timer: 5000
        })
      });
    }
  }
}
</script>

<style scoped>

</style>
