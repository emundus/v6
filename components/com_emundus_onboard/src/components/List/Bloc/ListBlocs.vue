<template>
	<div id="list-blocs">
		<list-bloc
			v-for="(item, index) in list" 
			:key="index" 
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
	mounted() {
		console.log(this.list);
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
	align-items: center;
	justify-content: flex-start;
	margin-top: 30px;
}
</style>

