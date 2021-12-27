<template>
	<tr>
		<td v-for="td in tds" :key="td.value">
			<span v-if="data[td.value]"> {{data[td.value]}} </span>
		</td>
	</tr>
</template>

<script>
export default {
	props: {
		tds: {
			type: Array,
			required: true
		},
		data: {
			type: Object,
			required: true
		},
	},
}
</script>