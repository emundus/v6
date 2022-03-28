<template>
  <div>
    <h2>{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_DEFAULT_CONFIG') }}</h2>

    <div class="em-flex-row">
      <select v-model="selectedProfile" class="em-sm-dropdown em-mr-8">
        <option value="0">{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_CONFIG_PLEASE_SELECT') }}</option>
        <option v-for="profile in profiles" :value="profile.id">{{ profile.label }}</option>
      </select>

      <button class="em-primary-button em-w-auto">
        {{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_ADD') }}
      </button>
    </div>

    <hr>

    <div class="em-dashboard">
      <div v-for="(widget,index) in widgets" class="em-shadow-cards" :key="'widget_' + widget.id">
        <div>
          <ChartRender :widget="widget" :index="index" />
        </div>
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import dashboardService from "../../../services/dashboard";
import userService from 'com_emundus/src/services/user.js';
import Swal from "sweetalert2";
import ChartRender from "./ChartRender";

export default {
  name: "Configuration",
  components: {ChartRender},
  data() {
    return {
      loading: false,

      widgets: [],
      profiles: [],

      selectedProfile: 0,
    }
  },
  created() {
    this.getProfiles();
  },

  methods:{
    updateSaving(saving){
      this.$emit('updateSaving',saving);
    },

    updateLastSaving(date){
      this.$emit('updateLastSaving',date);
    },
    getProfiles(){
      userService.getNoApplicantProfiles().then((response) => {
        this.profiles = response;
      })
    },
    getDefaultDashoard(profile){
      dashboardService.getDefaultDashboard(profile).then((response) => {
        this.widgets = response.data.data;
      })
    }
  },

  watch: {
    selectedProfile: function(value){
      this.getDefaultDashoard(value);
    }
  }
}
</script>

<style scoped>
.em-sm-dropdown{
  height: 40px;
}
</style>
