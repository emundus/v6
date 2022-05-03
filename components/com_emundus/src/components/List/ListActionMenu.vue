<template>
	<div class="list-actions-menu">
    <button
			v-if="type == 'email'"
			type="button"
      class="em-transparent-button"
			:title="translations.visualize"
			@click="showModalPreview"
		>
      <span class="material-icons-outlined">visibility</span>
    </button>
		<v-popover
			v-if="showTootlip === true"
      class="em-pointer"
			:popoverArrowClass="'custom-popover-arrow'"
		>
      <span class="tooltip-target b3 material-icons">more_vert</span>
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
import actions from "../ListComponents/action_menu.vue";

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
				visualize: this.translate("COM_EMUNDUS_ONBOARD_VISUALIZE"),
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

	.cta-block:hover {
		color: #298721;
	}

	&#list-row#action-menu {
		margin: 0;
		transform: rotate(90deg);
	}
}
.material-icons{
  font-size: 24px !important;
}
</style>
