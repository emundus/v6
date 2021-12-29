<template>
	<div id="list-blocs" 
		:class="{
			'more-than-3': list.length >= 4,
			'less-than-4': list.length < 4,
		}"
	>
		<list-bloc
			v-for="item in list" 
			:key="item.id" 
			:data="item" 
			:type="type" 
			:actions="actions"
			@validateFilters="validateFilters"
			@updateLoading="updateLoading"
		>
		</list-bloc>
	</div>
</template>

<script>
import { list } from "../../../store/store";
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
	},
	data() {
		return {
			phantomBlocs: 0,
		};
	},
	methods: {
		validateFilters() {
			this.$emit('validateFilters');
		},
		updateLoading(value) {
			this.$emit('updateLoading',value);
		},
	},
	computed: {
		list() {
			return list.getters.list;
		}
	}
}
</script>

<style lang="scss" scoped>
#list-blocs {
	display: grid;

	&.more-than-3 {
		grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
	}

	&.less-than-4 {
		grid-template-columns: repeat(auto-fit, minmax(380px, 450px));
	}
}
</style>

