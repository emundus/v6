<template>
	<div id="list-table">
		<table :aria-describedby="'Table of ' + type">
			<list-table-head :ths="rowsData[type]"></list-table-head>
			<list-table-rows :tds="rowsData[type]" :data="list"></list-table-rows>
		</table>
	</div>
</template>

<script>
import ListTableRows from './ListTableRows.vue'
import ListTableHead from './ListTableHead.vue'
import { list } from '../../../store/store'

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
			rowsData: {
				campaign: [
					{
						label: 'Nom',
						value: 'label',
					},
					{
						label: 'Date de début',
						value: 'start_date',
					},
					{
						label: 'Date de fin',
						value: 'end_date',
					},
					{
						label: 'État',
						value: 'status',
					},
					{
						label: 'Nombre de dossiers',
						value: 'nb_files',
					},
					{
						label: '',
						value: 'actions',
					}
				],
				email: [],
				form:  [],
			},
		}
	},
	computed: {
		list() {
			return list.getters.list;
		}
	}
}
</script>
