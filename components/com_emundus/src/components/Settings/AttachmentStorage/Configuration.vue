<template>
  <div>
    <h2>{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_CONFIGURATION') }}</h2>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
export default {
  name: "Configuration",
  data() {
    return {
      loading: false
    }
  },
}
</script>

<style scoped>

</style>
