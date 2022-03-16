<template>
  <div>
    <h2>{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_STORAGE') }}</h2>

    <table>
      <thead>
        <tr>
          <th>{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_DOCTYPE') }}</th>
          <th>{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_STATUS') }}</th>
          <th>{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_STORAGE_TYPE') }}</th>
          <th>{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_SYNCHRO') }}</th>
        </tr>
      </thead>
    </table>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
export default {
  name: "Storage",
  data() {
    return {
      loading: false
    }
  },
}
</script>

<style scoped>
table{
  border: unset;
}
th{
  background: unset;
  border-bottom: 1px solid #ddd;
}
</style>
