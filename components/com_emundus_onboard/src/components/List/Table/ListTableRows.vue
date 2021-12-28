<template>
	<tbody>
		<list-table-row 
			v-for="item in items" 
			:key="item.id" 
			:item="item" 
			:isPublished="isPublished(item)"
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
		isPublished(item) {
			let published = false;

			item.forEach(element => {
				if (element.value == "status" && element.class.indexOf('tag published') !== -1) {
						published = true;
				}
			});

			return published;
		},
		validateFilters() {
      this.$emit('validateFilters');
    },
		updateLoading(value) {
      this.$emit('updateLoading',value);
    },
	},
}
</script>