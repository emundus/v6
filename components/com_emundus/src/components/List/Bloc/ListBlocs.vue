<template>
	<div id="list-blocs"
		class="em-grid"
		:class="{
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
	},
	computed: {
		list() {
			if (this.params.email_category) {
				return this.$store.getters['lists/list'].filter((item) => {
					if (this.params.email_category == 0) {
						return true;
					} else {
						return item.category === this.params.email_category;
					}
				});
			}

			return this.$store.getters['lists/list'];
		}
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

