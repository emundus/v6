<template>
	<div class="list-actions-menu">
		<v-popover :popoverArrowClass="'custom-popover-arrow'">
      <button class="tooltip-target b3 card-button"></button>
			<template slot="popover">
				<actions
					:data="{type: type}"
					:selected="itemId"
					:published="isPublished"
					@validateFilters="validateFilters()"
					@updateLoading="updateLoading"
				></actions>
			</template>
		</v-popover>
	</div>
</template>

<script>
import actions from "../list_components/action_menu.vue";

export default {
	components: { actions },
	props: {
		type: {
			type: String,
			required: true
		},
		itemId: {
			type: String,
			required: true
		},
		isPublished: {
			type: Boolean,
			required: true
		},
	},
	methods: {
		validateFilters() {
      this.$emit('validateFilters');
    },
		updateLoading(value) {
      this.$emit('updateLoading',value);
    },
	}
}
</script>