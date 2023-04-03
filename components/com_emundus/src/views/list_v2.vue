<template>
	<div id="onboarding_list" class="em-w-100">
		<div class="head em-flex-row em-flex-space-between em-mb-8 em-mt-8">
			<h2 style="margin:0;">{{ currentList.title }}</h2>
			<a v-if="currentAddAction" class="em-primary-button em-w-auto" @click="onClickAddAction">{{ translate('COM_EMUNDUS_LIST_ADD_' + type.toUpperCase()) }}</a>
		</div>
		<div class="list">
			<nav v-if="currentList.tabs.length > 1">
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
						{{ tab.title }}
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
				<div v-if="typeof items[selectedListTab] != 'undefined' && items[selectedListTab].length > 0" id="list-items">
					<table id="list-table" :class="{
						'blocs': viewType === 'blocs',
					}">
						<thead>
							<tr>
								<th>Label</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="item in items[selectedListTab]" :key="item.id">
								<td>{{ item.label[params.shortlang] }}</td>
								<td>
									<ul>
										<li v-for="action in tabActionsWithoutAdd"
										    :key="action.action"
										    @click="onClickAction(action, item.id)"
										>
											{{ action.label }}
										</li>
									</ul>
								</td>
							</tr>
					</table>
				</div>
				<p v-else id="list-empty">{{ translate('COM_EMUNDUS_LIST_EMPTY') }}</p>
			</div>
		</div>
	</div>
</template>

<script>
import Vue from 'vue';
import settingsService from '../services/settings.js';
import client from '../services/axiosClient';

export default {
	name: "list_v2",
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
		changeViewType(viewType) {
			this.viewType = viewType.value;
			localStorage.setItem('tchooz_view_type/' + document.location.hostname,viewType.value);
		},
		initList() {
			settingsService.getOnboardingLists().then(response => {
				if (response.data.status) {
					this.lists = response.data.data;

					if (typeof this.lists[this.type] === 'undefined') {
						console.error('List type ' + this.type + ' does not exist');
						window.location.href = '/';
						return;
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
					return;
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
		onClickAddAction() {
			const addAction = this.currentTab.actions.find((action) => {
				return action.type == 'add';
			});

			if (typeof addAction !== 'undefined') {
				this.onClickAction(addAction);
			} else {
				console.error('No add action found for this list');
			}
		},
		onClickAction(action, itemId = null) {
			const parameter = action.parameter || 'id';

			if (action.type == 'redirect') {
				window.location.href = action.action.replace('%id%', itemId);
				return;
			} else {
				let url = 'index.php?option=com_emundus&controller=' + action.controller + '&task=' + action.action;

				if (itemId !== null) {
					url += '&' + parameter + '=' + itemId;
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
		}
	},
	computed: {
		currentTab() {
			return this.currentList.tabs.find((tab) => {
				return tab.key == this.selectedListTab;
			});
		},
		tabActionsWithoutAdd() {
			return typeof this.currentTab.actions !== 'undefined' ? this.currentTab.actions.filter((action) => {
				return action.type != 'add';
			}): [];
		},
		currentAddAction() {
			return typeof this.currentTab.actions !== 'undefined' ? this.currentTab.actions.find((action) => {
				return action.type == 'add';
			}): false;
		}
	}
}
</script>

<style lang="scss">
#list-table {
	&.blocs {
		border: 0;

		thead {
			display: none;
		}

		tbody {
			tr {
				display: flex;
				flex-direction: column;
				border: 1px solid #ddd;
				border-radius: 5px;
				margin-bottom: 10px;
				padding: 10px;

				td {
					display: flex;
					flex-direction: column;
					justify-content: space-between;
					padding: 0;

					ul {
						display: flex;
						flex-direction: row;
						justify-content: space-between;
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