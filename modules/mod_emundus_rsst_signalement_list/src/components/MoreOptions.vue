<template>
	<div id="more-opt-popover" @focusout="$emit('focusout')">
		<v-popover class="em-pointer custom-em-popover">
			<div class="opt-popover" v-for="option in options" :key="option.value">
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

<style lang="scss">
.custom-em-popover {
	position: absolute;
	width: 100px;
	background-color: white;
	border-radius: 4px;

	.trigger {
		width: 100%;
	}
	.opt-popover span {
		padding: 4px 0;
	}

	.opt-popover {
		width: 100%;
		padding: 8px;
		transition: all .3s;

		&:hover {
			background: #dff5e9;
		}
	}
}
</style>