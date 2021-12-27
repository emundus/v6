<template>
	<tbody>
		<list-table-row v-for="item in data" :key="item.id" :data="item" :tds="tds"></list-table-row>
	</tbody>
</template>

<script>
import ListTableRow from './ListTableRow.vue';

export default {
	components: { ListTableRow },
	props: {
		tds: {
			type: Array,
			required: true
		},
		data: {
			type: Array,
			required: true
		},
	},
}
</script>