<template>
  <!-- modalC -->
  <span :id="'modalEmailPreview'">
    <modal
            :name="'modalEmailPreview'"
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
      <div class="modalC-content">
        <div class="update-field-header">
          <div class="topright">
            <button type="button" class="btnCloseModal" @click.prevent="$modal.hide('modalEmailPreview')">
              <em class="fas fa-times-circle"></em>
            </button>
          </div>
          <h2 class="update-title-header">
             {{ModelPreview}}
          </h2>
          <p class="description-block" v-if="email != null"><span v-html="email.message"></span></p>
        </div>
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
