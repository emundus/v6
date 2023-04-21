<template>
	<div id="list-table">
		<table :aria-describedby="'Table of ' + type" v-if="!isEmptyRowsData">
		<thead class="list-table-head">
			<tr><th v-for="th in rowsData" :key="th.value" :id="th.value">{{ translate(th.label) }}</th></tr>
		</thead>
		<tbody>
			<list-table-row
					v-for="item in items"
					:key="item.id"
					:data="item"
					:type="type"
					:actions="actions"
					@validateFilters="validateFilters"
					@updateLoading="updateLoading"
					@showModalPreview="showModalPreview(item.id)"
			></list-table-row>
		</tbody>
		</table>
		<p v-if="isEmptyRowsData">Unable to create table...</p>
	</div>
</template>

<script>
import ListTableRow from './ListTableRow.vue'
import rows from '../../../../data/tableRows'

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
		},
		items: {
			type: [],
			required: true
		}
	},
	data() {
		return 		{
			rowsData: [],
		}
	},
	mounted() {
		this.rowsData = typeof rows[this.type] !== undefined ? rows[this.type] : [];
	},
	methods: {
		validateFilters() {
      this.$emit('validateFilters');
    },
		updateLoading(value) {
      this.$emit('updateLoading', value);
    },
		showModalPreview(itemId) {
			this.$emit('showModalPreview', itemId);
		}
	},
	computed: {
		isEmptyRowsData() {
			return this.rowsData.length === 0;
		},
	},
	watch: {
		type: function() {
			this.rowsData = typeof rows[this.type] !== undefined ? rows[this.type] : [];
		}
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
	.list-table-head {
		tr th {
			font-size: 14px;
			padding: 0.85rem 0.5rem;
			background-color: transparent;
      color: var(--neutral-900);

		}
	}
}
</style>
