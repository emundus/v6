<template>
	<tbody>
		<list-table-row
			v-for="item in list"
			:key="item.id"
			:data="item"
			:type="type"
			:actions="actions"
			@validateFilters="validateFilters"
			@updateLoading="updateLoading"
			@showModalPreview="showModalPreview(item.id)"
		></list-table-row>
	</tbody>
</template>

<script>
import { list } from "../../../store/store";
import ListTableRow from './ListTableRow.vue';

export default {
	components: { 
		ListTableRow 
	},
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
	methods: {
		validateFilters() {
			this.$emit('validateFilters');
		},
		updateLoading(value) {
			this.$emit('updateLoading',value);
		},
		showModalPreview(id) {
			this.$emit('showModalPreview', id)
		},
	},
	computed: {
		list() {
			return list.getters.list;
		}
	},
}
</script>