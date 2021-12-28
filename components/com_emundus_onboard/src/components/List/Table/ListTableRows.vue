<template>
	<tbody>
		<list-table-row 
			v-for="item in items" 
			:key="item.id" 
			:item="item" 
			@validateFilters="validateFilters"
			@updateLoading="updateLoading"
		>
		</list-table-row>
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
	methods: {
		validateFilters() {
      this.$emit('validateFilters');
    },
		updateLoading(value) {
      this.$emit('updateLoading',value);
    },
	},
}
</script>