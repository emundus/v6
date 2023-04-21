<template>
	<div id="onboarding_list" class="em-w-100">
		<skeleton v-if="loading.lists" height="40px" width="100%" class="em-mb-16 em-mt-16 em-border-radius-8"></skeleton>
		<div v-else class="head em-flex-row em-flex-space-between em-mb-16 em-mt-16">
			<h2 style="margin:0;">{{ translate(currentList.title) }}</h2>
			<a v-if="addAction" id="add-action-btn" class="em-primary-button em-w-auto em-pointer" @click="onClickAction(addAction)">{{ translate(addAction.label) }}</a>
		</div>
		<hr class="em-w-100">

		<div v-if="loading.tabs" id="tabs-loading">
			<div class="em-flex-row em-flex-space-between">
				<skeleton height="40px" width="20%" class="em-mb-16 em-border-radius-8"></skeleton>
				<skeleton height="40px" width="5%" class="em-mb-16 em-border-radius-8"></skeleton>
			</div>
			<div :class="{'skeleton-grid': viewType === 'blocs','em-flex-column': viewType === 'list'}" style="flex-wrap: wrap">
				<skeleton v-for="i in 9" :key="i" class="em-border-radius-8 skeleton-item"></skeleton>
			</div>
		</div>
		<div v-else class="list">
			<section id="pagination-wrapper" class="em-flex-row em-flex-space-between">
				<select name="numberOfItemsToDisplay" v-model="numberOfItemsToDisplay" @change="getListItems()" class='em-mt-16 em-mb-16 em-default-input'>
					<option value='10'>{{ translate('COM_EMUNDUS_ONBOARD_RESULTS') }} 10</option>
					<option value='25'>{{ translate('COM_EMUNDUS_ONBOARD_RESULTS') }} 25</option>
					<option value='50'>{{ translate('COM_EMUNDUS_ONBOARD_RESULTS') }} 50</option>
					<option value='all'>{{ translate('ALL') }}</option>
				</select>
				<div v-if="typeof currentTab.pagination !== undefined && currentTab.pagination && currentTab.pagination.total > 1" id="pagination" class="em-text-align-center">
					<ul class="em-flex-row em-flex-center" style="list-style-type:none;margin-left:0;">
					<span :class="{'em-text-neutral-600 em-disabled-events': currentTab.pagination.current === 1}"
					      class="material-icons-outlined em-pointer em-mr-8"
					      @click="getListItems(currentTab.pagination.current - 1, selectedListTab)">chevron_left</span>
						<li v-for="i in currentTab.pagination.total" :key="i"
						    class="em-pointer em-square-button"
						    :class="{'active': i === currentTab.pagination.current}"
						    @click="getListItems(i, selectedListTab)">
							{{ i }}
						</li>
						<span :class="{'em-text-neutral-600 em-disabled-events': currentTab.pagination.current === currentTab.pagination.total}"
						      class="material-icons-outlined em-pointer em-ml-8"
						      @click="getListItems(currentTab.pagination.current + 1, selectedListTab)">chevron_right</span>
					</ul>
				</div>
			</section>
			<nav v-if="currentList.tabs.length > 1" id="list-nav">
				<ul style="list-style-type: none;margin-left:0;" class="em-flex-row">
					<li v-for="tab in currentList.tabs" :key="tab.key"
					    class="em-pointer em-p-8 em-font-weight-400 em-p-16"
					    :class="{
								'em-border-bottom-main-500 em-neutral-900-color ': selectedListTab === tab.key,
							  'em-neutral-700-color em-border-bottom-neutral-300': selectedListTab !== tab.key
							}"
					    @click="selectedListTab = tab.key"
					>
						{{ translate(tab.title) }}
					</li>
				</ul>
			</nav>
			<section id="actions" class="em-flex-row em-flex-space-between em-mt-16 em-mb-16">
				<section id="tab-actions">
					<select v-for="filter in filters[selectedListTab]" :key="selectedListTab + '-' + filter.key" v-model="filter.value" @change="onChangeFilter(filter)" class="em-default-input em-mr-8">
						<option v-for="option in filter.options" :key="option.value" :value="option.value">{{ translate(option.label) }}</option>
					</select>
				</section>

				<section id="default-actions" class="em-flex-row">
					<div class="em-flex-row em-flex-row-center">
						<input name="search" type="text" v-model="searches[selectedListTab].search"
						       :placeholder="translate('COM_EMUNDUS_ONBOARD_SEARCH')"
						       class="em-border-radius-8 em-default-input"
						       :class="{'em-disabled-events': items[this.selectedListTab].length < 1 && searches[selectedListTab].search === ''}" style="margin: 0;"
						       :disabled="items[this.selectedListTab].length < 1 && searches[selectedListTab].search === ''"
						       @change="searchItems" @keyup="searchItems">
						<span class="material-icons-outlined em-mr-8 em-pointer" style="margin-left: -32px" @click="searchItems">
							search
						</span>
					</div>
					<div class="view-type">
					<span v-for="viewTypeOption in viewTypeOptions" :key="viewTypeOption.value"
					      style="padding: 4px;border-radius: 4px;"
					      class="material-icons-outlined em-pointer em-ml-8"
					      :class="{
								'active em-main-500-color em-border-main-500': viewTypeOption.value === viewType,
								'em-neutral-300-color em-border-neutral-300': viewTypeOption.value !== viewType
							}"
					      @click="changeViewType(viewTypeOption)"
					>{{ viewTypeOption.icon }}</span>
					</div>
				</section>
			</section>

			<div v-if="loading.items"
			     id="items-loading"
			     :class="{'skeleton-grid': viewType === 'blocs','em-flex-column em-mb-16': viewType === 'list'}"
			     style="flex-wrap: wrap"
			>
				<skeleton v-for="i in 9" :key="i" class="em-border-radius-8 skeleton-item"></skeleton>
			</div>
			<div v-else>
				<div v-if="displayedItems.length > 0" id="list-items">
					<table id="list-table" :class="{'blocs': viewType === 'blocs'}">
						<thead>
							<tr>
								<th>{{ translate('COM_EMUNDUS_ONBOARD_LABEL') }}</th>
								<th v-for="column in additionalColumns" :key="column"> {{ column }}</th>
								<th v-if="tabActionsPopover && tabActionsPopover.length > 0">{{ translate('COM_EMUNDUS_ONBOARD_ACTIONS') }}</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="item in displayedItems" :key="item.id"
							    class="em-border-bottom-neutral-300"
							    :class="{'em-card-neutral-100 em-card-shadow em-p-24' : viewType === 'blocs'}"
							>
								<td class="em-pointer" @click="onClickAction(editAction, item.id)">
									<span :class="{'em-font-weight-600 em-mb-16':  viewType === 'blocs'}">{{ item.label[params.shortlang] }}</span>
								</td>
								<td class="columns" v-for="column in item.additional_columns" :key="column.key" v-if="column.display === viewType || column.display === 'all'">
									<div v-if="column.type === 'tags'" class="em-flex-row em-flex-wrap" :class="column.classes">
										<span v-for="tag in column.values" :key="tag.key" class="em-mr-8" :class="tag.classes">{{ tag.value }}</span>
									</div>
									<span v-else class="em-mt-8 em-mb-8" :class="column.classes">
										{{ column.value }}
									</span>
								</td>
								<div>
									<hr v-if="viewType === 'blocs'" class="em-w-100">
									<td class="actions">
										<a v-if="viewType === 'blocs' && editAction" @click="onClickAction(editAction, item.id)" class="em-primary-button em-font-size-14 em-pointer em-w-auto">
											{{ translate(editAction.label) }}
										</a>
										<div class="em-flex-row">
											<span v-if="previewAction" class="material-icons-outlined em-pointer" @click="onClickPreview(item)">visibility</span>
											<v-popover v-if="tabActionsPopover && tabActionsPopover.length > 0 && filterShowOnActions(tabActionsPopover, item).length" :popoverArrowClass="'custom-popover-arrow'">
												<span class="tooltip-target b3 material-icons">more_vert</span>
												<template slot="popover">
													<ul style="list-style-type: none; margin: 0;">
														<li v-for="action in tabActionsPopover"
														    :key="action.action"
														    :class="{'hidden': !(typeof action.showon === 'undefined' || evaluateShowOn(item, action.showon))}"
														    @click="onClickAction(action, item.id)"
														    class="em-pointer em-p-8 em-font-weight-600"
														>
															{{ translate(action.label) }}
														</li>
													</ul>
												</template>
											</v-popover>
										</div>
									</td>
								</div>
							</tr>
						</tbody>
					</table>
				</div>
				<p v-else id="empty-list">{{ translate('COM_EMUNDUS_ONBOARD_EMPTY_LIST') }}</p>
			</div>
		</div>
	</div>
