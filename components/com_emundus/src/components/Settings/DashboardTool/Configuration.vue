<template>
  <div>
    <h2>{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_DEFAULT_CONFIG') }}</h2>

    <div class="em-flex-row">
      <select v-model="selectedProfile" class="em-sm-dropdown em-mr-8">
        <option value="0">{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_CONFIG_PLEASE_SELECT') }}</option>
        <option v-for="profile in profiles" :value="profile.id">{{ profile.label }}</option>
      </select>

      <button class="em-primary-button em-w-auto" v-if="selectedProfile" @click="addWidget">
        {{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_ADD') }}
      </button>
    </div>

    <hr>

    <div class="em-dashboard">
      <draggable
          handle=".handle"
          class="groups-block"
          v-model="widgets"
          v-bind="dragOptions">
        <transition-group type="transition" :value="!drag ? 'flip-list' : null" style="display: block;min-height: 200px">
          <div v-for="(widget,index) in widgets" class="em-shadow-cards handle em-grab" style="height: auto" :key="'widget_' + widget.id">
            <span class="material-icons-outlined em-pointer">remove_circle_outline</span>
            <div>
              <ChartRender :widget="widget" :index="index" />
            </div>
          </div>
        </transition-group>
      </draggable>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import dashboardService from "../../../services/dashboard";
import userService from 'com_emundus/src/services/user.js';
import Swal from "sweetalert2";
import ChartRender from "./ChartRender";
import draggable from "vuedraggable";


export default {
  name: "Configuration",
  components: {ChartRender,draggable},
  data() {
    return {
      loading: false,
      drag: false,

      widgets: [],
      widgets_by_profile: [],
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
    },
    getWidgetsByProfile(profile){
      dashboardService.getWidgetsByProfile(profile).then((response) => {
        this.widgets_by_profile = response.data.data;
      })
    },
    addWidget(){
      let select = '<select id="select_new_widget" class="em-sm-dropdown">'
      select += '<option value="0">' + this.translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_CONFIG_PLEASE_SELECT') + '</option>';
      for(const widget of this.widgets_by_profile){
        select += '<option value="' + widget.id + '">' + widget.label + '</option>';
      }
      select += '</select>';

      Swal.fire({
        title: this.translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_ADD'),
        html : select,
        showCancelButton: true,
        confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
        cancelButtonText: this.translate("COM_EMUNDUS_ONBOARD_CANCEL"),
        reverseButtons: true,
        customClass: {
          title: 'em-swal-title',
          cancelButton: 'em-swal-cancel-button',
          confirmButton: 'em-swal-confirm-button',
        },
      }).then(result => {
        if (result.value) {
          const value = document.getElementById('select_new_widget').value;

          dashboardService.addDefaultWidget(value,this.selectedProfile).then(() => {
            this.getDefaultDashoard(this.selectedProfile);
          });
        }
      });
    }
  },

  watch: {
    selectedProfile: function(value){
      this.getDefaultDashoard(value);
      this.getWidgetsByProfile(value);
    }
  },

  computed: {
    dragOptions() {
      return {
        group: {
          name: "documents",
          put: false
        },
        animation: 200,
        sort: true,
        disabled: false,
        ghostClass: "ghost"
      };
    },
  }
}
</script>

<style scoped>
.em-sm-dropdown{
  height: 40px;
}
</style>
