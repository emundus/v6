<template>
	<thead>
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