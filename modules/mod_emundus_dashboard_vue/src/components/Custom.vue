<template>
	<div
		class="tchooz-widget"
		:class="[
			'col-md-' + selectedWidget.size,
			'col-sm-' + selectedWidget.size_small,
			selectedWidget.class,
		]"
	>
		<div
			class="section-sub-menu"
			style="margin-bottom: 10px"
			:class="
				selectedWidget.type === 'article'
					? 'tchooz-widget__article-overflow'
					: ''
			"
		>
			<div id="chart-container" v-if="selectedWidget.type === 'chart'">
				<div class="tchooz-widget__selects">
					<multiselect
						v-if="filters.length > 0"
						v-model="selectedFilters"
						:class="'tchooz-widget__select'"
						label="label"
						track-by="value"
						:options="filters"
						:multiple="true"
						:taggable="false"
						:placeholder="translations.selectfilter"
						select-label=""
						selected-label=""
						deselect-label=""
						:close-on-select="true"
						:clear-on-select="false"
						:searchable="true"
					></multiselect>
					<multiselect
						v-model="selectedWidget"
						:class="'tchooz-widget__select'"
						label="label"
						track-by="id"
						:options="widgets"
						:multiple="false"
						:taggable="false"
						select-label=""
						selected-label=""
						deselect-label=""
						:close-on-select="true"
						:clear-on-select="false"
						:searchable="false"
					></multiselect>
				</div>
				<div v-if="loading" class="lds-ring">
					<div></div>
					<div></div>
					<div></div>
					<div></div>
				</div>
				<fusioncharts
					v-if="chart_render !== 0"
					:key="chart_render"
					:type="chart_type"
					:width="'100%'"
					:height="'300'"
					:dataFormat="dataFormat"
					:dataSource="dataSource"
				>
				</fusioncharts>
			</div>
			<div v-else :class="selectedWidget.class">
				<div v-html="datas"></div>
			</div>
		</div>
	</div>
</template>

<script>
import axios from "axios";
import Multiselect from "vue-multiselect";

const qs = require("qs");

export default {
	name: "Custom",

	components: {
		Multiselect,
	},

	props: {
		widget: Object,
		colors: String,
	},

	data: () => ({
		widgets: [],
		chart_render: 0,
		position: null,
		selectedWidget: null,
		selectedFilters: [],
		filters: [],
		translations: {
			selectfilter: "",
		},
		loading: false,
		// Fusion charts variables
		datas: {},
		chart_type: "column2d",
		renderAt: "chart-container",
		dataFormat: "json",
		dataSource: {},
		chart_values: [],
	}),
	methods: {
		getTranslations() {
			this.translations.selectfilter = this.translate(
				"COM_EMUNDUS_DASHBOARD_SELECT_FILTER"
			);
		},

    render() {
      switch (this.selectedWidget.type) {
        case "article":
          this.getArticle();
          break;
        case "other":
          this.getEval();
          break;
        case "chart":
          this.getFilters().then(() => {
            this.renderChart();
          });
          break;
        default:
          this.getEval();
      }
    },

		renderChart() {
			this.dataSource = {};
			this.loading = true;
			axios({
				method: "post",
				url: "index.php?option=com_emundus_onboard&controller=dashboard&task=renderchartbytag",
				data: qs.stringify({
					widget: this.selectedWidget.id,
					filters: this.selectedFilters,
				}),
			})
				.then((response) => {
					this.chart_type = this.selectedWidget.chart_type;
					this.dataSource = response.data.dataset;
					if (typeof this.dataSource.filters !== "undefined") {
						this.filters = this.dataSource.filters;
					}

					this.chart_render++;
					this.loading = false;
					//
				})
				.catch((error) => {
					// TODO: handle error
					this.loading = false;
				});
		},

		getArticle() {
			axios({
				method: "get",
				url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getarticle",
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
					this.datas = {};
				});
		},

		getEval() {
			axios({
				method: "get",
				url: "index.php?option=com_emundus_onboard&controller=dashboard&task=geteval",
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
					this.datas = {};
				});
		},

		getWidgets() {
			axios({
				method: "get",
				url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getallwidgetsbysize",
				params: {
					size: this.selectedWidget.size,
				},
				paramsSerializer: (params) => {
					return qs.stringify(params);
				},
			})
				.then((response) => {
					this.widgets = response.data.data;
				})
				.catch((error) => {
					// TODO: handle error
					this.widgets = [];
				});
		},

		updateDashboard() {
			axios({
				method: "post",
				url: "index.php?option=com_emundus_onboard&controller=dashboard&task=updatemydashboard",
				headers: {
					"Content-Type": "application/x-www-form-urlencoded",
				},
				data: qs.stringify({
					widget: this.selectedWidget.id,
					position: this.position,
				}),
			})
				.then(() => {
          this.render();
				})
				.catch((error) => {
					// TODO: handle error
				});
		},

		async getFilters() {
			return new Promise((resolve, reject) => {
				axios({
					method: "get",
					url: "index.php?option=com_emundus_onboard&controller=dashboard&task=getfilters",
          params: {
            widget: this.selectedWidget.id,
          },
          paramsSerializer: (params) => {
            return qs.stringify(params);
          },
				})
					.then((response) => {
						this.selectedFilters = response.data.filters;
						resolve(true);
					})
					.catch((error) => {
						reject(error);
					});
			});
		},
	},

	created() {
		this.getTranslations();
		this.selectedWidget = this.widget;
		this.position = this.selectedWidget.position;
    this.render();
		this.getWidgets();
	},

	watch: {
		selectedWidget: function () {
			if (this.chart_render !== 0) {
				this.updateDashboard();
			}
		},

		selectedFilters: function () {
			if (this.chart_render !== 0) {
				this.renderChart();
			}
		},
	},
};
</script>

<style scoped>
.section-sub-menu {
	display: block;
	width: 100%;
	height: 100%;
	justify-content: center;
	border-radius: 4px;
	background-color: #fff;
	color: #1f1f1f;
	box-shadow: 0 1px 2px 0 hsla(0, 0%, 41.2%, 0.19);
	padding: 30px;
}
</style>
