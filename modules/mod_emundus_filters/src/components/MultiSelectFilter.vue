<template>
	<div class="multi-select-filter em-w-100 em-mb-8">
		<label class="em-w-100"> {{ filter.label }}</label>
		<select v-model="filter.value" class="em-w-100">
			<option v-for="value in filter.values" :value="value.value"> {{ value.label }}</option>
		</select>
	</div>
</template>

<script>
export default {
	name: 'MultiSelect.vue',
	props: {
		moduleId: {
			type: Number,
			required: true
		},
		filter: {
			type: Object,
			required: true
		},
	},
}
</script>

<style scoped>

</style>