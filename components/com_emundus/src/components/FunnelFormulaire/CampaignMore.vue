<template>
  <div>
    <h1>More</h1>
    <p>{{ campaign_id }}</p>
    <iframe
      :src="formUrl"
      width="100%"
      height="100%"
    ></iframe>
  </div>
</template>

<script>
export default {
  name: 'CampaignMore',
  props: {
    campaign_id: {
      type: Number,
      required: true
    }
  },
  data: {
    formUrl: ''
  },
  created() {
    this.getFormUrl();
  },
  methods: {
    getFormUrl() {
      this.formUrl = '/index.php?option=com_fabrik&view=form&formid=386&tmpl=component&iframe=1&rowid=';
    }
  }
}
</script>

<style scoped>

</style>