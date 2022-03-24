<template>
  <div>
    <h2>{{ translate('COM_EMUNDUS_ONBOARD_DASHBOARD_TOOL_WIDGETS_LIBRARY') }}</h2>

    <div class="em-grid-3">
      <div v-for="(widget,index) in widgets" class="em-shadow-cards" :key="'widget_' + widget.id">
        <ChartRender :widget="widget" :index="index" />
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
}
</style>
