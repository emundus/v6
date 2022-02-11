<template>
  <!-- modalC -->
  <span :id="'modalEmailPreview_' + model">
    <modal
            :name="'modalEmailPreview_' + model"
            height="auto"
            transition="nice-modal-fade"
            :min-width="200"
            :min-height="200"
            :delay="100"
            :adaptive="true"
            :clickToClose="false"
            width="70%"
            @closed="beforeClose"
            @before-open="beforeOpen"
    >
      <div class="fixed-header-modal">
        <div class="update-field-header d-flex justify-content-between">
          <h2 class="update-title-header" style="margin-bottom: 10px">
             {{ModelPreview}}
          </h2>
          <div class="topright">
            <button type="button" class="btnCloseModal" @click="$modal.hide('modalEmailPreview_' + model)">
              <em class="fas fa-times"></em>
            </button>
          </div>
        </div>
        </div>

      <div class="modalC-content">
          <p v-if="email != null"><span v-html="email.message"></span></p>
      </div>
    </modal>
  </span>
</template>

<script>
  const qs = require("qs");

  export default {
    name: "modalEmailPreview",
    props: { model: String, models: Array },
    data() {
      return {
        email: null,
        ModelPreview: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_PREVIEWMODEL"),
      };
    },
    methods: {
      beforeClose(event) {
      },
      beforeOpen(event) {
        this.models.forEach(element => {
          if(element.id == this.model){
            this.email = element;
          }
        });
      },
    },
  };
</script>

<style scoped>
.description-block{
  overflow: auto;
  white-space: normal;
  height: 100%;
}
</style>
