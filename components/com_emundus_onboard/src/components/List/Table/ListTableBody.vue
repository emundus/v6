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
		params: {
			type: Object,
			default: {}
		}
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
			if (this.type === "email" && typeof this.params !== "undefined") {
				if (this.params.email_category) {
					return list.getters.list.filter((item) => {
						if (this.params.email_category == 0) {
							return true;
						} else {
							return item.category === this.params.email_category;
						}
					});
				}
			}

			return list.getters.list;
		}
	},
}
</script>