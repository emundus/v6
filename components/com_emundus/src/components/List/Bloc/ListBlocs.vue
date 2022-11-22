<template>
	<div id="list-blocs" class="em-grid" :class="{'less-than-4': items.length < 4,}">
		<list-bloc
			v-for="item in items"
			:key="item.id"
			:data="item"
			:type="type"
			:actions="actions"
			@validateFilters="validateFilters"
			@updateLoading="updateLoading"
			@showModalPreview="showModalPreview(item.id)"
		>
		</list-bloc>
	</div>
</template>

<script>

import ListBloc from './ListBloc.vue';

export default {
	components: {
		ListBloc
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
	methods: {
		validateFilters() {
			this.$emit('validateFilters');
		},
		updateLoading(value) {
			this.$emit('updateLoading',value);
		},
		showModalPreview(id) {
			this.$emit('showModalPreview', id)
		},
	}
}
</script>

<style lang="scss" scoped>
#list-blocs {
	&.less-than-4 {
		grid-template-columns: repeat(auto-fit, minmax(380px, 450px));
	}
}
</style>

