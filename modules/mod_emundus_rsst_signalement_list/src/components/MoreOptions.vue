<template>
	<div id="more-opt-popover">
		<v-popover class="em-pointer" style="position: absolute;">
			<div v-for="option in options" :key="option.value">
				<span :id="'opt-' + option.value" @click="$emit('select-option', option)">{{ option.label }}</span>
			</div>
		</v-popover>
	</div>
</template>

<script>
export default {
	props: {
		options: {
			type: Array,
			required: true
		}
	}
}
</script>