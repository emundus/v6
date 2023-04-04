<template>
	<div id="onboarding_list" class="em-w-100">
		<div class="head em-flex-row em-flex-space-between em-mb-16 em-mt-16">
			<h2 style="margin:0;">{{ currentList.title }}</h2>
			<a v-if="addAction" class="em-primary-button em-w-auto em-pointer" @click="onClickAction(addAction)">{{ translate(addAction.label) }}</a>
		</div>
		<hr class="em-w-100">
		<div class="list">
			<section id="list-filter">
				<div class="em-flex-row em-flex-row-center">
					<span class="material-icons-outlined em-mr-8">search</span>
					<input name="search" type="text" style="margin: 0;" :placeholder="translate('COM_EMUNDUS_ONBOARD_SEARCH')" v-model="search">
				</div>
			</section>
			<nav v-if="currentList.tabs.length > 1" id="list-nav">
				<ul style="list-style-type: none;margin-left:0;" class="em-flex-row">
					<li v-for="tab in currentList.tabs"
					    key="tab.key"
					    class="em-pointer em-p-8 em-font-weight-600 em-p-16"
					    :class="{
								'em-main-500-color em-border-bottom-main-500 ': selectedListTab === tab.key,
							  'em-border-bottom-neutral-300': selectedListTab !== tab.key
							}"
					    @click="selectedListTab = tab.key"
					>
						{{ translate(tab.title) }}
					</li>
				</ul>
			</nav>
			<div id="actions" class="em-flex-row-justify-end em-mt-16 em-mb-16">
				<div class="view-type">
					<span
							v-for="viewTypeOption in viewTypeOptions"
							:key="viewTypeOption.value"
							class="material-icons-outlined em-pointer em-ml-8"
							:class="{'active': viewTypeOption.value === viewType}"
							@click="changeViewType(viewTypeOption)"
					>
						{{ viewTypeOption.icon }}
					</span>
				</div>
			</div>
			<div>
				<div v-if="displayedItems.length > 0" id="list-items">
					<table id="list-table" :class="{'blocs': viewType === 'blocs'}">
						<thead>
							<tr>
								<th>{{ translate('COM_EMUNDUS_ONBOARD_LABEL') }}</th>
								<th>{{ translate('COM_EMUNDUS_ONBOARD_ACTIONS') }}</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="item in displayedItems" :key="item.id">
								<td class="em-pointer" @click="onClickAction(editAction, item.id)"><h3>{{ item.label[params.shortlang] }}</h3></td>
								<div>
									<hr v-if="viewType === 'blocs'" class="em-w-100">
									<td class="actions">
										<a v-if="viewType === 'blocs' && editAction"
										   @click="onClickAction(editAction, item.id)"
										   class="em-primary-button em-font-size-14 em-pointer em-w-auto"
										>
											{{ translate(editAction.label) }}
										</a>

										<v-popover :popoverArrowClass="'custom-popover-arrow'">
											<span class="tooltip-target b3 material-icons">more_vert</span>
											<template slot="popover">
												<ul style="list-style-type: none; margin: 0;">
													<li v-for="action in tabActionsPopover"
													    :key="action.action"
													    @click="onClickAction(action, item.id)"
													    class="em-pointer em-p-8 em-font-weight-600"
													>
														{{ translate(action.label) }}
													</li>
												</ul>
											</template>
										</v-popover>
									</td>
								</div>
							</tr>
					</table>
				</div>
				<p v-else id="list-empty">{{ translate('COM_EMUNDUS_ONBOARD_EMPTY_LIST') }}</p>
			</div>
		</div>
	</div>
</template>

<script>
import Vue from 'vue';
import settingsService from '../services/settings.js';
import client from '../services/axiosClient';

export default {
	name: 'list_v2',
	data() {
		return {
			lists: {},
			type: 'forms',
			params: {},
			currentList: {'title': '', 'tabs': []},
			selectedListTab: 0,
			items: {},
			title: '',
			viewType: 'table',
			viewTypeOptions: [
				{
					value: 'table',
					icon: 'dehaze'
				},
				{
					value: 'blocs',
					icon: 'grid_view'
				}
			],
			search: '',
		}
	},
	created() {
		const data = this.$store.getters['global/datas'];
		this.params = Object.assign({}, ...Array.from(data).map(({name, value}) => ({[name]: value})));
		this.type = this.params.type;

		this.viewType = localStorage.getItem('tchooz_view_type/' + document.location.hostname)
		if(this.viewType === null || typeof this.viewType === 'undefined' || (this.viewType !== 'blocs' && this.viewType !== 'table')){
			this.viewType = 'blocs';
			localStorage.setItem('tchooz_view_type/' + document.location.hostname,'blocs');
		}

		this.initList();
	},
	methods: {
		initList() {
			settingsService.getOnboardingLists().then(response => {
				if (response.data.status) {
					this.lists = response.data.data;

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

					this.getListItems();
				} else {
					console.error('Error while getting onboarding lists');
					window.location.href = '/';
				}
			});
		},
		getListItems() {
			this.items = Vue.observable(Object.assign({}, ...this.currentList.tabs.map(tab => ({[tab.key]: []}))));

			this.currentList.tabs.forEach(tab => {
				if (typeof tab.getter !== 'undefined') {
					client().get('index.php?option=com_emundus&controller=' + tab.controller + '&task=' + tab.getter)
						.then(response => {
							if (response.data.status === true) {
								this.items[tab.key] = response.data.data;
							}
						})
						.catch(error => {
							console.error(error);
						});
				}
			});
		},
		onClickAction(action, itemId = null) {
			let item = null;
			if (itemId !== null) {
				item = this.items[this.selectedListTab].find(item => item.id === itemId);
			}

			if (action.type === 'redirect') {
				let url = action.action;
				Object.keys(item).forEach(key => {
					url = url.replace('%' + key + '%', item[key]);
				});

				window.location.href = url;
			} else {
				let url = 'index.php?option=com_emundus&controller=' + action.controller + '&task=' + action.action;

				if (itemId !== null) {
					if (action.parameters) {
						let url_parameters = action.parameters;
						Object.keys(item).forEach(key => {
							url_parameters = url_parameters.replace('%' + key + '%', item[key]);
						});

						url += url_parameters;
					} else {
						url += '&id=' + itemId;
					}
				}

				client().get(url)
						.then(response => {
							if (response.data.status === true) {
								if (response.data.redirect) {
									window.location.href = response.data.redirect;
								}

								this.getListItems();
							}
						})
						.catch(error => {
							console.error(error);
						});
			}
		},
		changeViewType(viewType) {
			this.viewType = viewType.value;
			localStorage.setItem('tchooz_view_type/' + document.location.hostname,viewType.value);
		},
	},
	computed: {
		currentTab() {
			return this.currentList.tabs.find((tab) => {
				return tab.key === this.selectedListTab;
			});
		},
		tabActionsPopover() {
			return typeof this.currentTab.actions !== 'undefined' ? this.currentTab.actions.filter((action) => {
				return !(['add', 'edit'].includes(action.name));
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
		displayedItems() {
			let items = typeof this.items[this.selectedListTab] !== 'undefined' ? this.items[this.selectedListTab] : [];
			return items.filter((item) => {
				return item.label[this.params.shortlang].toLowerCase().includes(this.search.toLowerCase());
			});
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

	h3 {
		font-size: 18px;
	}

	&.blocs {
		border: 0;

		thead {
			display: none;
		}

		tbody {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
			column-gap: 10px;
			row-gap: 15px;

			tr {
				background: #fff;
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				border: 1px solid #ddd;
				border-radius: 4px;
				padding: 12px;
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
</style>