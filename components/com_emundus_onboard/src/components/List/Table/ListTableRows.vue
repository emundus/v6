<template>
	<tbody>
		<list-table-row v-for="item in items" :key="item.id" :item="item"></list-table-row>
	</tbody>
</template>

<script>
import ListTableRow from './ListTableRow.vue';

export default {
	components: { ListTableRow },
	props: {
		items: {
			type: Array,
			required: true
		},
	},
}
</script>