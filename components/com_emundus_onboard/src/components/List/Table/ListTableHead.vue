<template>
	<thead class="list-table-head">
		<tr>
			<th v-for="th in ths" :key="th.value" :id="th.value"> 
				{{ th.label }}
			</th>
		</tr>
	</thead>
</template>

<script>
export default {
	props: {
		ths: {
			type: Array,
			required: true
		},
	},
}
</script>

<style lang="scss" scoped>
.list-table-head {
	tr th {
		font-size: 14px;
		padding: 0.85rem 0.5rem;
		background-color: transparent;
	}
}
</style>