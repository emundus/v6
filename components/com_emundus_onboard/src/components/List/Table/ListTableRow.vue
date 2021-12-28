<template>
	<tr>
		<td v-for="itemValue in item" :key="itemValue.value">
			<span v-if="itemValue.value"> {{ itemValue.value }} </span>
		</td>
	</tr>
</template>

<script>
export default {
	props: {
		item: {
			type: Array,
			required: true
		},
	},
}
</script>