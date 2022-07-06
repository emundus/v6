<template>
	<div class="list-actions-menu">
		<v-popover
      class="em-pointer"
			:popoverArrowClass="'custom-popover-arrow'"
		>
      <span class="tooltip-target b3 material-icons">more_vert</span>
			<template slot="popover">
				<actions
				></actions>
			</template>
		</v-popover>
	</div>
</template>

<script>
import actions from "./ActionMenu.vue";

export default {
  name: "ListActionMenu",
	components: { actions },

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
.material-icons, .material-icons-outlined{
  font-size: 24px !important;
}
</style>
