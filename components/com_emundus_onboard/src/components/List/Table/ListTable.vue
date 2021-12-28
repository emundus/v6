<template>
	<div id="list-table">
		<table :aria-describedby="'Table of ' + type" v-if="!isEmptyRowsData">
			<list-table-head :ths="rowsData"></list-table-head>
			<list-table-rows :tds="rowsData" :data="list"></list-table-rows>
		</table>
		<p v-if="isEmptyRowsData">Unable to create table...</p>
	</div>
</template>

<script>
import ListTableRows from './ListTableRows.vue'
import ListTableHead from './ListTableHead.vue'
import { list } from '../../../store/store'
import rows from '../../../data/tableRows'

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
		}
	},
	mounted() {
		this.getRowsData()
	},
	methods: {
		getRowsData() {
			// get rows data from json file tableRows.json
			this.rowsData = typeof rows[this.type] !== undefined ? rows[this.type] : [];
		},
	},
	computed: {
		list() {
			return list.getters.list;
		},
		isEmptyRowsData() {
			return this.rowsData.length === 0;
		},
	}
}
</script>
