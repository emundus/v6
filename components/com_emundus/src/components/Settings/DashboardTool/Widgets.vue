<template>
  <div>
    <h2>{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_LIBRARY') }}</h2>

    <div class="em-grid-2">
      <div v-for="(widget,index) in widgets" class="em-shadow-cards" @mouseover="show_title = widget.id" @mouseleave="show_title = 0" :key="'widget_' + widget.id">
        <ChartRender :widget="widget" :index="index" />
        <span v-if="show_title == widget.id">{{ translate(widget.label) }}</span>
      </div>
    </div>

    <div class="em-page-loader" v-if="loading"></div>
  </div>
</template>

<script>
import dashboardService from "../../../services/dashboard";
import ChartRender from "./ChartRender";

export default {
  name: "Widgets",
  components: {ChartRender},
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
    }
  }
}
</script>

<style scoped>
.em-shadow-cards{
  margin: unset;
  height: 300px;
  width: 35vw;
  transition: all 0.3s ease-in-out;
}
.em-shadow-cards:hover {
  filter: blur(1px);
  box-shadow: inset 0 0 10px 1px #E3E5E8;
}
</style>
