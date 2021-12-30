<template>
	<div class="list-actions-menu">
		<button 
			v-if="type == 'email'"
			class="cta-block" 
			style="height: unset" 
			type="button" 
			:title="translations.visualize" 
			@click="showModalPreview"
		>
      <em class="fas fa-eye"></em>
    </button>
		<v-popover
			v-if="showTootlip === true"
			:popoverArrowClass="'custom-popover-arrow'"
		>
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
		showTootlip : {
			type: Boolean,
			default: true
		}
	},
	data() {
		return {
			translations: {
				visualize: Joomla.JText._("COM_EMUNDUS_ONBOARD_VISUALIZE"),
			}
		}
	},
	methods: {
		showModalPreview() {
			this.$emit("showModalPreview");
		},
		validateFilters() {
      this.$emit('validateFilters');
    },
		updateLoading(value) {
      this.$emit('updateLoading',value);
    },
	}
}
</script>

<style lang="scss" scoped>
.list-actions-menu {
	display: flex;
	align-items: center;
	justify-content: flex-end;
	margin-top: 1rem;
	margin-bottom: 1rem;

	.cta-block:hover {
		color: #298721;
	}
}
</style>