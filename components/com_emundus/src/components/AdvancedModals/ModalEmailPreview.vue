<template>
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
        @before-open="beforeOpen"
    >

      <div class="em-flex-row em-flex-space-between em-mb-16">
        <h4>
          {{ ModelPreview }}
        </h4>
        <button class="em-pointer em-transparent-button" @click.prevent="$modal.hide('modalEmailPreview_' + model)">
          <span class="material-icons-outlined">close</span>
        </button>
      </div>

      <div class="em-mb-16">
          <p v-if="email != null"><span v-html="email.message"></span></p>
      </div>

    </modal>
  </span>
</template>

<script>
const qs = require("qs");

export default {
  name: "modalEmailPreview",
  props: {model: String, models: Array},
  data() {
    return {
      email: null,
      ModelPreview: this.translate("COM_EMUNDUS_ONBOARD_EMAIL_PREVIEWMODEL"),
    };
  },
  methods: {
    beforeOpen() {
      this.models.forEach(element => {
        if (element.id == this.model) {
          this.email = element;
        }
      });
    },
  },
};
</script>

<style scoped>
</style>
