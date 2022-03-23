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

    <div class="fixed-header-modal">
      <div class="topright">
          <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalWarningFormBuilder')">
            <em class="fas fa-times"></em>
          </button>
      </div>
      <div class="update-field-header">
        <h2 class="update-title-header">
          {{Warning}}
        </h2>
        <p>
          {{FormAffectedToFiles}}
        </p>
      </div>

      <div class="modalC-content">
        <div class="d-flex justify-content-between mb-1">
          <button class="bouton-sauvergarder-et-continuer w-retour"
                  type="button"
                  @click.prevent="$modal.hide('modalWarningFormBuilder')">
            {{Retour}}
          </button>
            <button class="bouton-sauvergarder-et-continuer mr-1"
                    type="button"
                    @click.prevent="formbuilder()">
            {{ Update }}
          </button>
        </div>
      </div>
    </div>

    </modal>
  </span>
</template>

<script>
const qs = require("qs");

export default {
  name: "modalWarningFormBuilder",
  props: {
    pid: Number,
    cid: Number
  },
  data() {
    return {
      Warning: this.translate("COM_EMUNDUS_ONBOARD_WARNING"),
      FormAffectedToFiles: this.translate("COM_EMUNDUS_ONBOARD_FORM_AFFECTEDFILES"),
      Retour: this.translate("COM_EMUNDUS_ONBOARD_ADD_RETOUR"),
      Duplicate: this.translate("COM_EMUNDUS_ONBOARD_DUPLICATE"),
      Update: this.translate("COM_EMUNDUS_ONBOARD_MODIFY"),
    };
  },
  methods: {
    beforeClose(event) {
    },
    beforeOpen(event) {},
    /*duplicateProfile() {
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
    },*/

    formbuilder(){
      window.location.replace(
              "index.php?option=com_emundus_onboard&view=form&layout=formbuilder&prid=" +
              this.pid +
              "&index=0&cid=" +
              this.cid
      );
    },
  },
};
</script>

<style scoped>
.modalC-content{
  margin-top: 0;
}
</style>
