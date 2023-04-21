<template>
	<span
		class="em-skeleton"
		:class="classes"
		:style="{
			height: height,
			width: width
		}"
	>
	</span>
</template>

<script>
export default {
	name: "Skeleton.vue",
	props: {
		height: {
			type: String,
			default: 'auto'
		},
		width: {
			type: String,
			default: 'auto'
		},
		classes: {
			type: String,
			default: ''
		}
	}
}
</script>

<style lang="scss" scoped>
.em-skeleton {
	display: inline-block;
	position: relative;
	overflow: hidden;
	vertical-align: middle;
	background-color: #DDDBDD !important;
	transition: all .3s;

	&::after {
		position: absolute;
		top: 0;
		right: 0;
		bottom: 0;
		left: 0;
		transform: translateX(-100%);
		background-image: linear-gradient(90deg, rgba(#fff, 0) 0, rgba(#fff, 0.2) 20%, rgba(#fff, 0.5) 60%, rgba(#fff, 0));
		animation: shimmer 4s infinite;
		content: '';
	}

	@keyframes shimmer {
		100% {
			transform: translateX(100%);
		}
	}
}
</style>