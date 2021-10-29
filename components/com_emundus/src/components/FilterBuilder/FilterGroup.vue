<template>
  <div class="group">
    <div class="wrapper">
      <div class="rows">
        <div class="relation">
          <label>Condition</label>
          <select name="and_or" v-model="andOr">
            <option value="AND">ET</option>
            <option value="OR">OU</option>
          </select>
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
      <div class="actions">
        <div class="btn-primary-vue" @click="addFilter">
          <span class="material-icons"> add </span>
          Ajouter un filtre
        </div>
      </div>
    </div>
    <span class="material-icons delete" @click="removeGroup">
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
    }
  },
  components: {
    FilterRow,
  },
  data() {
    return {
      andOr: "AND",
      filters: [],
    };
  },
  mounted() {
    this.filters = this.$store.state.queryFilters.groups[this.id] ? Object.entries(this.$store.state.queryFilters.groups[this.id].filters).map(filter => Number(filter[0])) : [];
  },
  methods: {
    addFilter() {
      this.filters.push(Math.floor(Math.random() * Date.now()));
    },
    removeFilter(filterId) {
      this.filters = this.filters.filter(filter => filter !== filterId);
    },
    removeGroup() {
      this.$store.dispatch("removeGroup", this.id);

      this.$emit("removeGroup", this.id);
    },
  },
  watch: {
    andOr() {
      this.$store.dispatch("updateAndOr", {
        group: this.id,
        and_or: this.andOr,
      });
    }
  }
};
</script>

<style lang="scss" scoped>
.group {
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  margin: 20px 0 20px 40px;

  .wrapper {
    display: flex;
    flex-direction: column;
    padding: 16px;
    border: 1px solid var(--border-color);
  }
}
</style>