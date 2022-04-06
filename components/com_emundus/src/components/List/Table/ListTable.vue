<template>
	<div id="list-table">
		<table :aria-describedby="'Table of ' + type" v-if="!isEmptyRowsData">
		<thead class="list-table-head">
			<tr>
				<th v-for="th in rowsData" :key="th.value" :id="th.value">
					{{ translate(th.label) }}
				</th>
			</tr>
		</thead>
		<list-table-body
			:type="type"
			:actions="actions"
			:params="params"
			@validateFilters="validateFilters"
			@updateLoading="updateLoading"
			@showModalPreview="showModalPreview"
		></list-table-body>
		</table>
		<p v-if="isEmptyRowsData">Unable to create table...</p>
	</div>
</template>

<script>
import ListTableBody from './ListTableBody.vue'
import rows from '../../../data/tableRows'

export default {
	components: {
		ListTableBody
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
	data() {
		return 		{
			rowsData: [],
		}
	},
	mounted() {
		this.rowsData = typeof rows[this.type] !== undefined ? rows[this.type] : [];
	},
	methods: {
		/*translate(label) {
			return label ? this.translate(label) : '';
		},*/
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
		}
	}
}
</style>
