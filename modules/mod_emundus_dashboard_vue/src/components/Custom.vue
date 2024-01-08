<template>
	<div class="tchooz-widget" :class="[selectedWidget.class]">
		<div
			class="section-sub-menu"
			style="margin-bottom: 10px"
			:class="selectedWidget.type === 'article' ? 'tchooz-widget__article-overflow' : ''"
		>
			<div id="chart-container" v-if="selectedWidget.type === 'chart'">
				<div class="tchooz-widget__selects">
					<div id="multi-filters" class="em-flex-row">
						<div v-for="(filter) in notEmptyFilters" :key="filter.key">
							<multiselect
									:id="filter.key"
									v-model="selectedFilters[filter.key]"
									class='tchooz-widget__select'
									label="label"
									track-by="value"
									:options="filter.options"
									:multiple="true"
									:taggable="false"
									:placeholder="translations.selectfilter"
									select-label=""
									selected-label=""
									deselect-label=""
									:close-on-select="true"
									:clear-on-select="false"
									:searchable="true"
									@select="onSelectFilter"
									@remove="onUnSelectFilter"
							>
							</multiselect>
						</div>
					</div>
          <select v-model="selectedWidgetId" @change="updateWidgetRender">
            <option v-for="widget in widgets" :value="widget.id">{{ widget.label }}</option>
          </select>
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
			<div v-else :class="selectedWidget.class"><div v-html="datas"></div></div>
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
		selectedWidgetId: null,
		selectedFilters: {
			'campaign_id': [],
			'status': []
		},
		filters: [],
		translations: {selectfilter: '',},
		loading: false,
		// Fusion charts variables
		datas: {},
		chart_type: 'column2d',
		renderAt: 'chart-container',
		dataFormat: 'json',
		dataSource: {},
		chart_values: [],
	}),

  created() {
    this.getTranslations();
    this.selectedWidget = this.widget;
    this.selectedWidgetId = this.widget.id;
    this.position = this.selectedWidget.position;
    this.render();
    this.getWidgets();
  },

	methods: {
		getTranslations() {
			this.translations.selectfilter = this.translate('COM_EMUNDUS_DASHBOARD_SELECT_FILTER');
		},

    render() {
      switch (this.selectedWidget.type) {
        case 'article':
          this.getArticle();
          break;
        case 'other':
          this.getEval();
          break;
        case 'chart':
          this.getFilters().then(() => {
            this.renderChart();
          });
          break;
        default:
          this.getEval();
      }
    },

		renderChart(filter = null) {
			let chartFilters = JSON.parse(JSON.stringify(this.selectedFilters));
			if (filter !== null) {
				let found = chartFilters[filter.filterKey].find((option) => {
					return option.value == filter.filterVal;
				});

				if (found == undefined) {
					chartFilters[filter.filterKey].push(filter.filterVal);
				}
			}

			this.dataSource = {};
			this.loading = true;
			axios({
				method: 'post',
				url: 'index.php?option=com_emundus&controller=dashboard&task=renderchartbytag',
				data: qs.stringify({
					widget: this.selectedWidget.id,
					filters: chartFilters,
				}),
			}).then((response) => {
					this.chart_type = this.selectedWidget.chart_type;
					this.dataSource = response.data.dataset;

					if (typeof this.dataSource.filters !== 'undefined') {
						this.dataSource.filters.forEach((filter) => {
							if (typeof this.selectedFilters[filter.key] == 'undefined' || this.selectedFilters[filter.key] == null) {
								this.selectedFilters[filter.key] = [];
							}
						});

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
				method: 'get',
				url: 'index.php?option=com_emundus&controller=dashboard&task=getarticle',
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
				method: 'get',
				url: 'index.php?option=com_emundus&controller=dashboard&task=geteval',
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
				method: 'get',
				url: 'index.php?option=com_emundus&controller=dashboard&task=getallwidgetsbysize',
				params: {
					size: this.selectedWidget.size,
				},
				paramsSerializer: (params) => {
					return qs.stringify(params);
				},
			})
				.then((response) => {
					if (response.data.data) {
						this.widgets = response.data.data;
					} else {
						this.widgets = [];
					}
				})
				.catch((error) => {
					// TODO: handle error
					this.widgets = [];
				});
		},

		updateDashboard() {
			axios({
				method: 'post',
				url: 'index.php?option=com_emundus&controller=dashboard&task=updatemydashboard',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
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
					method: 'get',
					url: 'index.php?option=com_emundus&controller=dashboard&task=getfilters',
          params: {
            widget: this.selectedWidget.id,
          },
          paramsSerializer: (params) => {
            return qs.stringify(params);
          },
				})
					.then((response) => {
						if (response.data.filters != null) {
							this.selectedFilters = response.data.filters;
						}
						resolve(true);
					})
					.catch((error) => {
						reject(error);
					});
			});
		},
    updateWidgetRender(){
      this.selectedWidget = this.widgets.find((widget) => widget.id == this.selectedWidgetId)
      if (this.chart_render !== 0) {
        this.updateDashboard();
      }
    },
		onSelectFilter(selectedOption, id) {
			if (this.chart_render !== 0) {
				this.renderChart({filterVal: selectedOption, filterKey: id});
			}
		},
		onUnSelectFilter(removedOption, id) {
			if (this.chart_render !== 0) {
				this.selectedFilters[id] = this.selectedFilters[id].filter((option) => {
					return option.value != removedOption.value;
				});
				this.renderChart();
			}
		},
	},
	computed: {
		notEmptyFilters() {
			return this.filters.filter((filter) => {
				if (typeof this.selectedFilters[filter.key] == 'undefined' || this.selectedFilters[filter.key] == null) {
					this.selectedFilters[filter.key] = [];
				}

				return filter.options.length > 0;
			});
		}
	}
};
</script>

<style scoped>
.section-sub-menu {
	display: block;
	width: 100%;
	height: 100%;
	justify-content: center;
	border-radius: var(--em-coordinator-br-cards);
	background-color: #fff;
	color: #1f1f1f;
	box-shadow: var(--em-box-shadow-x-1) var(--em-box-shadow-y-1) var(--em-box-shadow-blur-1) var(--em-box-shadow-color-1), var(--em-box-shadow-x-2) var(--em-box-shadow-y-2) var(--em-box-shadow-blur-2) var(--em-box-shadow-color-2), var(--em-box-shadow-x-3) var(--em-box-shadow-y-3) var(--em-box-shadow-blur-3) var(--em-box-shadow-color-3);
	padding: 30px;
}
</style>
