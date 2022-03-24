<template>
  <div
      class="tchooz-widget"
      :class="[
			selectedWidget.class
		]"
  >
    <div
        class="section-sub-menu"
        :class="
				selectedWidget.type === 'article'
					? 'tchooz-widget__article-overflow'
					: ''
			"
    >
      <div v-if="selectedWidget.type === 'chart'">
        <div v-if="loading" class="lds-ring">
          <div></div>
          <div></div>
          <div></div>
          <div></div>
        </div>
        <div :id="'chartobject-' + index"></div>
      </div>
      <div v-else :class="selectedWidget.class">
        <div v-html="datas"></div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

const qs = require("qs");

export default {
  name: "ChartRender",

  components: {},

  props: {
    widget: Object,
    index: Number,
    colors: String,
  },

  data: () => ({
    selectedWidget: null,
    loading: false,

    datas: null,
  }),
  methods: {

    render() {
      switch (this.selectedWidget.type) {
        case "article":
          this.getArticle();
          break;
        case "other":
          this.getEval();
          break;
        case "chart":
          this.renderChart();
          break;
        default:
          this.getEval();
      }
    },

    renderChart() {
      this.loading = true;

      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=dashboard&task=renderchartbytag",
        data: qs.stringify({
          widget: this.selectedWidget.id,
          filters: [],
        }),
      }).then((response) => {
        this.datas = response.data.dataset;
        Highcharts.chart('chartobject-' + this.index, this.datas);
        this.loading = false;
        //
      }).catch((error) => {
        // TODO: handle error
        this.loading = false;
      });
    },

    getArticle() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=dashboard&task=getarticle",
        params: {
          widget: this.selectedWidget.id,
          article: this.selectedWidget.article_id,
        },
        paramsSerializer: (params) => {
          return qs.stringify(params);
        },
      })
          .then((response) => {
            this.datas = response.data.data;
          })
          .catch((error) => {
            // TODO: handle error
            this.datas = null;
          });
    },

    getEval() {
      axios({
        method: "get",
        url: "index.php?option=com_emundus&controller=dashboard&task=geteval",
        params: {
          widget: this.selectedWidget.id,
        },
        paramsSerializer: (params) => {
          return qs.stringify(params);
        },
      })
          .then((response) => {
            this.datas = response.data.data;
          })
          .catch((error) => {
            // TODO: handle error
            this.datas = null;
          });
    },
  },

  created() {
    this.selectedWidget = this.widget;
    this.render();
  },

  watch: {},
};
</script>

<style scoped>
.section-sub-menu {
  display: block;
  width: 100%;
  height: 250px;
  justify-content: center;
  background-color: transparent;
  color: #1f1f1f;
  padding: unset !important;
  overflow: hidden;
}

div[id^='chartobject-'] {
  height: 250px;
}
</style>
