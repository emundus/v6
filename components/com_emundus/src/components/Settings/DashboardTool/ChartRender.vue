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
        <div :id="'chartobject-' + selectedWidget.id"></div>
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
      axios({
        method: "post",
        url: "index.php?option=com_emundus&controller=dashboard&task=renderchartbytag",
        data: qs.stringify({
          widget: this.selectedWidget.id,
          filters: [],
        }),
      }).then((response) => {
        this.datas = response.data.dataset;

        const chartConfig = {
          type: this.selectedWidget.chart_type,
          renderAt: 'chartobject-' + this.selectedWidget.id,
          width: '100%',
          height: '250',
          dataFormat: 'json',
          dataSource: response.data.dataset
        };
        FusionCharts.ready(function() {
          let fusioncharts = new FusionCharts(chartConfig);
          fusioncharts.render();
        });
        //
      }).catch((error) => {
        // TODO: handle error
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

<style lang="scss">
.tchooz-widget{
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
  .blog-featuredhomepage{
    display: none;
  }

  .g-grid{
    width: 100%;
  }

  .tchooz-widget__selects{
    display: flex;
    align-items: center;
    float: right;
    justify-content: end;
    width: 500px;
  }
  .tchooz-widget__select{
    position: relative !important;
    right: 0;
    top: 0;
    margin: 8px;
    cursor: pointer;
  }

  .tchooz-widget__article-overflow{
    overflow-y: auto;
    overflow-x: hidden;
  }

  #chart-container{
    background: white;
  }

  .fusioncharts-container svg{
    cursor: pointer !important;
  }

  /** Loader **/
  .lds-ring {
    display: inline-block;
    position: absolute;
    top: 50%;
    z-index: 999;
    right: 50%;
    width: 80px;
    height: 80px;
  }
  .lds-ring div {
    box-sizing: border-box;
    display: block;
    position: absolute;
    width: 64px;
    height: 64px;
    margin: 8px;
    border: 8px solid #20835F;
    border-radius: 50%;
    animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    border-color: #20835F transparent transparent transparent;
  }
  .lds-ring div:nth-child(1) {
    animation-delay: -0.45s;
  }
  .lds-ring div:nth-child(2) {
    animation-delay: -0.3s;
  }
  .lds-ring div:nth-child(3) {
    animation-delay: -0.15s;
  }
  @keyframes lds-ring {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  }
  /** END **/

  /** FAQ **/
  .custom-faq-widget{
    max-width: 330px !important;
    margin: 0 30px 30px 0 !important;
    height: 100%;
  }

  /* brother element of faq-widget */
  .custom-faq-widget + div {
    width: calc(100% - 360px) !important;
  }


  .faq-widget .faq-intro {
    max-width: 250px;
  }

  .faq-widget {
    height: 100%;
  }

  .faq-widget > div {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .faq-widget .bouton-faq{
    padding: 5px 30px;
    height: 29px;
    border-radius: 25px;
    border: 2px solid #16afe1;
    background-color: #16afe1;
    transition: color .2s ease,background-color .2s cubic-bezier(.55,.085,.68,.53);
    color: #fff;
    text-decoration: none;
    width: auto;
    display: flex;
    align-items: center;
    font-size: 14px;
    font-weight: 500;
    bottom: 30px;
  }
  .faq-widget .bouton-faq:hover{
    cursor: pointer;
    background-color: transparent;
    color: #16afe1;
  }

  .faq-widget h3{
    margin-bottom: 15px;
    color: #000;
    font-size: 24px;
  }

  /** END **/

  /** Files by status - number **/
  .widget-files-status-number .widget-files-status-number-block{
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10%;
  }
}

</style>
