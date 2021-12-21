<template>
	<div :class="groupClasses">
		<div class="wrapper">
			<div class="rows">
				<div class="relation">
					<!-- toggle between AND and OR, not as select but as -->
					<div
						:class="{ selected: andOr === 'AND' }"
						@click="toggleAndOr('AND')"
					>
						ET
					</div>
					<div :class="{ selected: andOr === 'OR' }" @click="toggleAndOr('OR')">
						OU
					</div>
				</div>
				<FilterRow
					:group="id"
					:id="filter"
					class="filter"
					v-for="filter in filters"
					:key="filter"
					@removeFilter="removeFilter(filter)"
				></FilterRow>
			</div>
			<div class="actions add">
				<span class="material-icons add btn-primary-vue" @click="addFilter">
					add
				</span>
			</div>
		</div>
		<span
			class="material-icons delete-group"
			@mouseover="addDeleteClass"
			@mouseleave="removeDeleteClass"
			@click="removeGroup"
		>
			clear
		</span>
	</div>
</template>

<script>
import FilterRow from "./FilterRow.vue";

export default {
	name: "FilterGroup",
	props: {
		id: {
			type: Number,
			required: true,
		},
	},
	components: {
		FilterRow,
	},
	data() {
		return {
			andOr: "AND",
			filters: [],
			groupClasses: "group",
		};
	},
	mounted() {
		this.filters = this.$store.state.filterBuilder.queryFilters.groups[this.id]
			? Object.entries(
					this.$store.state.filterBuilder.queryFilters.groups[this.id].filters
			  ).map((filter) => Number(filter[0]))
			: [];
		if (this.filters.length < 1) {
			this.addFilter();
		}
	},
	methods: {
		addFilter() {
			// add uniqid
			this.filters.push(Math.floor(Math.random() * Date.now()));
		},
		removeFilter(filterId) {
			this.filters = this.filters.filter((filter) => filter !== filterId);

			if (this.filters.length < 1) {
				this.removeGroup();
			}
		},
		removeGroup() {
			this.$store.dispatch("filterBuilder/removeGroup", this.id);

			this.$emit("removeGroup", this.id);
		},
		toggleAndOr(andOr) {
			this.andOr = andOr;
		},
		addDeleteClass() {
			this.groupClasses = "group to-delete";
		},
		removeDeleteClass() {
			this.groupClasses = "group";
		},
	},
	watch: {
		andOr() {
			this.$store.dispatch("filterBuilder/updateAndOr", {
				group: this.id,
				and_or: this.andOr,
			});
		},
	},
};
</script>

<style lang="scss">
.group {
	display: flex;
	flex-direction: row;
	align-items: center;
	justify-content: space-between;
	margin: 20px 0 20px 0px;

	&.to-delete {
		.wrapper {
			border: 1px solid var(--error-color);
		}
	}

	.wrapper {
		display: flex;
		flex-direction: column;
		padding: 8px 16px;
		border-radius: 4px;
		border: 1px solid transparent;
		background-color: var(--grey-bg-color);
		transition: all 0.3s;
	}

	.actions.add {
		display: flex;
		flex-direction: row;
		justify-content: flex-end;
		cursor: pointer;
		margin-top: 8px;
		opacity: 1 !important;
		pointer-events: all !important;

		.material-icons {
			margin-right: 10px;
			border-radius: 4px;
			padding: 0;
			margin: 0 !important;
		}
	}

	.delete-group {
		cursor: pointer;
		transition: all 0.3s;
		margin-left: 8px;

		&:hover {
			color: var(--error-color);
			transform: rotate(90deg);
		}
	}
}
</style>