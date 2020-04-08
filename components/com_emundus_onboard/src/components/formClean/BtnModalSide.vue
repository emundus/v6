<template>
  <!-- modalBTN 
  -->
  <span
    id="BtnModalSide"
    class="BtnModal"
    @click="$modal.show('modalSide' + IDs)"
    data-toggle="tooltip"
    data-placement="top"
    :title="sidemenuhelp"
  >
    <em class="fas fa-cog"></em>
  </span>
</template>

<script>
export default {
  name: "BtnModalSide",
  props: {
    IDs: Number
  },
  data() {
    return {
      sidemenuhelp: Joomla.JText._("COM_EMUNDUS_ONBOARD_SIDEMENUHELP")
    };
  }
};
</script>
<style scoped>
.BtnModal {
  margin-right: 7px !important;
}
</style>
