<template>
  <div class="filter-row">
    <!-- <label for="filter-name">Filtres</label> -->
    <select name="filter-name" v-model="selectedFilter">
      <option v-for="name in names" :key="name.id" :value="name.id">
        {{ translate(name.label) }}
      </option>
    </select>

    <!-- <label for="filter-action">Actions</label> -->
    <select
        name="filter-action"
        v-model="selectedAction"
    >
      <option v-for="(action, key) in actions" :key="key" :value="key">
        {{ action }}
      </option>
    </select>

    <!-- <label for="filter-value">Valeurs</label> -->
    <select
        v-if="type == 'select'"
        name="filter-value"
        v-model="selectedValue"
    >
      <option v-for="(value, key) in values" :key="key" :value="key">
        {{ value }}
      </option>
    </select>
    <input
        v-else
        type="text"
        name="filter-value"
        v-model="selectedValue"
    />

    <span class="material-icons-outlined delete" @click="removeFilter">
      clear
    </span>
  </div>
</template>

<script>

export default {
  props: {
    id: {
      type: Number,
      required: true,
    },
    group: {
      type: Number,
      default: 0,
    },
  },
  data() {
    return {
      selectedFilter: null,
      selectedAction: null,
      selectedValue: null,
      names: [],
    };
  },
  mounted() {
    this.names = this.$store.state.filters.map((filter, index) => {
      if (index == 0) {
        this.selectedFilter = filter.id;
      }
      return {
        id: filter.id,
        label: filter.label,
      };
    });

    if (
        this.$store.state.queryFilters.groups[this.group] &&
        this.$store.state.queryFilters.groups[this.group].filters &&
        this.$store.state.queryFilters.groups[this.group].filters[this.id]
    ) {
      this.selectedFilter = this.$store.state.queryFilters.groups[this.group].filters[this.id].id;
      this.selectedAction = this.$store.state.queryFilters.groups[this.group].filters[this.id].action;
      this.selectedValue = this.$store.state.queryFilters.groups[this.group].filters[this.id].value;
    } else {
      this.updateFilter();
    }
  },
  methods: {
    updateFilter() {
      // update the actions and the values based on name selected
      this.$store.dispatch("updateQueryFilters", {
        group: this.group,
        id: this.id,
        filter: {
          id: this.selectedFilter,
          action: this.selectedAction,
          value: this.selectedValue,
        },
      });
    },
    removeFilter() {
      this.$store.dispatch("removeQueryFilter", {
        group: this.group,
        id: this.id,
      });

      this.$emit("removeFilter", this.id);
    },
  },
  computed: {
    actions() {
      const filter = this.$store.state.filters.find(
          (filter) => filter.id == this.selectedFilter
      );
      return filter ? filter.actions : [];
    },
    values() {
      const filter = this.$store.state.filters.find(
          (filter) => filter.id == this.selectedFilter
      );
      return filter ? filter.values : [];
    },
    type() {
      const filter = this.$store.state.filters.find(
          (filter) => filter.id == this.selectedFilter
      );
      return filter ? filter.type : null;
    },
  },
  watch: {
    selectedFilter() {
      this.updateFilter();
    },
    selectedAction() {
      this.updateFilter();
    },
    selectedValue() {
      this.updateFilter();
    },
  },
};
</script>

<style lang="scss" scoped>
.filter-row {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  margin: 8px 0;
  padding: 8px 16px;
  background-color: var(--grey-bg-color);
  border-radius: 4px;

  select,
  input {
    height: 32px;
    margin: 0 16px 0 0;
  }

  .delete {
    cursor: pointer;
  }
}
</style>
