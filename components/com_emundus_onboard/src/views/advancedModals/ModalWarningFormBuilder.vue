<template>
  <!-- modalC -->
  <span :id="'modalWarningFormBuilder'">
    <modal
      :name="'modalWarningFormBuilder'"
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
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalWarningFormBuilder')">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{Warning}}
          </h2>
          <p>
            {{FormAffectedToFiles}}
          </p>
        </div>
      </div>
      <div class="col-md-12 mb-1">
        <a
                class="bouton-sauvergarder-et-continuer-3"
                @click.prevent="duplicateProfile()"
        >{{ Duplicate }}</a>
        <a
                class="bouton-sauvergarder-et-continuer-3 w-retour"
                @click.prevent="$modal.hide('modalWarningFormBuilder')"
        >{{Retour}}</a>
      </div>
    </modal>
  </span>
</template>

<script>
import axios from "axios";
const qs = require("qs");

export default {
  name: "modalWarningFormBuilder",
  props: {
    pid: Number,
    cid: Number
  },
  data() {
    return {
      Warning: Joomla.JText._("COM_EMUNDUS_ONBOARD_WARNING"),
      FormAffectedToFiles: Joomla.JText._("COM_EMUNDUS_ONBOARD_FORM_AFFECTEDFILES"),
      Retour: Joomla.JText._("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Duplicate: Joomla.JText._("COM_EMUNDUS_ONBOARD_DUPLICATE"),
    };
  },
  methods: {
    beforeClose(event) {
    },
    beforeOpen(event) {},
    duplicateProfile() {
      axios({
        method: "post",
        url:
                "index.php?option=com_emundus_onboard&controller=form&task=duplicateform",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        data: qs.stringify({
          id: this.pid
        })
      }).then((rep) => {
        axios({
          method: "post",
          url: "index.php?option=com_emundus_onboard&controller=campaign&task=updateprofile",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          data: qs.stringify({
            profile: this.pid,
            campaign: this.cid
          })
        }).then(() => {
          window.location.replace(
                  "index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=" +
                  rep.data.data +
                  "&index=0&cid=" +
                  this.cid
          );
        });
      });
    }
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
</style>
