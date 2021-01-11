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
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalEmailPreview_' + model)">
              <em class="fas fa-times"></em>
            </button>
          </div>
                        <div class="update-field-header">
          <h2 class="update-title-header">
             {{ModelPreview}}
          </h2>
                        </div>
        </div>

      <div class="modalC-content">
          <p class="description-block" v-if="email != null"><span v-html="email.message"></span></p>
      </div>
    </modal>
  </span>
</template>

<script>
  const qs = require("qs");

  export default {
    name: "modalEmailPreview",
    props: { model: Number, models: Array },
    data() {
      return {
        email: null,
        ModelPreview: Joomla.JText._("COM_EMUNDUS_ONBOARD_EMAIL_PREVIEWMODEL"),
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
