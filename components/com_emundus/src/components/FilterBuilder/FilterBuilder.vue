<template>
	<div id="filter-builder" :class="{ active: open }">
		<div class="header">
			<p>Filtres avancés</p>
			<span v-if="!open" class="material-icons click" @click="open = true">
				expand_more
			</span>
			<span v-if="open" class="material-icons click" @click="open = false">
				expand_less
			</span>
		</div>
		<div class="actions" :class="{ active: open }">
			<div class="btn-secondary-vue" @click="cancel">Annuler</div>
			<div class="btn-primary-vue" @click="applyFilters">
				Appliquer les filtres
			</div>
		</div>
		<div class="filters" :class="{ active: open }">
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
				<div class="elements">
					<div
						class="element"
						v-for="element in orderedElements"
						:key="element.type == 'filter' ? element.id : element.group"
					>
						<FilterRow
							v-if="element.type == 'filter'"
							class="filter-row"
							@removeFilter="removeFilter($event)"
							:group="0"
							:id="element.id"
						></FilterRow>
						<FilterGroup
							v-if="element.type == 'group'"
							:id="element.group"
							@removeGroup="removeGroup($event)"
						></FilterGroup>
					</div>
				</div>
			</div>
			<div class="actions" :class="{ active: open }">
				<div class="btn-primary-vue" @click="addGroup">Ajouter un filtre</div>
			</div>
		</div>
	</div>
</template>

<script>
import filterService from "../../services/filter";
import FilterRow from "./FilterRow.vue";
import FilterGroup from "./FilterGroup.vue";

export default {
	name: "FilterBuilder",
	components: {
		FilterRow,
		FilterGroup,
	},
	props: {
		type: {
			type: String,
			default: "list",
		},
		id: {
			type: String,
			default: "70",
		},
		otherIds: {
			type: String,
			default: null,
		},
	},
	data() {
		return {
			open: false,
			andOr: "AND",
			orderedElements: [],
		};
	},
	mounted() {
		this.getFilters();
	},
	methods: {
		async getFilters() {
			const response = await filterService.getFilters(
				this.type,
				this.id,
				this.otherIds
			);

			if (response.status === true) {
				this.$store.dispatch("filterBuilder/setFilters", response.filters);
			}
		},
		async applyFilters() {
			this.open = false;

			if (
				Object.keys(this.$store.state.filterBuilder.queryFilters.groups)
					.length > 1
			) {
				// build query and send result ? or just send the filters ?
				const response = await filterService.mountQuery(
					this.id,
					this.$store.state.filterBuilder.queryFilters
				);

				if (response.status === true) {
					this.$emit("applyFilters", response.query);
				}
			}
		},
		cancel() {
			this.open = false;
		},
		addGroup() {
			this.orderedElements.push({
				type: "group",
				group: Math.floor(Math.random() * Date.now()),
			});
		},

		/**
		 * Tips for v-for => avoid using index as key
		 * When you remove an item of your array, you shift every index from the removal point up by one, which means only one index disappears from the array: the last one.
		 */
		removeFilter(id) {
			// find filter position
			const index = this.orderedElements.findIndex((element) => {
				return element.id == id;
			});
			// remove filter
			this.orderedElements.splice(index, 1);
		},
		removeGroup(id) {
			// find filter position
			const index = this.orderedElements.findIndex((element) => {
				return element.group == id;
			});
			// remove filter
			this.orderedElements.splice(index, 1);
		},
		toggleAndOr(andOr) {
			this.andOr = andOr;
		},
	},
	watch: {
		andOr() {
			this.$store.dispatch("filterBuilder/updateAndOr", {
				group: 0,
				and_or: this.andOr,
			});
		},
	},
};
</script>

<style lang="scss">
#filter-builder {
	max-width: 300px;
	background-color: white;
	padding: 8px 16px;
	border-radius: 4px;
	border: 1px solid var(--border-color);
	margin: 19px;
	max-height: 37px;
	overflow: hidden;
	transition: all 0.7s linear;
	z-index: 2;

	&.active {
		max-width: 1000px;
		max-height: 90vh;
		box-shadow: 0px 1px 1px rgba(5, 47, 55, 0.07),
			0px 2px 1px rgba(5, 47, 55, 0.06), 0px 1px 3px rgba(5, 47, 55, 0.1);
	}

	.click {
		cursor: pointer;
	}

	.rows {
		margin-top: 15px;
	}

	.header {
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		align-items: center;
	}

	.filters,
	.actions {
		opacity: 0;
		transition: all 0.3s;
		pointer-events: none;

		&.active {
			opacity: 1;
			pointer-events: all;
		}
	}

	.filter-row {
		margin-left: 0px;
	}

	.actions {
		display: flex;
		align-items: center;
		justify-content: flex-end;
		margin-top: 20px;

		.btn-primary-vue {
			margin-left: 10px;
		}
	}
}

.relation {
	display: flex;
	flex-direction: row;
	align-items: center;
	justify-content: flex-start;
	margin: 0 0 8px 0;
	padding: 4px 8px;
	background-color: white;
	width: fit-content;
	border-radius: 4px;
	border: 1px solid var(--border-color);

	> div {
		margin: 0 8px 0 0;
		padding: 4px 8px;
		border-radius: 4px;
		cursor: pointer;
		transition: all 0.3s;

		&:last-child {
			margin: 0;
		}

		&.selected {
			background-color: var(--primary-color);
			color: white;
			box-shadow: 0px 1px 1px rgba(5, 47, 55, 0.07),
				0px 2px 1px rgba(5, 47, 55, 0.06), 0px 1px 3px rgba(5, 47, 55, 0.1);
		}
	}
}
</style>