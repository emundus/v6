<template>
  <!-- modalC -->
  <span :id="'modalUpdateColors'">
    <modal
        :name="'modalUpdateColors'"
        height="auto"
        :min-width="200"
        :min-height="200"
        :delay="100"
        :adaptive="true"
        :clickToClose="false"
    >

      <div class="em-modal-header">
        <div class="em-flex-space-between em-flex-row em-pointer" @click.prevent="$modal.hide('modalUpdateColors')">
          <div class="em-w-max-content em-flex-row">
            <span class="material-icons-outlined">arrow_back</span>
            <span class="em-ml-8">{{ translate('COM_EMUNDUS_ONBOARD_ADD_RETOUR') }}</span>
          </div>
        </div>
      </div>

      <div class="em-grid-3">
        <div v-for="(preset) in presets" class="preset-presentation"
             :style="'background-color:' + preset.primary + ';border-right: 100px solid' + preset.secondary"
             @click="changeColors(preset)"></div>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";

const qs = require("qs");

export default {
  name: "modalUpdateColors",
  props: {},
  components: {},
  data() {
    return {
      presets: [
        {primary: '#1b1f3c', secondary: '#de6339'},
        {primary: '#DA3832', secondary: '#204382'},
        {primary: '#727378', secondary: '#A0BD51'},
        {primary: '#727378', secondary: '#000000'},
        {primary: '#000000', secondary: '#5AA6DC'},
        {primary: '#A0BD51', secondary: '#204382'},
      ],
      updateColors: this.translate("COM_EMUNDUS_ONBOARD_UPDATE_COLORS"),
      Retour: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: this.translate("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
      Error: this.translate("COM_EMUNDUS_ONBOARD_ERROR"),
    };
  },
  methods: {
    changeColors(preset) {
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=settings&task=updatecolor",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          preset: preset,
        })
      }).then((result) => {
        this.$emit("UpdateColors", preset);
        this.$modal.hide('modalUpdateColors');
      });
    }
  }
};
</script>

<style scoped>
.fa-file-image {
  font-size: 25px;
  margin-right: 20px;
}

.preset-presentation {
  height: 100px;
  margin: 30px;
  border-radius: 25px;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
}

.preset-presentation:hover {
  transform: scale(1.05);
}

.modalC-content {
  margin-top: 20%;
}
</style>
