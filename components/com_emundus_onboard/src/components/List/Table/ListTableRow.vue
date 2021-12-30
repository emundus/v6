<template>
	<tr class="list-row">
		<td v-for="itemValue in item" :key="itemValue.value">
			<span v-if="itemValue.value && itemValue.value != 'actions'" :class="itemValue.class"> {{ itemValue.label }} </span>
			<span v-if="itemValue.value == 'actions'"> 
				<list-action-menu
					:type="itemValue.label.type"
					:itemId="itemValue.id"
					:isPublished="isPublished"
					@validateFilters="validateFilters"
					@updateLoading="updateLoading"
				></list-action-menu>
			</span>
		</td>
	</tr>
</template>

<script>
import ListActionMenu from '../ListActionMenu.vue';
import moment from "moment";

export default {
	components: { ListActionMenu },
	props: {
		item: {
			type: Array,
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
	},
	computed: {
		isPublished() {
			let published = false;

			this.item.forEach(element => {
				if (element.value == "published" || element.value == "status") {
					published = element.data == 1 ? true : false;
				}
			});

			return !this.isFinished && published;
		},
		isFinished() {
			let finished = false;

			this.item.forEach(element => {
				if (element.value == "end_date") {
					finished = moment(element.data).isBefore(moment());
				}
			});

			return finished;
		},
	}
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