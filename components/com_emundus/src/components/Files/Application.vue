<template>
  <modal
      id="application-modal"
      name="application-modal"
      height="100vh"
      width="100vw"
      styles="display:flex;flex-direction:column;justify-content:center;align-items:center;"
  >
    <div class="em-modal-header em-w-100">
      <div class="em-flex-row em-pointer" id="evaluation-modal-close" @click="$modal.hide('application-modal')">
        <div class="em-w-max-content em-flex-row">
          <span class="material-icons-outlined">arrow_back</span>
        </div>
      </div>
    </div>
  </modal>
</template>

<script>
export default {
  name: "Application",
  props: {
    file: Object
  },
}
</script>

<style scoped>

</style>