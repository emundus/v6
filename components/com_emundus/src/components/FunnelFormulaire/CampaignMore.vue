<template>
  <div>
    <iframe
        v-if="formUrl.length > 0"
        id="more-form-iframe"
        :src="formUrl"
        width="100%"
    ></iframe>
  </div>
</template>

<script>
import campaignService from '@/services/campaign';

export default {
  name: 'CampaignMore',
  props: {
    campaignId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      formUrl: ''
    }
  },
  created() {
    this.getFormUrl();
  },
  methods: {
    getFormUrl() {
      campaignService.getCampaignMoreFormUrl(this.campaignId)
        .then(response => {
          if (response.status) {
            this.formUrl = response.data;
          }
        })
        .catch(error => {
          console.error(error);
        });
    }
  }
}
</script>

<style scoped>
#more-form-iframe {
  height: 50vh;
}
</style>