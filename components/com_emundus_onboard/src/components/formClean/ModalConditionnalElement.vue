<template>
  <!-- modalC -->
  <span :id="'modalConditionnalElement'">
    <modal
      :name="'modalConditionnalElement' + ID"
      height="auto"
      transition="nice-modal-fade"
      :min-width="200"
      :min-height="200"
      :delay="100"
      :adaptive="true"
      :clickToClose="false"
      @closed="beforeClose"
      @before-open="beforeOpen"
    >
      <div class="modalC-content">
        <div class="update-field-header">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalConditionnalElement' + ID)">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{ConditionalElement}}
          </h2>
        </div>
      </div>
      <div class="col-md-12 mb-1">
        <a
          class="bouton-sauvergarder-et-continuer-3"
        >{{ Continuer }}</a>
        <a
          class="bouton-sauvergarder-et-continuer-3 w-retour"
          @click.prevent="$modal.hide('modalConditionnalElement' + ID)"
        >{{Retour}}</a>
      </div>
      <div class="loading-form" style="top: 10vh" v-if="submitted">
        <Ring-Loader :color="'#de6339'" />
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
  name: "modalConditionnalElement",
  props: {
    ID: Number,
  },
  data() {
    return {
      submitted: false,
      ConditionalElement: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONDITION"),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Continuer: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_CONTINUER"),
    };
  },
  methods: {
    beforeClose(event) {
      if (this.changes === true) {
        this.$emit(
          "show",
          "foo-velocity",
          "warn",
          this.dataSaved,
          this.informations
        );
      }
      this.changes = false;
    },
    beforeOpen(event) {},
  },
};
</script>

<style scoped>
.modalC-content {
  height: 100%;
  box-sizing: border-box;
  padding: 10px;
  font-size: 15px;
  overflow: auto;
}
.topright {
  font-size: 25px;
  float: right;
}
.btnCloseModal {
  background-color: inherit;
}
.update-field-header{
  margin-bottom: 1em;
}

.update-title-header{
  margin-top: 0;
  display: flex;
  align-items: center;
}

@media (max-width: 991px) {
  .top-responsive {
    margin-top: 5em;
  }
}
</style>