</template>

<script>
import Vue from 'vue';

// Components
import Skeleton from '../components/Skeleton.vue';

// Services
import settingsService from '../services/settings.js';
import client from '../services/axiosClient';
import Swal from 'sweetalert2';
import Section from "../components/Users/Section";

export default {
	name: 'list_v2',
	components: {
		Section,
		Skeleton
	},
	props: {
		defaultType: {
			type: String,
			default: null
		}
	},
	data() {
		return {
			loading: {
				'lists': false,
				'tabs': false,
				'items': false,
			},
			numberOfItemsToDisplay: 25,
			lists: {},
			type: 'forms',
			params: {},
			currentList: {'title': '', 'tabs': []},
			selectedListTab: 0,
			items: {},
			title: '',
			viewType: 'table',
			viewTypeOptions: [{value: 'table', icon: 'dehaze'}, {value: 'blocs', icon: 'grid_view'}],
			searches: {},
			filters: {}
		}
	},
	created() {
		this.loading.lists = true;
		this.loading.tabs = true;

		if (this.defaultType !== null) {
			this.params = {
				'type': this.defaultType
			};
		} else {
			const data = this.$store.getters['global/datas'];
			this.params = Object.assign({}, ...Array.from(data).map(({name, value}) => ({[name]: value})));
		}
		this.type = this.params.type;

		this.viewType = localStorage.getItem('tchooz_view_type/' + document.location.hostname)
		if (this.viewType === null || typeof this.viewType === 'undefined' || (this.viewType !== 'blocs' && this.viewType !== 'table')) {
			this.viewType = 'blocs';
			localStorage.setItem('tchooz_view_type/' + document.location.hostname,'blocs');
		}
		const storageNbItemsDisplay = localStorage.getItem('tchooz_number_of_items_to_display/' + document.location.hostname);
		if (storageNbItemsDisplay !== null) {
			this.numberOfItemsToDisplay = parseInt(storageNbItemsDisplay);
		}

		this.initList();
	},
	methods: {
		initList() {
			let lists = localStorage.getItem('tchooz_lists/' + document.location.hostname);

			if (lists !== null) {
				lists = atob(lists);
				this.lists = JSON.parse(lists);
				if (typeof this.lists[this.type] === 'undefined') {
					console.error('List type ' + this.type + ' does not exist');
					window.location.href = '/';
				}

				this.currentList = this.lists[this.type];
				if (this.params.hasOwnProperty('tab')) {
					this.selectedListTab = this.params.tab;
				} else {
					this.selectedListTab = this.currentList.tabs[0].key;
				}

				this.loading.lists = false;
				this.getListItems();
			} else {
				this.getLists();
			}
		},
		getLists() {
			settingsService.getOnboardingLists().then(response => {
				if (response.data.status) {
					this.lists = response.data.data;
					localStorage.setItem('tchooz_lists/' + document.location.hostname, btoa(JSON.stringify(this.lists)));

					if (typeof this.lists[this.type] === 'undefined') {
						console.error('List type ' + this.type + ' does not exist');
						window.location.href = '/';
					}

					this.currentList = this.lists[this.type];
					if (this.params.hasOwnProperty('tab')) {
						this.selectedListTab = this.params.tab;
					} else {
						this.selectedListTab = this.currentList.tabs[0].key;
					}

					this.loading.lists = false;

					this.getListItems();
				} else {
					console.error('Error while getting onboarding lists');
					window.location.href = '/';
					this.loading.lists = false;
				}
			});
		},
		getListItems(page = 1, tab = null) {
			if (tab === null) {
				this.loading.tabs = true;
				this.items = Vue.observable(Object.assign({}, ...this.currentList.tabs.map(tab => ({[tab.key]: []}))));
			} else {
				this.loading.items = true;
			}

			const tabs = tab === null ? this.currentList.tabs : [this.currentTab];
			if (tabs.length > 0) {
				tabs.forEach(tab => {
					if (typeof this.searches[tab.key] === 'undefined') {
						this.searches[tab.key] = {
							search: '',
							lastSearch: '',
							debounce: null
						};
					}
					this.setTabFilters(tab);
					if (typeof tab.getter !== 'undefined') {
						let url = 'index.php?option=com_emundus&controller=' + tab.controller + '&task=' + tab.getter + '&lim=' + this.numberOfItemsToDisplay + '&page=' + page;
						if (this.searches[tab.key].search !== '') {
							url += '&recherche=' + this.searches[tab.key].search;
						}
						if (typeof this.filters[tab.key] !== 'undefined') {
							this.filters[tab.key].forEach(filter => {
								if (filter.value !== '' && filter.value !== 'all') {
									url += '&' + filter.key + '=' + filter.value;
								}
							});
						}

						try {
							client().get(url)
									.then(response => {
										if (response.data.status === true) {
											if (typeof response.data.data.datas !== 'undefined') {
												this.items[tab.key] = response.data.data.datas;
												tab.pagination = {
													current: page,
													total: Math.ceil(response.data.data.count / this.numberOfItemsToDisplay)
												}
											}
										} else {
											console.error('Failed to get data : ' + response.data.data.msg);
										}
										this.loading.tabs = false;
										this.loading.items = false;
									})
									.catch(error => {
										console.error(error);
										this.loading.tabs = false;
										this.loading.items = false;
									});
						} catch (e) {
							console.error(e);
							this.loading.tabs = false;
							this.loading.items = false;
						}
					} else {
						this.loading.tabs = false;
						this.loading.items = false;
					}
				});
			} else {
				this.loading.tabs = false;
				this.loading.items = false;
			}
		},
		setTabFilters(tab) {
			if (typeof tab.filters !== 'undefined' && tab.filters.length > 0) {
				if (typeof this.filters[tab.key] === 'undefined') {
					this.filters[tab.key] = [];

					tab.filters.forEach(filter => {
						if (filter.values === null) {
							if (filter.getter) {
								const controller = typeof filter.controller !== 'undefined' ? filter.controller : tab.controller;
								client().get('index.php?option=com_emundus&controller=' + controller + '&task=' + filter.getter)
										.then(response => {
											if (response.data.status === true) {
												let options = response.data.data;

												// if options is an array of strings, convert it to an array of objects
												if (typeof options[0] === 'string') {
													options = options.map(option => ({value: option, label: option}));
												}

												options.unshift({value: 'all', label: this.translate(filter.label)});

												this.filters[tab.key].push({
													key: filter.key,
													value: filter.default ? filter.default : 'all',
													options: options
												});
											}
										});
							}
						} else {
							this.filters[tab.key].push({
								key: filter.key,
								value: filter.default ? filter.default : 'all',
								options: filter.values
							});
						}
					});
				}
			}
		},
		searchItems() {
			if (this.searches[this.selectedListTab].searchDebounce !== null) {
				clearTimeout(this.searches[this.selectedListTab].searchDebounce);
			}

			this.searches[this.selectedListTab].searchDebounce = setTimeout(() => {
				if (this.searches[this.selectedListTab].search !== this.searches[this.selectedListTab].lastSearch) {
					this.searches[this.selectedListTab].lastSearch = this.searches[this.selectedListTab].search;

					// when we are searching through the list, we reset the pagination
					this.getListItems(1, this.selectedListTab);
				}
			}, 500);
		},
		onClickAction(action, itemId = null) {
			if (action === null || typeof action !== 'object') {
				return false;
			}

			let item = null;
			if (itemId !== null) {
				item = this.items[this.selectedListTab].find(item => item.id === itemId);
			}

			if (action.type === 'redirect') {
				let url = action.action;
				if (item !== null) {
					Object.keys(item).forEach(key => {
						url = url.replace('%' + key + '%', item[key]);
					});
				}

				window.location.href = url;
			} else {
				let url = 'index.php?option=com_emundus&controller=' + action.controller + '&task=' + action.action;

				if (itemId !== null) {
					if (action.parameters) {
						let url_parameters = action.parameters;
						if (item !== null) {
							Object.keys(item).forEach(key => {
								url_parameters = url_parameters.replace('%' + key + '%', item[key]);
							});
						}

						url += url_parameters;
					} else {
						url += '&id=' + itemId;
					}
				}

				client().get(url)
						.then(response => {
							if (response.data.status === true || response.data.status === 1) {
								if (response.data.redirect) {
									window.location.href = response.data.redirect;
								}

								this.getListItems();
							} else {
								if (response.data.msg) {
									Swal.fire({
										type: 'error',
										title: this.translate(response.data.msg),
										reverseButtons: true,
										customClass: {
											title: 'em-swal-title',
											confirmButton: 'em-swal-confirm-button',
											actions: 'em-swal-single-action'
										}
									});
								}
							}
						})
						.catch(error => {
							console.error(error);
						});
			}
		},
		onClickPreview(item) {
			if (this.previewAction && (this.previewAction.title || this.previewAction.content)) {
				Swal.fire({
					title: item[this.previewAction.title],
					html: '<div style="text-align: left;">' + item[this.previewAction.content] + '</div>',
					reverseButtons: true,
					customClass: {
						title: 'em-swal-title',
						confirmButton: 'em-swal-confirm-button',
						actions: 'em-swal-single-action',
					}
				});
			}
		},
		onChangeFilter() {
			// when we change a filter, we reset the pagination
			this.getListItems(1, this.selectedListTab);
		},
		changeViewType(viewType) {
			this.viewType = viewType.value;
			localStorage.setItem('tchooz_view_type/' + document.location.hostname,viewType.value);
		},
		filterShowOnActions(actions, item) {
			return actions.filter(action => {
				if (action.hasOwnProperty('showon')) {
					return this.evaluateShowOn(item, action.showon);
				}

				return true;
			});
		},
		evaluateShowOn(item, showon) {
			let show = true;
			switch (showon.operator) {
				case '==':
				case '=':
					show = item[showon.key] == showon.value;
					break;
				case '!=':
					show = item[showon.key] != showon.value;
					break;
				case '>':
					show = item[showon.key] > showon.value;
					break;
				case '<':
					show = item[showon.key] < showon.value;
					break;
				case '>=':
					show = item[showon.key] >= showon.value;
					break;
				case '<=':
					show = item[showon.key] <= showon.value;
					break;
				default:
					show = true;
			}

			return show;
		}
},
	computed: {
		currentTab() {
			return this.currentList.tabs.find((tab) => {
				return tab.key === this.selectedListTab;
			});
		},
		tabActionsPopover() {
			return typeof this.currentTab.actions !== 'undefined' ? this.currentTab.actions.filter((action) => {
				return !(['add', 'edit', 'preview'].includes(action.name));
			}): [];
		},
		editAction() {
			return typeof this.currentTab !== 'undefined' && typeof this.currentTab.actions !== 'undefined' ? this.currentTab.actions.find((action) => {
				return action.name === 'edit';
			}): false;
		},
		addAction() {
			return typeof this.currentTab !== 'undefined' && typeof this.currentTab.actions !== 'undefined' ? this.currentTab.actions.find((action) => {
				return action.name === 'add';
			}): false;
		},
		previewAction() {
			return typeof this.currentTab !== 'undefined' && typeof this.currentTab.actions !== 'undefined' ? this.currentTab.actions.find((action) => {
				return action.name === 'preview';
			}): false;
		},
		displayedItems() {
			let items = typeof this.items[this.selectedListTab] !== 'undefined' ? this.items[this.selectedListTab] : [];
			return items.filter((item) => {
				return item.label[this.params.shortlang].toLowerCase().includes(this.searches[this.selectedListTab].search.toLowerCase());
			});
		},
		additionalColumns() {
			let columns = [];
			let items = typeof this.items[this.selectedListTab] !== 'undefined' ? this.items[this.selectedListTab] : [];

			if (items.length > 0 && typeof items[0].additional_columns !== 'undefined') {
				items[0].additional_columns.forEach((column) => {
					if (column.display === 'all' || (column.display === this.viewType)) {
						columns.push(column.key);
					}
				});
			}

			return columns;
		}
	},
	watch: {
		numberOfItemsToDisplay() {
			localStorage.setItem('tchooz_number_of_items_to_display/' + document.location.hostname, this.numberOfItemsToDisplay);
		},
	}
}
</script>

