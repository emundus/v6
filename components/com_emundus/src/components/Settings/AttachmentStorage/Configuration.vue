<template>
  <div>
    <h2>{{ translate('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_CONFIGURATION') }}</h2>

    <div class="em-flex-row">
      <ul class="nav nav-tabs topnav">

        <li v-for="(integration,index) in integrations" :key="'integration-' + index">
          <a class="em-neutral-700-color em-pointer"
             :class="currentIntegration === index ? 'w--current' : ''">
            {{ translate(integration) }}
          </a>
        </li>
      </ul>
    </div>

    <IntegrationGED v-if="currentIntegration === 0" :site="site" :level_max="level_max" @updateSaving="updateSaving"
                    @updateLastSaving="updateLastSaving"/>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import settingsService from "../../../services/settings";
import IntegrationGED from "./GED/IntegrationGED";

export default {
  name: "Configuration",
  components: {IntegrationGED},
  data() {
    return {
      loading: false,
      em_params: {},

      integrations: [],
      currentIntegration: 0,

      // GED
      site: null,
      level_max: null
    }
  },
  created() {
    this.loading = true;
    settingsService.getEmundusParams().then((params) => {
      this.em_params = params.data.config;

      if (parseInt(this.em_params.external_storage_ged_alfresco_integration) === 1) {
        this.integrations.push('COM_EMUNDUS_ONBOARD_ATTACHMENT_STORAGE_GED_ALFRESCO');
        this.site = this.em_params.external_storage_ged_alfresco_site;
        this.level_max = parseInt(this.em_params.external_storage_ged_alfresco_max_level);
      }

      this.loading = false;
    });
  },

  methods: {
    updateSaving(saving) {
      this.$emit('updateSaving', saving);
    },

    updateLastSaving(date) {
      this.$emit('updateLastSaving', date);
    }
  }
}
</script>

<style scoped>
.w--current {
  border: solid 1px #eeeeee;
  background: #eeeeee;
}
</style>
