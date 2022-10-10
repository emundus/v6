<template>
  <span :id="'modalEmailPreview_' + model">
    <vue-final-modal
        v-model="showModal"
        :name="'modalEmailPreview_' + model"
        @before-open="beforeOpen"
    >
      <template v-slot:title>$vfm.show</template>

      <div class="em-flex-row em-flex-space-between em-mb-16">
        <span class="em-h4">
          {{ModelPreview}}
        </span>
        <button class="em-pointer em-transparent-button" @click.prevent="$vfm.hide('modalEmailPreview_' + model)">
          <span class="material-icons-outlined">close</span>
        </button>
      </div>

      <div class="em-mb-16">
          <p v-if="email != null"><span v-html="email.message"></span></p>
      </div>

    </vue-final-modal>
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
        showModal: false
      };
    },
    methods: {
      beforeOpen() {
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
</style>