<style lang="scss">
#list-nav {
	li {
		transition: all .3s;
	}
}

#list-table {
	transition: all .3s;
	border: 0;

	thead th{
		background-color: transparent;
	}

	&.blocs {
		border: 0;


		thead {
			display: none;
		}

		tbody {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
			column-gap: 24px;
			row-gap: 24px;

			tr {
				background: #fff;
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				min-height: 200px;

				td {
					display: flex;
					flex-direction: row;
					justify-content: space-between;
					padding: 0;

					&.actions {
						align-items: center;
					}

					ul {
						display: flex;
						flex-direction: column;
						justify-content: flex-end;
						align-items: flex-end;
						padding: 0;
						margin: 0;

						li {
							list-style: none;
							cursor: pointer;
						}
					}
				}
			}
		}
	}
}

.skeleton-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
	column-gap: 24px;
	row-gap: 24px;
}

#tabs-loading, #items-loading {

	:not(.skeleton-grid) .skeleton-item,
	&:not(.skeleton-grid) .skeleton-item {
		height: 40px !important;
		width: 100% !important;
		margin-bottom: 16px !important;
	}

	.skeleton-grid  .skeleton-item,
	&.skeleton-grid  .skeleton-item {
		height: 200px !important;
		min-width: 340px !important;
	}
}

#pagination {
	transition: all .3s;

	li {
		transition: all .3s;
		font-size: 12px;
	}
}
</style>