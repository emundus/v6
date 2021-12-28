<template>
	<tr>
		<td v-for="itemValue in item" :key="itemValue.value">
			<span v-if="itemValue.value" :class="itemValue.class"> {{ itemValue.value }} </span>
		</td>
	</tr>
</template>

<script>
export default {
	props: {
		item: {
			type: Array,
			required: true
		},
	},
}
</script>

<style lang="scss" scoped>
tr td {
	border-left: 0;
  border-right: 0;
	font-size: 12px;
	padding: 0.85rem 0.5rem;

	span {
		&.tag {
			margin: 0 8px 8px 0;
			padding: 4px 8px;
			border-radius: 4px;
			color: #080C12;
			height: fit-content;
			background: #F2F2F3;

			&.published {
				background: #DFF5E9;
			}

			&.unpublished {
				color: #ACB1B9;
			}

			&.finished {
				color: #FFFFFF;
				background: #080C12;
			}	
		}
	}
}
</style>