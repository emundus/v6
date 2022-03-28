<template>
  <div>
    <h2>{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_LIBRARY') }}</h2>

    <button class="em-primary-button em-w-auto em-mb-32" @click="openWidgetParameters('')">
      {{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_ADD') }}
    </button>
    <WidgetSettings :profiles="profiles" />

    <div class="em-grid-2">
      <div v-for="(widget,index) in widgets" class="em-shadow-cards" @mouseover="show_title = widget.id" @mouseleave="show_title = 0" :key="'widget_' + widget.id">
        <div class="em-flex-row em-widget-actions" v-show="show_title == widget.id">
          <span class="em-widget-title">{{ translate(widget.label) }}</span>
          <div>
            <span class="material-icons-outlined em-mr-16" :title="translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_EDIT')" @click="openWidgetParameters(widget.id)">edit</span>
            <span class="material-icons em-red-500-color" :title="translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_DELETE')" @click="deleteWidget(widget.id)">delete_outline</span>
          </div>
        </div>
        <div class="em-hover-blur">
          <ChartRender :widget="widget" :index="index" />
        </div>
        <WidgetSettings :widget="widget" :profiles="profiles" :key="'widget_' + widget.id" />
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import dashboardService from "../../../services/dashboard";
import ChartRender from "./ChartRender";
import WidgetSettings from "./WidgetSettings";
import userService from 'com_emundus/src/services/user.js';
import Swal from "sweetalert2";

export default {
  name: "Widgets",
  components: {WidgetSettings, ChartRender},
  data() {
    return {
      loading: false,

      widgets: [],
      profiles: [],
      show_title: 0
    }
  },
  created() {
    this.loading = true;
    dashboardService.getWidgets().then((response) => {
      this.widgets = response.data.data;
      this.loading = false;
    })
    this.getProfiles();
  },

  methods:{
    updateSaving(saving){
      this.$emit('updateSaving',saving);
    },

    updateLastSaving(date){
      this.$emit('updateLastSaving',date);
    },
    openWidgetParameters(id){
      this.$modal.show('modalWidgetSettings' + id);
    },
    getProfiles(){
      userService.getNoApplicantProfiles().then((response) => {
        this.profiles = response;
      })
    },
    deleteWidget(widget_id){
      Swal.fire({
        title: this.translate("COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_DELETE"),
        text: this.translate("COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_CONFIRM_DELETE"),
        type: "warning",
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
          dashboardService.deleteWidget(widget_id).then((response) => {
            let widget_found = this.widgets.findIndex((widget, index) => {
              if(widget.id === widget_id)
                return true;
            });
            this.widgets.splice(widget_found,1);
          });
        }
      });
    }
  }
}
</script>

<style scoped>
.em-shadow-cards{
  margin: unset;
  height: 300px;
  width: 35vw;
  padding: unset !important;
  position: relative;
}
.em-hover-blur{
  padding: 24px 32px;
  transition: all 0.3s ease-in-out;
}
.em-hover-blur:hover {
  filter: blur(2px);
}
.em-widget-title{
  font-size: 24px;
}
.em-widget-actions{
  position: absolute;
  justify-content: space-between;
  width: 100%;
  padding: 16px;
  transition: all 0.3s ease-in-out;
  z-index: 10;
}
</style>
