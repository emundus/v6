<template>
	<div id="list-table">
		<table :aria-describedby="'Table of ' + type" v-if="!isEmptyRowsData">
			<list-table-head :ths="rowsData"></list-table-head>
			<list-table-rows 
				:items="items"
				@validateFilters="validateFilters"
				@updateLoading="updateLoading"
			>
			</list-table-rows>
		</table>
		<p v-if="isEmptyRowsData">Unable to create table...</p>
	</div>
</template>

<script>
import ListTableRows from './ListTableRows.vue'
import ListTableHead from './ListTableHead.vue'
import { list } from '../../../store/store'
import rows from '../../../data/tableRows'
import moment from 'moment'

export default {
	components: { ListTableHead, ListTableRows },
	props: {
		type: {
			type: String,
			required: true
		},
		actions: {
			type: Object,
			required: true
		},
	},
	data() {
		return 		{
			rowsData: [],
			items: [],
		}
	},
	mounted() {
		this.getItems();
	},
	methods: {
		getItems() {
			this.rowsData = typeof rows[this.type] !== undefined ? rows[this.type] : [];

			list.getters.list.forEach(element => {
				this.items.push(this.formatData(element));
			});
		},
		formatData(listElement) {
			switch (this.type) {
				case 'campaign': 
					return this.formatCampaignData(listElement);
				case 'email':
					return this.formatEmailData(listElement);
				case 'form':
				case 'formulaire':
				case 'grilleEval':
					return this.formatFormData(listElement);
			}

			return listElement;
		},
		formatCampaignData(listElement) {
			let item = [];

			if (!this.isEmptyRowsData) {
				this.rowsData.forEach((td) => {
					switch(td.value) {
						case "start_date":
						case "end_date":
							const date = moment(listElement[td.value]).format('DD/MM/YYYY');

							item.push({
								value: td.value,
								label: date,
								id: listElement.id,
								class: "",
								data: listElement[td.value],
							});
						break;

						case "status":
							let value = {
								value: td.value,
								id: listElement.id,
								data: listElement.published,
							}

							// check if it is finished
							const isFinished = listElement.end_date ? moment(listElement.end_date) < moment() : false;

							if (isFinished === true) {
								value.label = "Terminé";
								value.class = "tag finished";
							} else if (listElement["published"] == 1) {
								value.label = "Publié";
								value.class = "tag published";
							} else {
								value.label = "Non publié";
								value.class = "tag unpublished";
							}

							item.push(value);
						break;

						case "actions": 
							item.push({
								value: "actions",
								label: this.actions,
								id: listElement.id,
								class: "actions",
								data: listElement[td.value],
							});
						break;

						default:
							item.push({
								value: td.value,
								label: listElement[td.value] ? listElement[td.value] : td.value,
								id: listElement.id,
								class: "",
								data: listElement[td.value],
							});
						break;
					}
				});
			}

			return item;
		},
		formatEmailData(element) {
			let item = [];

			this.rowsData.forEach((td) => {
				item.push({
					label: element[td.value],
					value: td.value,
					id: element.id,
					class: "",
					data: listElement[td.value],
				});
			});

			return item;
		},
		formatFormData(element) {
			let item = [];

			this.rowsData.forEach((td) => {
				item.push({
					label: element[td.value],
					value: td.value,
					id: element.id,
					class: "",
					data: listElement[td.value]
				});
			});

			return item;
		},

		validateFilters() {
      this.$emit('validateFilters');
    },
		updateLoading(value) {
      this.$emit('updateLoading', value);
    },
	},
	computed: {
		isEmptyRowsData() {
			return this.rowsData.length === 0;
		},
	}
}
</script>

<style lang="scss" scoped>
#list-table {
	width: 100%;
	margin-top: 20px;

	table {
		border-left: 0;
  	border-right: 0;
	}
}
</style>