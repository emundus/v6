<template>
  <div>
    <h2>{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_LIBRARY') }}</h2>

    <button class="em-primary-button em-w-auto em-mb-32">
      {{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_ADD') }}
    </button>

    <div class="em-grid-2">
      <div v-for="(widget,index) in widgets" class="em-shadow-cards" @click="openWidgetParameters(widget.id)" @mouseover="show_title = widget.id" @mouseleave="show_title = 0" :key="'widget_' + widget.id">
        <span class="em-widget-title" v-show="show_title == widget.id">{{ translate(widget.label) }}</span>
        <div class="em-hover-blur">
          <ChartRender :widget="widget" :index="index" />
        </div>
        <WidgetSettings :widget="widget" />
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import dashboardService from "../../../services/dashboard";
import ChartRender from "./ChartRender";
import WidgetSettings from "./WidgetSettings";

export default {
  name: "Widgets",
  components: {WidgetSettings, ChartRender},
  data() {
    return {
      loading: false,

      widgets: [],
      show_title: 0
    }
  },
  created() {
    this.loading = true;
    dashboardService.getWidgets().then((response) => {
      this.widgets = response.data.data;
      this.loading = false;
    })
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
  box-shadow: inset 0 0 10px 1px #E3E5E8;
}
.em-widget-title{
  position: absolute;
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  transition: all 0.3s ease-in-out;
  font-size: 24px;
}
</style>
