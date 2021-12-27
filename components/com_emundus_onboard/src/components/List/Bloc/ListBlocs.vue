<template>
	<div id="list-blocs">
		<list-bloc
			v-for="item in list" 
			:key="item.id" 
			:data="item" 
			:type="type" 
			:actions="actions">
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
	computed: {
		list() {
			return list.getters.list;
		}
	}
}
</script>

<style lang="scss" scoped>
#list-blocs {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	align-items: stretch;
	justify-content: flex-start;
	margin-top: 30px;
}
</style>

